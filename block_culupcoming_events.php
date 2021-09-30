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
 * CUL upcoming events block
 *
 * Displays the CUL upcoming events block.
 *
 * @package    block/culupcoming_events
 * @copyright  Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Handles displaying the CUL upcoming events block.
 *
 * @package    block_culupcoming_events
 * @copyright  Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
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

            $renderable = new \block_culupcoming_events\output\main(
                $lookahead,
                $courseid,
                $lastid,
                $lastdate,
                $limitfrom,
                $limitnum,
                $page
            );

            $renderer = $this->page->get_renderer('block_culupcoming_events');
            $this->content->text = $renderer->render($renderable);
            $renderable = new \block_culupcoming_events\output\footer($courseid);
            $this->content->footer .= $renderer->render($renderable);

            $this->page->requires->yui_module(
                'moodle-block_culupcoming_events-scroll',
                'M.block_culupcoming_events.scroll.init',
                [[
                    'lookahead' => $lookahead,
                    'courseid' => $courseid,
                    'limitnum' => $limitnum
                ]]
            );
        }

        return $this->content;
    }

    /**
     * Returns a list of formats, and whether the block
     * should be displayed within them.
     * @return array(string => boolean) List of formats
     */
    public function applicable_formats() {
        return array('site' => true, 'my' => true, 'course' => true);
    }

    /**
     * Return the plugin config settings for external functions.
     *
     * @return stdClass the configs for both the block instance and plugin
     * @since Moodle 3.8
     */
    public function get_config_for_external() {
        // Return all settings for all users since it is safe (no private keys, etc..).
        $configs = !empty($this->config) ? $this->config : new stdClass();

        return (object) [
            'instance' => $configs,
            'plugin' => new stdClass(),
        ];
    }
}
