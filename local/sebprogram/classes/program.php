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
 * Class for loading/storing programs from the DB.
 *
 * @package    local_sebprogram
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_sebprogram;

use core\persistent;

/**
 * Class for loading/storing programs from the DB.
 */
class program extends persistent {
    /** Table name for the persistent. */
    const TABLE = 'local_sebprogram_program';

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'title' => [
                'type' => PARAM_TEXT,
                'default' => '',
            ],
            'executable' => [
                'type' => PARAM_TEXT,
                'default' => '',
            ],
            'originalname' => [
                'type' => PARAM_TEXT,
            ],
            'path' => [
                'type' => PARAM_TEXT,
                'default' => '',
            ],
            'visible' => [
                'type' => PARAM_INT,
                'default' => 1,
            ],
            'contextid' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'uses' => [
                    'type' => PARAM_INT,
                    'default' => 0,
            ],
            'requiredprograms' => [
                    'type' => PARAM_RAW,
                    'default' => '',
            ],
        ];
    }


    /**
     * Return the number of modules that allow the program.
     *
     * @return int number of modules
     */
    protected function get_uses() {
        return program_module::count_records(['programid' => $this->get('id')]);
    }

    /**
     * Return the program title with its context.
     *
     * Program context can be system context (added in site administration), user context (added by current user)
     * or other (added by other user).
     *
     * @return string Title with context
     */
    public function get_title_with_context(): string {
        global $USER;

        $usercontext = \context_user::instance($USER->id);
        $titlecontext = '';
        if ($this->get('contextid') == $usercontext->id) {
            $titlecontext = ' (' . get_string('programcontextuser', 'local_sebprogram') . ')';
        } else if ($this->get('contextid') <> \context_system::instance()->id) {
            $titlecontext = ' (' . get_string('programcontexcourseteacher', 'local_sebprogram') . ')';
        }

        return $this->get('title') . $titlecontext;
    }

    /**
     * Check if we can delete the programs.
     *
     * @return bool
     */
    public function can_delete(): bool {
        $result = true;

        if ($this->get('id')) {
            if (
                program_dependency::record_exists_select('requiredpregramid = ?', [$this->get('id')])
                || program_module::record_exists_select('programid = ?', [$this->get('id')])
            ) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Get program dependencies.
     *
     * @param bool $recursive if true returns also the dependencies of the dependencies
     * @return program[] Dependencies for the program.
     */
    public function get_dependencies(bool $recursive = false): array {

        if ($this->get('id')) {
            $dependencies = program_dependency::get_records_select('programid = ?', [$this->get('id')]);

            $dependencies = array_map(
                fn($programdependency) => new program($programdependency->get('requiredprogramid')),
                $dependencies
            );
        }

        if ($recursive) {
            $alldependencies = [];
            foreach ($dependencies as $dependencyprogram) {
                $alldependencies = array_merge($alldependencies, $dependencyprogram->get_dependencies(true));
            }
            $dependencies = array_merge($dependencies, $alldependencies);
        }

        return $dependencies;
    }

    /**
     * Get dependent programs.
     *
     * @param bool $recursive if true returns also the dependent of the dependent
     * @return program[] Programs that depend on this one.
     */
    public function get_dependent(bool $recursive = false): array {
        $dependent = [];

        if ($this->get('id')) {
            $dependent = program_dependency::get_records_select('requiredprogramid = ?', [$this->get('id')]);

            $dependent = array_map(fn($programdependency) => new program($programdependency->get('programid')), $dependent);
        }

        if ($recursive) {
            $alldependentprograms = [];
            foreach ($dependent as $dependentprogram) {
                $alldependentprograms = array_merge($alldependentprograms, $dependentprogram->get_dependent(true));
            }
            $dependent = array_merge($dependent, $alldependentprograms);
        }

        return $dependent;
    }

    /**
     * Get modules that allow this program.
     *
     * @return program_module[]
     */
    public function get_program_allowed_modules(): array {
        $programmodules = [];

        if ($this->get('id')) {
            $programmodules = program_module::get_records_select('programid = ?', [$this->get('id')]);
        }

        return $programmodules;
    }

    /**
     * Get programs for a context.
     *
     * @param int $contextid      The id of the context for which to get programs.
     * @param string $sort        Sort programs by this field.
     * @param bool $returnrecords if true return Program objets else only program titles.
     * @return program[]|string[] Programs for the context.
     */
    public static function get_context_programs($contextid, $sort = '', $returnrecords = false): array {
        global $DB;

        $records = self::get_records_select('contextid = ?', [$contextid], $sort);

        if ($returnrecords) {
            return $records;
        }

        $programs = [];
        foreach ($records as $program) {
            $programs[] = new static(0, $program);
        }

        return $programs;
    }
}
