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
 * Event observer used by the assign SEB submission plugin.
 *
 * @package    assignsubmission_seb
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace assignsubmission_seb\event;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/assign/locallib.php');

/**
 * Event handler for SEB submission plugin.
 *
 * @package    assignsubmission_seb
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {
    /**
     * Triggered when a new course module is created
     * @param \core\event\course_module_created $event
     */
    public static function course_module_created_handle(\core\event\course_module_created $event) {
        self::handle_course_module_event($event);
    }

    /**
     * Triggered when a new course module is updated
     * @param \core\event\course_module_updated $event
     */
    public static function course_module_updated_handle(\core\event\course_module_updated $event) {
        self::handle_course_module_event($event);
    }

    /**
     * Course module created/updated event.
     *
     * If module is assign and has SEB enabled, change calendar events description to avoid
     * that assign description can be viewed by students.
     *
     * @param \mod_assign\event\base $event The course module created/updated event.
     */
    protected static function handle_course_module_event($event) {
        global $DB;

        if ('assign' == $event->get_data()['other']['modulename']) {
            $instanceid = $event->get_data()['other']['instanceid'];
            $cm = get_coursemodule_from_instance('assign', $instanceid);
            if ($cm) {
                $context = \context_module::instance($cm->id);
                $assign = new \assign($context, null, null);
                $plugin = new \assign_submission_seb($assign, 'seb');
                if ($plugin->get_config('enabled')) {
                    $calendarevents = $DB->get_records(
                        'event',
                        ['modulename' => 'assign', 'instance' => $event->get_data()['other']['instanceid']]
                    );
                    foreach ($calendarevents as $calendarevent) {
                        $DB->set_field(
                            'event',
                            'description',
                            get_string('seb_calendareventdescription', 'assignsubmission_seb'),
                            ['id' => $calendarevent->id]
                        );
                    }
                }
            }
        }
    }
}
