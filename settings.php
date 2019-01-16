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
 * Settings for the CUL upcoming events block.
 *
 * @package    block/culupcoming_events
 * @copyright  Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $options = [];

    for ($i = 0; $i <= 365; $i++) {
        $options[$i] = $i;
    }

    $settings->add(new admin_setting_configselect(
        'block_culupcoming_events/lookahead',
        new lang_string('lookahead', 'block_culupcoming_events'),
        new lang_string('lookahead_help', 'block_culupcoming_events'),
        365,
        $options
    ));
}