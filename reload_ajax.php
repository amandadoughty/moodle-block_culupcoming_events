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
 * Reloading functionality for culupcoming_events block.
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

require_login();
$PAGE->set_context(context_system::instance());

if (!confirm_sesskey()) {
    $error = array('error' => get_string('invalidsesskey', 'error'));
    die(json_encode($error));
}

$lastid = required_param('lastid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$count = required_param('count', PARAM_INT);
$list = '';

// Get more events.
$events = block_culupcoming_events_ajax_reload($count, $lastid);
$renderer = $PAGE->get_renderer('block_culupcoming_events');

if ($events) {
    $list .= $renderer->culupcoming_events_items ($events);
}

echo json_encode(array('output' => $list, 'count' => $count));
