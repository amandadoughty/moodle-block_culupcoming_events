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
 * Instance settings for CUL Upcoming Events
 *
 * @package    block
 * @subpackage cupcoming_events
 * @copyright  2013 Amanda Doughty <amanda.doughty.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

class block_culupcoming_events_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $options = array();

        for ($i = 0; $i <= 365; $i++) {
            $options[$i] = $i;
        }

        $default = get_config('block_culupcoming_events', 'lookahead');

        $mform->addElement('select', 'config_lookahead', get_string('lookahead', 'block_culupcoming_events'), $options);
        $mform->setDefault('config_lookahead', $default);
        $mform->addHelpButton('config_lookahead', 'lookahead', 'block_culupcoming_events');
    }
}