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
 * Course picture renderable.
 *
 * @package    block/culupcoming_events
 * @copyright  Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 */

namespace block_culupcoming_events\output;

use renderer_base;
use renderable;
use templatable;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

class course_picture implements templatable, renderable {
    /**
     * @var array List of mandatory fields in user record here. (do not include
     * TEXT columns because it would break SELECT DISTINCT in MSSQL and ORACLE)
     */
    protected static $fields = ['id', 'shortname', 'idnumber'];

    /**
     * @var stdClass A course object with at least fields all columns specified
     * in $fields array constant set.
     */
    public $course;

    /**
     * @var bool Add course link to image
     */
    public $link = true;

    /**
     * @var bool Add non-blank alt-text to the image.
     * Default true, set to false when image alt just duplicates text in screenreaders.
     */
    public $alttext = true;

    /**
     * @var bool Whether or not to open the link in a popup window.
     */
    public $popup = false;

    /**
     * @var string Image class attribute
     */
    public $class = 'this';

    /**
     * Course picture constructor.
     *
     * @param stdClass $course course record with at least id, picture, imagealt, coursename set.
     *                 It is recommended to add also contextid of the course for performance reasons.
     */
    public function __construct(\stdClass $course) {
        global $CFG, $DB;

        if (empty($course->id)) {
            throw new coding_exception('Course id is required when printing course avatar image.');
        }

        // Only touch the DB if we are missing data and complain loudly.
        $needrec = false;

        foreach (self::$fields as $field) {
            if (!property_exists($course, $field)) {
                $needrec = true;
                debugging('Missing '.$field
                    .' property in $course object, this is a performance problem that needs to be fixed by a developer. '
                    .'Please use course_picture::fields() to get the full list of required fields.', DEBUG_DEVELOPER);
                break;
            }
        }

        if ($needrec) {
            $this->course = $DB->get_record('course', ['id' => $course->id], self::fields(), MUST_EXIST);
        }

        $this->course = new \core_course_list_element($course);
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        $this->output = $output;
        $courseimg = $this->get_course_picture();

        return $courseimg;
    }

    /**
     * Internal implementation of course image rendering.
     *
     * @return string
     */
    protected function get_course_picture() {
        global $CFG, $DB, $PAGE;

        $course = $this->course;
        $coursedisplayname = $course->shortname;

        if ($this->alttext) {
            $alt = get_string('pictureof', '', $coursedisplayname);
        } else {
            $alt = '';
        }

        $class = $this->class;
        $src = $this->get_url($PAGE, $this->output);
        $courseimg = ['src' => $src, 'alt' => $alt, 'title' => $alt, 'class' => $class];

        // Then wrap it in link if needed.
        if ($this->link) {
            $courseimg['url'] = new \moodle_url('/course/view.php', ['id' => $course->id]);
        }

        return $courseimg;
    }

    /**
     * Works out the URL for the course picture.
     *
     * This method is recommended as it avoids costly redirects of course pictures
     * if requests are made for non-existent files etc.
     *
     * @param moodle_page $page
     * @param renderer_base $renderer
     * @return moodle_url
     */
    public function get_url(\moodle_page $page, renderer_base $renderer = null) {
        global $CFG, $DB;

        $config = get_config('block_culupcoming_events');

        if (is_null($renderer)) {
            $renderer = $page->get_renderer('core');
        }

        $defaulturl = $renderer->image_url('u/f2'); // Default image.

        if ((!empty($CFG->forcelogin) and !isloggedin()) ||
            (!empty($CFG->forceloginforprofileimage) && (!isloggedin() || isguestuser()))) {
            // Protect images if login required and not logged in
            // also if login is required for profile images and is not logged in or guest
            // do not use require_login() because it is expensive and not suitable here anyway.
            return $defaulturl;
        }

        if ($this->course->has_course_overviewfiles()) {
            foreach ($this->course->get_course_overviewfiles() as $file) {
                try {
                    $isimage = $file->is_valid_image();
                } catch (exception $e) {
                    $isimage = false;
                }

                if ($isimage) {
                    $url = moodle_url::make_pluginfile_url(
                        $file->get_contextid(),
                        $file->get_component(),
                        $file->get_filearea(),
                        null,
                        $file->get_filepath(),
                        $file->get_filename()
                    );
                    return $url;
                }
            }
        } else {
            return $this->output->get_generated_image_for_id($this->course->id);
        }

        return $defaulturl;
    }
}
