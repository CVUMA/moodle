<?php
// This file is part of Moodle - https://moodle.org/
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

namespace quizaccess_seb\local;


use quizaccess_seb\hook\seb_regenerate_config_hook;

/**
 * Hook callbacks to handle SEB callbacks.
 *
 * @package    quizaccess_seb
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class seb_hooks_handler {
    /**
     * Callback for seb_regenerate_config_hook
     * @param seb_regenerate_config_hook $hook
     */
    public static function regenerate_seb_config(seb_regenerate_config_hook $hook): void {
        $cminfo = get_coursemodule_from_id('', $hook->cmid);
        if ($cminfo->modname == 'quiz') {
            $quizsettings = \quizaccess_seb\seb_quiz_settings::get_by_quiz_id($cminfo->instance);
            if ($quizsettings) {
                $quizsettings->regenerate_config();
            }
        }
    }
}
