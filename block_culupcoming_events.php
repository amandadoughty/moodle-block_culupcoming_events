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

    public function instance_allow_config() {
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
            // Extra params for reloading and scrolling.
            $limitnum = 7;
            $page = optional_param('block_culupcoming_events_page', 1, PARAM_RAW);
            $limitfrom = $page > 1 ? ($page * $limitnum) - $limitnum : 0;
            $lastdate = 0;
            $lastid = 0;
            $courseid = $COURSE->id;

            if (isset($this->config->lookahead)) {
                $lookahead = $this->config->lookahead;
            } else {
                $lookahead = get_config('block_culupcoming_events', 'lookahead');
            }

            $renderable = new \block_culupcoming_events\output\main($lookahead,
                $courseid,
                $lastid,
                $lastdate,
                $limitfrom,
                $limitnum,
                $page
            );

            $renderer = $this->page->get_renderer('block_culupcoming_events');
            $this->content->text = $renderer->render($renderable);

            $this->page->requires->yui_module(
                'moodle-block_culupcoming_events-scroll',
                'M.block_culupcoming_events.scroll.init',
                [[
                    'lookahead' => $lookahead,
                    'courseid' => $courseid,
                    'limitnum' => $limitnum,
                    'page' => $page
                ]]
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
