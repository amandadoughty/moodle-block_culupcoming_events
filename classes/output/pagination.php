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
 * Class containing data for CUL Upcoming Events block.
 *
 * @package    block/culupcoming_events
 * @version    See the value of '$plugin->version' in below.
 * @author     Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 */

namespace block_culupcoming_events\output;

use renderer_base;
use renderable;
use templatable;
use stdClass;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * Class containing data for CUL Upcoming Events block.
 *
 * @author     Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
class pagination implements templatable, renderable {
    /**
     * @var string The tab to display.
     */
    public $prev;

    /**
     * @var string The tab to display.
     */
    public $next;

    /**
     * Constructor.
     *
     * @param string $tab The tab to display.
     */
    public function __construct($prev, $next) {
        $this->prev = $prev;
        $this->next = $next;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $pagination = $this->get_pagination($this->prev, $this->next);

        return $pagination;
    }

    /**
     * Function to create the pagination. This will only show up for non-js
     * enabled browsers.
     *
     * @param int $prev the previous page number
     * @param int $next the next page number
     * @return string $output html
     */
    public function get_pagination($prev = false, $next = false) {
        global $PAGE;

        $pagination = new stdClass();
        if ($prev) {
            $pagination->prev = new stdClass();
            $pagination->prev->prevurl = new moodle_url($PAGE->url, array('block_culupcoming_events_page' => $prev));
            $pagination->prev->prevtext = get_string('sooner', 'block_culupcoming_events');
        }
        if ($prev && $next) {
            $pagination->sep = '&nbsp;|&nbsp;';
        } else {
            $pagination->sep = '';
        }
        if ($next) {
            $pagination->next = new stdClass();
            $pagination->next->nexturl = new moodle_url($PAGE->url, array('block_culupcoming_events_page' => $next));
            $pagination->next->nexttext = get_string('later', 'block_culupcoming_events');
        }

        return $pagination;
    }
}