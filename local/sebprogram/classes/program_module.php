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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Class for loading/storing programs allowed for modules from the DB.
 *
 * @package    local_sebprogram
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_sebprogram;

use core\persistent;

/**
 * Class for loading/storing a program allowed for a module from the DB.
 *
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class program_module extends persistent {
    /** Table name for the persistent. */
    const TABLE = 'local_sebprogram_program_mod';

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'programid' => [
                'type' => PARAM_INT,
            ],
            'cmid' => [
                'type' => PARAM_INT,
            ],
        ];
    }

    /**
     * Delete enabled programs for all modules in a course.
     *
     * @param int $courseid The course ID.
     */
    public static function delete_all_course_modules(int $courseid) {
        global $DB;

        if (is_int($courseid)) {
            $coursemodulessql = "SELECT id AS cmid
                            FROM {course_modules}
                           WHERE course = $courseid";

            $DB->delete_records_subquery(self::TABLE, 'cmid', 'cmid', $coursemodulessql);
        }
    }

    /**
     * Delete enabled programs for modules that no longer exists.
     */
    public static function delete_orphaned_module_programs() {
        global $DB;

        $orphanedmodulessql = "SELECT DISTINCT s.cmid AS cmid
                              FROM {" . self::TABLE . "} s
                                    LEFT JOIN
                                   {course_modules} cm ON cm.id = s.cmid
                             WHERE cm.id IS NULL
                            ";

        $DB->delete_records_subquery(self::TABLE, 'cmid', 'cmid', $orphanedmodulessql);
    }
}
