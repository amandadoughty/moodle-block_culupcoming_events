<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Helper functions for CUL Upcoming Events block
 *
 * @package    block
 * @subpackage culupcoming_events
 * @copyright  2013 Tim Gagen <Tim.Gagen.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

/**
 * block_culupcoming_events_get_entries()
 * @global type $CFG
 * @global type $COURSE
 * @param  mixed $filtercourse
 * @return array $entries array of upcoming event entries
 */
function block_culupcoming_events_get_entries($filtercourse) {
    global $CFG, $COURSE;

    $entries = array();
    list($courses, $group, $user) = calendar_set_filters($filtercourse);
    $defaultlookahead = CALENDAR_DEFAULT_UPCOMING_LOOKAHEAD;

    if (isset($CFG->calendar_lookahead)) {
        $defaultlookahead = intval($CFG->calendar_lookahead);
    }

    $lookahead = get_user_preferences('calendar_lookahead', $defaultlookahead);
    $defaultmaxevents = CALENDAR_DEFAULT_UPCOMING_MAXEVENTS;

    if (isset($CFG->calendar_maxevents)) {
        $defaultmaxevents = intval($CFG->calendar_maxevents);
    }

    $maxevents = get_user_preferences('calendar_maxevents', $defaultmaxevents);
    $entries   = calendar_get_upcoming($courses, $group, $user, $lookahead, $maxevents);

    foreach ($entries as $entry) {

        if (!isset($entry->time)) {
            continue;
        }

        calendar_add_event_metadata($entry);
        $entry->timeuntil = block_culupcoming_events_human_timing($entry->timestart);
        $courseid  = is_numeric($entry->courseid) ? $entry->courseid : 0;

        $a = new stdClass();
        $a->name = $entry->name;

        if ($courseid && $courseid != SITEID) {
            $a->course = block_culupcoming_events_get_course_displayname ($courseid, $filtercourse);
            $entry->description = get_string('courseevent', 'block_culupcoming_events', $a);
        } else {
            $entry->description = get_string('event', 'block_culupcoming_events', $a);
        }

        switch (strtolower($entry->eventtype)) {
            case 'user':
                $entry->img = block_culupcoming_events_get_user_img($entry->userid);
                break;
            case 'course':
                $entry->img = block_culupcoming_events_get_course_img($entry->courseid, $filtercourse);
                break;
            case 'site':
                $entry->img = block_culupcoming_events_get_site_img();
                break;
            default:
                $entry->img = block_culupcoming_events_get_course_img($entry->courseid, $filtercourse);
        }
    }

    return $entries;
}

/**
 * Function that compares a time stamp to the current time and returns a human
 * readable string saying how long until time stamp
 *
 * @param int $time unix time stamp
 * @return string representing time since message created
 */
function block_culupcoming_events_human_timing ($time) {
    $time = $time - time(); // To get the time until that moment.
    $timeuntil = get_string('today');

    $tokens = array (
        31536000 => get_string('year'),
        2592000 => get_string('month'),
        604800 => get_string('week'),
        86400 => get_string('day'),
        3600 => get_string('hour'),
        60 => get_string('minute'),
        1 => get_string('second', 'block_culupcoming_events')
    );

    foreach ($tokens as $unit => $text) {

        if ($time < $unit) {
            continue;
        }

        $numberofunits = floor($time / $unit);
        $units = $numberofunits . ' ' . $text . (($numberofunits > 1) ? 's' : '');
        return get_string('time', 'block_culupcoming_events', $units);
    }

    return $timeuntil;
}

/**
 * block_culupcoming_events_get_course_displayname()
 * @param  type $courseid
 * @return string
 */
function block_culupcoming_events_get_course_displayname ($courseid, $filtercourse) {
    global $DB;

    if (!$courseid) {
        return '';
    } else if (array_key_exists($courseid, $filtercourse)) {
        $coursefullname  = $filtercourse[$courseid]->fullname;
        $courseshortname = $filtercourse[$courseid]->shortname;
        $courseidnumber  = $filtercourse[$courseid]->idnumber;
    } else {
        $course = $DB->get_record('course', array('id' => $courseid));
        $coursefullname  = $course->fullname;
        $courseshortname = $course->shortname;
        $courseidnumber  = $course->idnumber;
    }

    $coursedisplayname = preg_match('/\A\s*\z/', trim($courseidnumber)) ?
        $courseshortname : $courseidnumber;

    return $coursedisplayname;
}

/**
 * block_culupcoming_events_get_course_img()
 * @global type $CFG
 * @global type $DB
 * @global type $PAGE
 * @global type $OUTPUT
 * @param  type $courseid
 * @return string Image tag, wrapped in a hyperlink.
 */
function block_culupcoming_events_get_course_img ($courseid) {
    global $CFG, $DB, $PAGE, $OUTPUT;

    $courseid  = is_numeric($courseid) ? $courseid : null;
    $coursedisplayname = block_culupcoming_events_get_course_displayname ($courseid, array());

    if ($course = $DB->get_record('course', array('id' => $courseid))) {
        $courseimgrenderer = $PAGE->get_renderer('block_culupcoming_events', 'renderers_course_picture');
        $coursepic = new block_culupcoming_events_course_picture($course);
        $coursepic->link = true;
        $coursepic->class = 'coursepicture';
        $courseimg = $courseimgrenderer->render($coursepic);
    } else {
        $url = $OUTPUT->pix_url('u/f2');
        $attributes = array(
            'src' => $url,
            'alt' => get_string('pictureof', '', $coursedisplayname),
            'class' => 'courseimage'
        );
        $img = html_writer::empty_tag('img', $attributes);
        $attributes = array('href' => $CFG->wwwroot);
        $courseimg = html_writer::tag('a', $img, $attributes);
    }

    return $courseimg;
}

/**
 * block_culupcoming_events_get_user_img()
 * @global type $DB
 * @global type $OUTPUT
 * @param  type $userid
 * @return string Image tag, possibly wrapped in a hyperlink.
 */
function block_culupcoming_events_get_user_img ($userid) {
    global $CFG, $DB, $OUTPUT;

    $userid  = is_numeric($userid) ? $userid : null;

    if ($user = $DB->get_record('user', array('id' => $userid))) {
        $userpic = new user_picture($user);
        $userpic->link = true;
        $userpic->class = 'personpicture';
        $userimg = $OUTPUT->render($userpic);
    } else {
        $url = $OUTPUT->pix_url('u/f2');
        $attributes = array(
            'src' => $url,
            'alt' => get_string('anon', 'block_culupcoming_events'),
            'class' => 'personpicture'
        );
        $img = html_writer::empty_tag('img', $attributes);
        $attributes = array('href' => $CFG->wwwroot);
        $userimg = html_writer::tag('a', $img, $attributes);
    }

    return $userimg;
}

/**
 * block_culupcoming_events_get_site_img()
 * @return string full image tag, possibly wrapped in a link.
 */
function block_culupcoming_events_get_site_img () {

    $admins      = get_admins();
    $adminuserid = 2;

    foreach ($admins as $admin) {
        if ('admin' == $admin->username) {
            $adminuserid = $admin->id;
            break;
        }
    }

    $siteimg = block_culupcoming_events_get_user_img($adminuserid);

    return $siteimg;
}
