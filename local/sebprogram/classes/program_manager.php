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
 * Manager for programs available to be used in SEB.
 *
 * @package    local_sebprogram
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_sebprogram;

use core\url;
use core\output\pix_icon;
use local_sebprogram\local\table\program_list;
use quizaccess_seb\hook\seb_regenerate_config_hook;
/**
 * Class to manage programs.
 */
class program_manager {
    /**
     * Return action icons for a program.
     *
     * @param program $program Program object.
     * @param int $returncmid Course module id of the activity to continue editing after returning
     * @return array Action icons
     */
    public static function get_action_icons($program, $returncmid): array {
        global $OUTPUT, $USER;

        $icons = [];

        $cssclass = 'iconsmall action-icon';

        $programcontext = \context::instance_by_id($program->get('contextid'));

        $params = ['id' => $program->get('id')];
        if ($returncmid) {
            $params['returncmid'] = $returncmid;
        }

        if (
            ($programcontext instanceof \context_user && $programcontext->instanceid == $USER->id)
                || has_capability('local/sebprogram:manageprograms', $programcontext)
        ) {
            $editlink = new url("/local/sebprogram/edit.php", $params);
            $icons[] = $OUTPUT->action_icon($editlink, new pix_icon(
                't/edit',
                get_string('edit'),
                'core',
                ['class' => $cssclass]
            ));

            $params['sesskey'] = sesskey();
            $deletelink = new url("/local/sebprogram/delete.php", $params);
            $icons[] = $OUTPUT->action_icon($deletelink, new pix_icon(
                't/delete',
                get_string('delete'),
                'core',
                ['data-action' => 'delete', 'data-id' => $program->get('id'), 'class' => $cssclass]
            ));
        }

        return $icons;
    }

    /**
     * Notify plugins that a program has been updated.
     *
     * @param int $programid Program id.
     */
    public static function program_updated($programid) {
        $modulesallowingprogram = program_module::get_records(['programid' => $programid]);

        $program = program::get_record(['id' => $programid]);
        $dependentprograms = $program->get_dependent(true);

        foreach ($dependentprograms as $dependentprogram) {
            $modulesallowingprogram = array_merge(
                $modulesallowingprogram,
                program_module::get_records(['programid' => $dependentprogram->get('id')])
            );
        }

        // Dispatch a hook to regenerate SEB config file of modules that allow updated programs.
        foreach ($modulesallowingprogram as $module) {
            $hook = new seb_regenerate_config_hook($module->get('cmid'));
            \core\di::get(\core\hook\manager::class)->dispatch($hook);
        }
    }

    /**
     * Returns programs available in a context.
     *
     * @param unknown $context The context.
     * @param boolean $onlyvisible  If true return only visible programs
     * @param boolean $onlytitle    If true returns program titles else returns program objects
     * @return array Programs available in a context.
     */
    public function get_available_programs($context, $course = null, $onlyvisible = false, $onlytitle = true) {
        global $DB;

        $where = ($onlyvisible ? ' AND visible = 1' : '');
        $siteprograms = program::get_records_select('contextid = ? ' . $where, [\context_system::instance()->id]);
        $siteprograms = array_map(function ($program) use ($onlytitle) {
            return ($onlytitle ? $program->get('title') : $program);
            ;
        }, $siteprograms);

        $contextprograms = program::get_records_select('contextid = ? ' . $where, [$context->id]);

        $editingteachersprograms = [];
        if ($course) {
            $coursecontext = \context_course::instance($course->id);
            $editingteacherroleid = $DB->get_field('role', 'id', ['shortname' => 'editingteacher'], MUST_EXIST);
            $editingteachers = get_role_users($editingteacherroleid, $coursecontext);
            foreach ($editingteachers as $editingteacher) {
                $editingteacherprograms = program::get_records_select(
                    'contextid = ? ' . $where,
                    [\context_user::instance($editingteacher->id)->id]
                );
                foreach ($editingteacherprograms as $index => $program) {
                    $alreadyexist = false;
                    foreach ($contextprograms + $editingteachersprograms as $contextprogram) {
                        if (
                            $contextprogram->get('title') == $program->get('title') &&
                            $contextprogram->get('executable') == $program->get('executable') &&
                            $contextprogram->get('originalname') == $program->get('originalname') &&
                            $contextprogram->get('path') == $program->get('path')
                        ) {
                            $alreadyexist = true;
                            break;
                        }
                    }
                    if (!$alreadyexist) {
                        $editingteachersprograms[$index] = $program;
                    }
                }
            }
        }

        $contextprograms = array_map(function ($program) use ($onlytitle) {
            return ($onlytitle ? $program->get_title_with_context() : $program);
            ;
        }, $contextprograms);

        $editingteachersprograms = array_map(function ($program) use ($onlytitle) {
            return ($onlytitle ? $program->get_title_with_context() : $program);
        }, $editingteachersprograms);

        $availableprograms = $siteprograms + $contextprograms + $editingteachersprograms;
        asort($availableprograms);

        return $availableprograms;
    }

