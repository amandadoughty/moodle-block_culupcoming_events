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

defined('MOODLE_INTERNAL') || die();

/**
 * Class containing data for CUL Upcoming Events block.
 *
 * @author     Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
class main implements templatable, renderable {
    /**
     * @var string The tab to display.
     */
    public $lookahead;

    /**
     * @var string The tab to display.
     */
    public $courseid;

    /**
     * @var string The tab to display.
     */
    public $lastid;

    /**
     * @var string The tab to display.
     */
    public $lastdate;

            /**
     * @var string The tab to display.
     */
    public $limitfrom;

    /**
     * @var string The tab to display.
     */
    public $limitnum;    

    /**
     * @var string The tab to display.
     */
    public $page;

    /**
     * Constructor.
     *
     * @param string $tab The tab to display.
     */
    public function __construct(
        $lookahead,
        $courseid,
        $lastid,
        $lastdate,
        $limitfrom,
        $limitnum, 
        $page) 
    {
        $this->lookahead = $lookahead;
        $this->courseid = $courseid;
        $this->lastid = $lastid;
        $this->lastdate = $lastdate;
        $this->limitfrom = $limitfrom;
        $this->limitnum = $limitnum;
        $this->page = $page;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $PAGE;

        $events = new eventlist(
            $this->lookahead,
            $this->courseid,
            $this->lastid,
            $this->lastdate,
            $this->limitfrom,
            $this->limitnum,
            $this->page
        );

        $templatecontext = $events->export_for_template($output);
        $templatecontext['reloadurl'] = $PAGE->url;

        return $templatecontext;
    }
}