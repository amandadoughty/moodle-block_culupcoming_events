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
 * Renderer for CUL Upcoming Events block
 *
 * @package    block
 * @subpackage culupcoming_events
 * @copyright  2013 Tim Gage <Tim.Gagen.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die;

require_once('renderers/course_picture.php');

/**
 * block_culupcoming_events_renderer
 *
 * @package   block
 * @copyright 2013 Tim Gagen <Tim.Gagen.1@city.ac.uk>
 */
class block_culupcoming_events_renderer extends plugin_renderer_base {
    /**
     * block_culupcoming_events_renderer::culupcoming_events()
     *
     * @param mixed $eventitems
     * @return string
     */
    public function culupcoming_events($eventitems) {

        global $COURSE;
        $courseid = $COURSE->id;

        if (!count($eventitems) || !is_numeric($courseid)) {
            return html_writer::tag('div', '<ul></ul>', array('class' => 'culupcoming_events'));
        }
        // Generate an id and the required JS call to make this a nice widget.
        $divid = html_writer::random_id('culupcoming_events');
        // Start content generation.
        $output  = html_writer::start_tag('div', array('id' => $divid, 'class' => 'culupcoming_events'));
        $output .= html_writer::start_tag('ul');
        $output .= $this->culupcoming_events_items($eventitems);
        $output .= html_writer::end_tag('ul');
        $output .= html_writer::end_tag('div');

        return $output;
    }

    /**
     * block_culupcoming_events_renderer::culupcoming_events()
     *
     * @param mixed $courseid
     * @param mixed $eventitems
     * @return string
     */
    public function culupcoming_events_items($eventitems) {

        global $COURSE;
        $courseid = $COURSE->id;

        $moodleurl = new moodle_url(CALENDAR_URL . 'view.php?view=day&amp;course=' . $courseid . '&amp;');
        $output = '';

        foreach ($eventitems as $item) {
            $usertime = usergetdate($item->timestart);
            $href = calendar_get_link_href($moodleurl, $usertime['mday'], $usertime['mon'], $usertime['year']);
            $href->set_anchor('event_'. $item->id);
            $displaytime = date_format_string($item->timestart, '%A, %e %b, %l:%M %p');

            $id = $item->id . '_' . $item->timestart;
            $output .= html_writer::start_tag('li', array('id' => $id));
            $output .= html_writer::start_tag('div', array('class' => 'clearfix notifictionitem'));

            // Avatar.
            $output .= html_writer::start_tag('div', array('class' => 'avatar'));
            $output .= empty($item->img) ? '' : $item->img;
            $output .= html_writer::end_tag('div');

            // Event description.
            $output .= html_writer::start_tag('div', array('class' => 'notificationtext'));
            $output .= html_writer::tag('span', $item->description . $displaytime);
            $output .= html_writer::end_tag('div');

            // Activity icon.
            $output .= html_writer::start_tag('div', array('class' => 'activityicon'));
            $output .= $item->icon;

            $output .= html_writer::end_tag('div'); // Closing div: .activityicon.
            $output .= html_writer::end_tag('div'); // Closing div: .notifictionitem.

            // Meta data.
            $output .= html_writer::start_tag('div', array('class' => 'meta'));

            // Time until.
            $output .= html_writer::start_tag('div', array('class' => 'timeuntil'));
            $output .= html_writer::start_tag('span');
            $output .= $item->timeuntil;
            $output .= html_writer::end_tag('span');
            $output .= html_writer::end_tag('div'); // Closing div: .timeuntil.

            // View link.
            $output .= html_writer::start_tag('div', array('class' => 'contexturls'));
            $output .= html_writer::link($href, get_string('visit', 'block_culupcoming_events'));
            $output .= html_writer::end_tag('div');

            $output .= html_writer::end_tag('div'); // Closing div: .meta.

            $output .= html_writer::end_tag('li');
            $output .= '<hr/>';
        }

        return $output;
    }


    /**
     * Function for appending reload button and ajax loading gif to title
     *
     * @return string $output html
     */
    public function culupcoming_events_reload () {
        $output = '';
        $output .= html_writer::start_tag('div', array('class' => 'reload'));

        // Reload button.
        $reloadimg = $this->output->pix_icon('i/reload', '', 'moodle',
                array('class' => 'smallicon'));
        $reloadurl = $this->page->url;
        $reloadattr = array('class' => 'block_culupcoming_events_reload');
        $output .= html_writer::link($reloadurl, $reloadimg, $reloadattr);

        // Loading gif.
        $ajaximg = $this->output->pix_icon('i/loading_small', '');
        $output .= html_writer::tag('span', $ajaximg, array('class' => 'block_culupcoming_events_loading'));
        $output .= html_writer::end_tag('div');
        return $output;
    }

    /**
     * Function to create the pagination. This will only show up for non-js
     * enabled browsers.
     *
     * @param int $prev the previous page number
     * @param int $next the next page number
     * @return string $output html
     */
    public function culupcoming_events_pagination($prev=false, $next=false) {
        $output = '';

        if ($prev || $next) {
            $output .= html_writer::start_tag('div', array('class' => 'pages'));

            if ($prev) {
                $prevurl = new moodle_url($this->page->url, array('block_culupcoming_events_page' => $prev));
                $prevtext = get_string('sooner', 'block_culupcoming_events');
                $output .= html_writer::link($prevurl, $prevtext);
            }

            if ($prev && $next) {
                $output .= '&nbsp;|&nbsp;';
            }

            if ($next) {
                $nexturl = new moodle_url($this->page->url, array('block_culupcoming_events_page' => $next));
                $nexttext = get_string('later', 'block_culupcoming_events');
                $output .= html_writer::link($nexturl, $nexttext);
            }

            $output .= html_writer::end_tag('div'); // Closing div: .pages.
        }

        return $output;
    }
}
