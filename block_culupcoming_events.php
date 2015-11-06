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
 * CUL Upcoming Events block
 *
 * @package    block
 * @subpackage culupcoming_events
 * @copyright  2013 Tim Gagen <Tim.Gagen.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require_once($CFG->dirroot.'/blocks/culupcoming_events/locallib.php');

/**
 * block_culupcoming_events
 *
 * @package block
 * @copyright
 */
class block_culupcoming_events extends block_base {
    /**
     * block_culupcoming_events::init()
     */
    public function init() {
        global $COURSE;
        if ($COURSE->id != SITEID) {
            $this->title = get_string('blocktitlecourse', 'block_culupcoming_events');
        } else {
            $this->title = get_string('blocktitlesite', 'block_culupcoming_events');
        }
    }

    public function has_config() {
        return true;
    }

    public function get_content() {
        global $CFG, $OUTPUT, $COURSE;

        require_once($CFG->dirroot . '/calendar/lib.php');

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text   = '';
        $this->content->footer = '';

        if (empty($this->instance)) {
            return $this->content;
        } else {
            $limitnum = 7;
            $page = optional_param('block_culupcoming_events_page', 1, PARAM_RAW);
            $limitfrom = $page > 1 ? ($page * $limitnum) - $limitnum : 0;
            $lastdate = 0;
            $lastid = 0;

            list($more, $events) = block_culupcoming_events_get_events($COURSE->id, $lastid, $lastdate, $limitfrom, $limitnum);
            $renderer = $this->page->get_renderer('block_culupcoming_events');
            $this->content->text = $renderer->culupcoming_events_reload();
            $this->content->text .= $renderer->culupcoming_events($events);

            $prev = false;
            $next = false;

            if ($page > 1) {
                // Add a 'sooner' link.
                $prev = $page - 1;
            }

            if ($more) {
                // Add an 'later' link.
                $next = $page + 1;
            }

            $this->content->text .= $renderer->culupcoming_events_pagination($prev, $next);

            if (empty($this->content->text)) {
                $this->content->text = html_writer::tag('div',
                                                        get_string('noupcomingevents', 'calendar'),
                                                        array('class' => 'post', 'style' => 'margin-left: 1em'));
            }

            $this->page->requires->yui_module(
                'moodle-block_culupcoming_events-scroll',
                'M.block_culupcoming_events.scroll.init',
                array(array('limitnum' => $limitnum, 'courseid' => $COURSE->id))
            );

            // Footer.
            $courseshown = $COURSE->id;
            $context = context_course::instance($courseshown);
            $hrefcal = new moodle_url('/calendar/view.php', array('view' => 'upcoming', 'course' => $courseshown));
            $iconcal = $OUTPUT->pix_icon('i/calendar', '', 'moodle', array('class' => 'iconsmall'));
            $linkcal = html_writer::link($hrefcal, $iconcal . get_string('gotocalendar', 'calendar') . '...');
            $this->content->footer .= html_writer::tag('div', $linkcal);

            if (has_any_capability(array('moodle/calendar:manageentries', 'moodle/calendar:manageownentries'), $context)) {
                $hrefnew = new moodle_url('/calendar/event.php', array('action' => 'new', 'course' => $courseshown));
                $iconnew = $OUTPUT->pix_icon('t/add', '', 'moodle', array('class' => 'iconsmall'));
                $linknew = html_writer::link($hrefnew, $iconnew . get_string('newevent', 'calendar').'...');
                $this->content->footer .= html_writer::tag('div', $linknew);
            }
        }
        return $this->content;
    }
}
