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
 * Defines the restore_local_sebprogram_plugin class.
 *
 * @package    local_sebprogram
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_sebprogram\program;
use local_sebprogram\program_dependency;
use local_sebprogram\program_module;

defined('MOODLE_INTERNAL') || die();

/**
 * Provides the information to restore SEB allowed programs.
 *
 * @package    local_sebprogram
 * @copyright  2024 Universidad de Málaga
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_local_sebprogram_plugin extends restore_local_plugin {
    /**
     * Declares the local_sebprogram paths.
     *
     * @return restore_path_element[]
     */
    protected function define_module_plugin_structure() {

        $paths = [];
        $paths[] = new restore_path_element('dependencies', $this->get_pathfor('seb_allowed_program_dependencies/dependency'));
        $paths[] = new restore_path_element('allowed_program', $this->get_pathfor('/seb_allowed_programs/program'));

        return $paths;
    }

    /**
     * Process the restored data for the program dependencies.
     *
     * @param stdClass $data Data for program dependencies retrieved from backup xml.
     */
    public function process_dependencies($data) {
        $this->process_program($data);
    }

    /**
     * Process the restored data for the allowed program.
     *
     * @param stdClass $data Data for allowed programs retrieved from backup xml.
     */
    public function process_allowed_program($data) {

        $this->process_program($data);

        $programmoduledata = new \stdClass();
        $programmoduledata->cmid = $this->task->get_moduleid();
        $programmoduledata->programid = $this->get_mappingid('program', $data['id']);
        $programmodule = new program_module(0, $programmoduledata);
        $programmodule->create();
    }

    /**
     * Process the restored data for the program.
     *
     * @param stdClass $data Data for program retrieved from backup xml.
     */
    public function process_program($data) {
        global $USER;

        $oldid = $data['id'];

        $context = ($data['contextlevel'] == CONTEXT_USER ? \context_user::instance($USER->id) : \context_system::instance());
        $visibility = $data['visible'];

        $programdependencies = $data['dependencies'];

        unset($data['id']);
        unset($data['visible']);
        unset($data['contextlevel']);
        unset($data['dependencies']);
        $sqlconditions = array_map(function ($fieldname) {
            return $fieldname . ' = ?';
        }, array_keys($data));
        $select = implode(' AND ', $sqlconditions);

        $createnewprogram = true;
        if ($programs = program::get_records_select($select, $data)) {
            $newprogram = reset($programs);
            $newprogramdependencies = $newprogram->get_dependencies();
            $newprogramdependenciesids = array_map(function ($program) {
                return $program->get('id');
            }, $newprogramdependencies);

            $oldprogramdependenciesids = (empty($programdependencies) ? [] : explode(',', $programdependencies));
            $oldprogramdependenciesmappedids = [];
            foreach ($oldprogramdependenciesids as $id) {
                $oldprogramdependenciesmappedids[] = $this->get_mappingid('program', $id, $id);
            }

            sort($newprogramdependenciesids);
            sort($oldprogramdependenciesids);
            sort($oldprogramdependenciesmappedids);

            if (
                $newprogramdependenciesids === $oldprogramdependenciesids ||
                    $newprogramdependenciesids === $oldprogramdependenciesmappedids
            ) {
                $createnewprogram = false;
            }
        }

        if ($createnewprogram) {
            $data['contextid'] = $context->id;
            $data['visible'] = $visibility;
            $newprogram = new program(0, (object)$data);
            $newprogram->create();

            if (!empty($programdependencies)) {
                $programdependencydata = new \stdClass();
                $programdependencydata->programid = $newprogram->get('id');

                foreach (explode(',', $programdependencies) as $programdependency) {
                    $requiredprogramid = $this->get_mappingid('program', $programdependency);
                    $programdependencydata->requiredprogramid = $requiredprogramid;
                    $programdependency = new program_dependency(0, $programdependencydata);
                    $programdependency->create();
                }
            }
        }

        $this->set_mapping('program', $oldid, $newprogram->get('id'));
    }
}
