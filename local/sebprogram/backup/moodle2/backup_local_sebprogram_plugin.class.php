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
 * Defines the backup_local_sebprogram_plugin class.
 *
 * @package    local_sebprogram
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_sebprogram\program;
use local_sebprogram\program_module;

defined('MOODLE_INTERNAL') || die();

/**
 * Provides the information to backup SEB allowed programs.
 *
 * @package    local_sebprogram
 * @copyright  2024 Universidad de Málaga
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_local_sebprogram_plugin extends backup_local_plugin {
    /** @var array Allowed programs data. */
    private $allowedprogramsdata = [];

    /** @var array Allowed programs dependencies data. */
    private $dependenciesdata = [];

    /**
     * Defines the SEB programs structures to append.
     *
     * @return backup_plugin_element
     */
    protected function define_module_plugin_structure() {

        $plugin = $this->get_plugin_element(null);

        // Elements.
        $sebprogramplugin = new backup_nested_element($this->get_recommended_name());

        $programs = new backup_nested_element('seb_allowed_programs');

        $programelements = ['title', 'executable', 'originalname', 'path', 'visible', 'contextlevel', 'dependencies'];
        $program = new backup_nested_element('program', ['id'], $programelements);

        $dependencies = new backup_nested_element('seb_allowed_program_dependencies');

        $dependency = new backup_nested_element('dependency', ['id'], $programelements);

        $plugin->add_child($sebprogramplugin);

        $sebprogramplugin->add_child($dependencies);
        $dependencies->add_child($dependency);

        $sebprogramplugin->add_child($programs);
        $programs->add_child($program);

        $moduleprograms = program_module::get_records(['cmid' => $this->task->get_moduleid()]);

        foreach ($moduleprograms as $moduleprogram) {
            $sebprogram = program::get_record(['id' => $moduleprogram->get('programid')]);

            $this->allowed_programs_data[] = $this->get_programdata($sebprogram);
        }

        $program->set_source_array($this->allowed_programs_data);
        $dependency->set_source_array($this->dependencies_data);

        return $plugin;
    }

    /**
     * Get program data to backup.
     *
     * @param program $program Program to backup
     * @return string[] Program data
     */
    private function get_programdata(program $program): array {
        $context = \context::instance_by_id($program->get('contextid'));

        $programdependencies = $program->get_dependencies();
        $programdependenciesids = array_map(function ($program) {
            return $program->get('id');
        }, $programdependencies);

        if (!empty($programdependencies)) {
            $this->add_required_programs($programdependencies);
        }

        return ['id' => $program->get('id'),
            'title' => $program->get('title'),
            'executable' => $program->get('executable'),
            'originalname' => $program->get('originalname'),
            'path' => $program->get('path'),
            'visible' => $program->get('visible'),
            'contextlevel' => $context->contextlevel,
            'dependencies' => implode(',', $programdependenciesids),
        ];
    }

    /**
     * Add required programs data to backup.
     *
     * @param program[] $requiredprograms Required programs.
     */
    private function add_required_programs(array $requiredprograms) {
        foreach ($requiredprograms as $program) {
            if (!isset($this->dependencies_data[$program->get('id')])) {
                $this->dependencies_data[$program->get('id')] = $this->get_programdata($program);
            }
        }
    }
}
