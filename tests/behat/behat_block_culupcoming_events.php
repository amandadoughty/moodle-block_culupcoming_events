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
 * Steps definitions for CUL Upcoming events.
 *
 * @package   block_culupcoming_events
 * @category  test
 * @copyright 2020 Amanda Doughty
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');
require_once(__DIR__ . '/../../../../lib/tests/behat/behat_general.php');

use Behat\Mink\Exception\ExpectationException as ExpectationException;

/**
 * CUL Upcoming Events block definitions.
 *
 * @package   block_culupcoming_events
 * @category  test
 * @copyright 2020 Amanda Doughty
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_block_culupcoming_events extends behat_base {

    /**
     * Adds the CUL Upcoming Events block. Editing mode must be previously enabled.
     *
     * @Given /^I add the CUL Upcoming Events block$/
     */
    public function i_add_the_cul_upcoming_events_block() {
        try {
            // Try with pluginname (Boost flat navigation).
            $this->execute('behat_blocks::i_add_the_block', ['CUL Upcoming Events']);
        } catch (Exception $e) {
            // Try with block title (Classic Add a block).
            try {
                $this->execute('behat_blocks::i_add_the_block', ['Events feed']);
            } catch (Exception $e) {
                $this->execute('behat_blocks::i_add_the_block', ['Module events']);
            }
        }
    }

    /**
     * Checks the number of events in the feed.
     *
     * @Then /^I should see "(?P<number_string>[^"]*)" events in feed$/
     *
     * @param string $number
     */
    public function i_should_see_events_in_feed($number) {
        try {
            $nodes = $this->find_all('css', ".block_culupcoming_events ul.events li.item");
            $actualnumber = count($nodes);
        } catch (Behat\Mink\Exception\ElementNotFoundException $e) {
            $actualnumber = 0;
        }

        if ($actualnumber != (int)$number) {
            throw new ExpectationException(
                "Expected '{$number}' events but found '{$actualnumber}'.",
                $this->getSession()->getDriver()
            );
        }
    }

    /**
     * Scrolls down the events feed.
     *
     * @When /^I scroll the events feed$/
     *
     */
    public function i_scroll_the_events_feed() {
        // Exception if it timesout and the element is still there.
        $msg = "No events were lazy loaded";
        $exception = new ExpectationException($msg, $this->getSession());
        $selector = '.block.block_culupcoming_events .culupcoming_events';
        $script = <<<EOF
        (function() {
            $('{$selector}').scrollTop($('{$selector}')[0].scrollHeight)
        })()
EOF;
        // It will stop spinning once the feed lazy loads a 6th item.
        $this->spin(
            function() use ($script) {
                $this->getSession()->evaluateScript($script);
                $nodes = $this->find_all('css', ".block_culupcoming_events ul.events li");
                $actualnumber = count($nodes);

                if ($actualnumber > 7) {
                    return true;
                }
                return false;
            },
            [],
            self::get_extended_timeout(),
            $exception,
            true
        );
    }
}