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
 * Appends events asynchronously on scroll.
 *
 * @package    block/culupcoming_events
 * @copyright  Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 */

define('AJAX_SCRIPT', true);

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/calendar/lib.php');

use block_culupcoming_events\output\eventlist;

require_sesskey();
require_login();

$PAGE->set_context(context_system::instance());
$lookahead = required_param('lookahead', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$lastid = required_param('lastid', PARAM_INT);
$limitnum = required_param('limitnum', PARAM_INT);
$list = '';
$end = false;
$renderer = $PAGE->get_renderer('block_culupcoming_events');

$eventlist = new eventlist(
    $lookahead,
    $courseid,
    $lastid,
    0,
    0,
    $limitnum,
    0
);

$templatecontext = $eventlist->export_for_template($renderer);
$events = $templatecontext['events'];
$more = $templatecontext['more'];

if ($events) {
    foreach ($events as $event) {
        $list .= $renderer->render_from_template('block_culupcoming_events/event',  $event);
    }
}

if (!$more) {
    $list .= html_writer::tag('li', get_string('nomoreevents', 'block_culupcoming_events'));
    $end = true;
}

echo json_encode(['output' => $list, 'end' => $end]);