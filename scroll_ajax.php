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
 * Infinite scrolling functionality for culupcoming_events block.
 *
 * @package    block
 * @subpackage culupcoming_events
 * @copyright  2013 Amanda Doughty <amanda.doughty.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

define('AJAX_SCRIPT', true);

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once($CFG->dirroot . '/calendar/lib.php');

require_sesskey();
require_login();
$PAGE->set_context(context_system::instance());
$limitfrom = required_param('limitfrom', PARAM_INT);
$limitnum = required_param('limitnum', PARAM_INT);
$lastdate = required_param('lastdate', PARAM_INT);
$lastid = required_param('lastid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$list = '';
$end = false;

// Get more events.
list($more, $events) = block_culupcoming_events_get_events($courseid, $lastid, $lastdate, $limitfrom, $limitnum);
$renderer = $PAGE->get_renderer('block_culupcoming_events');

if ($events) {
    $list .= $renderer->culupcoming_events_items($events);
}

if (!$more) {
    $list .= html_writer::tag('li', get_string('nomoreevents', 'block_culupcoming_events'));
    $end = true;
}

echo json_encode(array('output' => $list, 'end' => $end));
