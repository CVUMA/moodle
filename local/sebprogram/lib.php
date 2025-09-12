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
 * This page contains hooks.
 *
 * @package    local_sebprogram
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\url;
use local_sebprogram\program_manager;
use local_sebprogram\program_module;

defined('MOODLE_INTERNAL') || die;

/**
 * Hook called before we delete a course module.
 *
 * @param \stdClass $cm The course module record.
 */
function local_sebprogram_pre_course_module_delete($cm) {
    $programmodules = program_module::get_records(['cmid' => $cm->id]);
    foreach ($programmodules as $programmodule) {
        $programmodule->delete();
    }
}

/**
 * Hook called before we delete a course.
 *
 * @param \stdClass $course The course record.
 */
function local_sebprogram_pre_course_delete($course) {
    \local_sebprogram\program_module::delete_all_course_modules($course->id);
}


/**
 * Add nodes to myprofile page.
 *
 * @param \core_user\output\myprofile\tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 *
 * @return bool
 */
function local_sebprogram_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    $usercontext = \context_user::instance($user->id);

    if (has_capability('local/sebprogram:manageprograms', $usercontext)) {
        $sebprogramscategory = new core_user\output\myprofile\category(
            'sebprogramscategory',
            get_string('pluginname', 'local_sebprogram'),
            'administration'
        );
        $tree->add_category($sebprogramscategory);

        $url = new url('/local/sebprogram/manage.php', ['contextid' => $usercontext->id]);
        $string = get_string('manageprograms', 'local_sebprogram');
        $node = new core_user\output\myprofile\node('sebprogramscategory', 'managesebprograms', $string, null, $url);
        $tree->add_node($node);

        $manager = new program_manager();
        ob_start();
        $manager->print_program_list($usercontext->id, 0, ['executable', 'originalname', 'path', 'visible', 'actions'], false);
        $table = ob_get_contents();
        ob_end_clean();
        $node = new core_user\output\myprofile\node(
            'sebprogramscategory',
            'currentsebprograms',
            get_string('pluginname', 'local_sebprogram'),
            null,
            null,
            $table
        );
        $tree->add_node($node);
    }

    return true;
}