    /**
     * Returns programs allowed for a module.
     *
     * @param int $cmid  Course module ID
     * @param boolean $onlytitle    If true returns program titles else returns program objects
     * @param boolean $withDependencies If true returs also programs that are required for allowed programs.
     * @return program[]|array[] Array of programs allowed
     */
    public function get_allowed_programs($cmid, $onlytitle = true, $withdependencies = false) {

        $moduleallowedprograms = program_module::get_records_select('cmid = ?', [$cmid]);

        $allowedprograms = [];
        foreach ($moduleallowedprograms as $moduleprogram) {
            $program = new program($moduleprogram->get('programid'));
            $allowedprograms[$program->get('id')] = ($onlytitle ? $program->get_title_with_context() : $program);
            if ($withdependencies) {
                $programdependencies = $program->get_dependencies(true);
                foreach ($programdependencies as $programrequired) {
                    if (!isset($allowedprograms[$programrequired->get('id')])) {
                        $allowedprograms[$programrequired->get('id')] = ($onlytitle ? $programrequired->get_title_with_context() : $programrequired);
                    }
                }
            }
        }

        return $allowedprograms;
    }

    /** Hook to include allowed programs selection field in module settings form.
     *
     * @param \MoodleQuickForm $mform Module settings form
     * @param int $cmid Course module id
     * @param array $hideifs Rules to show or hide control based on another form fields.
     *                       Array key is the field name and the value is the condition.
     *                       See $dependentOn, $condition in https://moodledev.io/docs/4.5/apis/subsystems/form#disabledif
     * @param string $before Name of the form element before which the seb program settings will be added
     */
    public function add_seb_programs_settings(\MoodleQuickForm $mform, $course, $cmid, array $hideifs = [], $before = '') {
        global $USER, $OUTPUT;

        $usercontext = \context_user::instance($USER->id);

        $availableprograms = $this->get_available_programs($usercontext, $course, true);

        $allowedprograms = [];
        if (get_coursemodule_from_id('', $cmid)) {
            $allowedprograms = $this->get_allowed_programs($cmid);
        }

        // Keep allowed programs of other teachers who are no longer teachers in the course.
        $allowednotavailable = array_diff_key($allowedprograms, $availableprograms);
        if (!empty($allowednotavailable)) {
            $availableprograms = $availableprograms + $allowednotavailable;
            asort($availableprograms);
        }

        $options = [
                'multiple' => true,
                'noselectionstring' => get_string('noprogramsallowed', 'local_sebprogram'),
                'placeholder' => get_string('chooseprograms', 'local_sebprogram'),
        ];

        $allowedprogramsgroup = [];
        $allowedprogramsgroup[] =& $mform->createElement(
            'autocomplete',
            'allowed_programs',
            get_string('allowedprograms', 'local_sebprogram'),
            $availableprograms,
            $options
        );
        $mform->setType('allowed_programs', PARAM_RAW);
        $mform->setDefault('allowed_programs', array_keys($allowedprograms));

        if (has_capability('local/sebprogram:manageprograms', \context_course::instance($course->id))) {
            $url = new url('/local/sebprogram/manage.php', ['returncmid' => $cmid]);
            $allowedprogramsgroup[] =& $mform->createElement(
                'html',
                $OUTPUT->action_link($url, get_string('manageprograms', 'local_sebprogram'))
            );
        }

        $mform->addGroup(
            $allowedprogramsgroup,
            'allowedprograms_group',
            get_string('allowedprograms', 'local_sebprogram'),
            ' ',
            false
        );
        $mform->addHelpButton('allowedprograms_group', 'allowedprograms', 'local_sebprogram');

        if (!empty($before)) {
            if ($mform->elementExists($before)) {
                $mform->insertElementBefore($mform->removeElement("allowedprograms_group", false), $before);
            }
        }

        foreach ($hideifs as $rule) {
            $mform->hideIf('allowedprograms_group', $rule['fieldname'], $rule['condition'], $rule['value']);
        }
    }

    /**
     * Print programs available in a context.
     *
     * @param int $contextid    The context ID
     * @param int $returncmid   The ID of the course module whose settings form to return to
     */
    public function print_program_list($contextid, $returncmid = 0, $hiddencolumns = null, $sortable = true) {
        $programs = program::get_context_programs($contextid, 'title', true);

        $table = new program_list('programs_list', \context::instance_by_id($contextid), $hiddencolumns, $sortable);
        $table->set_returncmid($returncmid);
        $table->display($programs);
    }

    /**
     * Download programs available in a context as csv.
     *
     * @param int $contextid    The context ID
     * @param int $returncmid   The ID of the course module whose settings form to return to
     */
    public function download_program_list($contextid, $returncmid = 0) {
        $programs = program::get_context_programs($contextid, 'title', true);

        $table = new program_list('programs_list', \context::instance_by_id($contextid), ['uses', 'actions'], false);
        $table->is_downloading('csv', 'seb_programs');
        $table->display($programs);
    }

    /**
     * Delete a program.
     *
     * @param program $program Program to delete
     */
    public function delete_program(program $program) {
        foreach ($program->get_dependencies() as $dependency) {
            $dependency->delete();
        }

        foreach ($program->get_program_allowed_modules() as $programmodule) {
            $programmodule->delete();
        }

        $program->delete();

        $dependencies = array_map(function ($program) {
            return $program->get('title');
        }, $program->get_dependencies());

        $event = \local_sebprogram\event\sebprogram_deleted::create([
                'objectid' => $program->get('id'),
                'context' => \context::instance_by_id($program->get('contextid')),
                'other' => ['title' => $program->get('title'),
                        'executable' => $program->get('executable'),
                        'originalname' => $program->get('originalname'),
                        'path' => $program->get('path'),
                        'visible' => $program->get('path'),
                        'dependencies' => implode(',', $dependencies)],
        ]);
        $event->add_record_snapshot('local_sebprogram_program', $program->to_record());
        $event->trigger();
    }
}
