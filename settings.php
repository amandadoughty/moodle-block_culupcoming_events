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
 * Admin settings for CUL Upcoming Events
 *
 * @package    block
 * @subpackage cupcoming_events
 * @copyright  2013 Amanda Doughty <amanda.doughty.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext(
        'block_culupcoming_events/gravatar',
        get_string('gravatardefaulturl', 'admin'),
        get_string('gravatardefaulturl_help', 'admin'), 'mm'
    ));

    $options = array();
    for ($i = 1; $i <= 365; $i++) {
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