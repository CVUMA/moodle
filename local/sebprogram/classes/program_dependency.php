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
 * Class for loading/storing programs that depends on another program from the DB.
 *
 * @package    local_sebprogram
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_sebprogram;

use core\persistent;

/**
 * Class for loading/storing a program and the program on which it depends from the DB.
 *
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class program_dependency extends persistent {
    /** Table name for the persistent. */
    const TABLE = 'local_sebprogram_program_dep';

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
            'requiredprogramid' => [
                'type' => PARAM_INT,
            ],
        ];
    }
}
