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
        $this->title = get_string('blocktitle', 'block_culupcoming_events');
    }

    public function has_config() {
        return true;
    }


    /**
     * block_culupcoming_events::get_content()
     * @return
     */
    public function get_content() {
        global $CFG, $OUTPUT;

        require_once($CFG->dirroot . '/calendar/lib.php');

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text   = '';
        $this->content->footer = '';
        $filtercourse = array();

        if (empty($this->instance)) {
            $courseshown = false;
            $this->content->footer = '';
            return $this->content;
        } else {
            $courseshown = $this->page->course->id;
            $hrefcal = new moodle_url('/calendar/view.php', array('view' => 'upcoming', 'course' => $courseshown));
            $iconcal = $OUTPUT->pix_icon('i/calendar', '', 'moodle', array('class' => 'iconsmall'));
            $linkcal = html_writer::link($hrefcal, $iconcal . get_string('gotocalendar', 'calendar') . '...');
            $this->content->footer .= html_writer::tag('div', $linkcal);
            $context = context_course::instance($courseshown);

            if (has_any_capability(array('moodle/calendar:manageentries', 'moodle/calendar:manageownentries'), $context)) {
                $hrefnew = new moodle_url('/calendar/event.php', array('action' => 'new', 'course' => $courseshown));
                $iconnew = $OUTPUT->pix_icon('t/add', '', 'moodle', array('class' => 'iconsmall'));
                $linknew = html_writer::link($hrefnew, $iconnew . get_string('newevent', 'calendar').'...');
                $this->content->footer .= html_writer::tag('div', $linknew);
            }

            // Filter events to include only those from the course we are in.
            $filtercourse = ($courseshown == SITEID) ?
                calendar_get_default_courses() : array($courseshown => $this->page->course);

            $events = block_culupcoming_events_get_entries($filtercourse);
            $renderer = $this->page->get_renderer('block_culupcoming_events');
            $this->content->text = $renderer->culupcoming_events($courseshown, $events);
        }

        if (empty($this->content->text)) {
            $this->content->text = html_writer::tag('div',
                                                    get_string('noupcomingevents', 'calendar'),
                                                    array('class' => 'post', 'style' => 'margin-left: 1em'));
        }

        return $this->content;
    }
}
