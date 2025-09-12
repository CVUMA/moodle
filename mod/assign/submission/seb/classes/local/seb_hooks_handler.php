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
 * Hook callbacks to regenerate SEB config.
 *
 * @package    assignsubmission_seb
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_seb\local;

use assignsubmission_seb\seb_access_manager;
use assignsubmission_seb\seb_assign_settings;
use quizaccess_seb\hook\seb_regenerate_config_hook;
/**
 * Hook handler to regenerate SEB config.
 *
 * @package    assignsubmission_seb
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class seb_hooks_handler {
    /**
     * Callback for the regenerate seb config hook.
     *
     * @param seb_regenerate_config_hook $hook
     */
    public static function regenerate_seb_config(seb_regenerate_config_hook $hook): void {

        $cminfo = get_coursemodule_from_id('', $hook->cmid);
        if ($cminfo->modname == 'assign') {
            $assignsettings = seb_assign_settings::get_by_assign_id($cminfo->instance);
            if ($assignsettings) {
                $assignsettings->regenerate_config();
            }
        }
    }

    /**
     * Callback to allow modify headers.
     *
     * @param \core\hook\output\before_http_headers $hook
     */
    public static function before_http_headers(\core\hook\output\before_http_headers $hook): void {
        global $PAGE;

        if (strpos($PAGE->url->get_path(), '/mod/assign/') !== false) {
            $cmid = $PAGE->url->get_param('id');
            $context = \context_module::instance($cmid);
            $assign = new \assign($context, null, null);
            $sebaccessmanager = new seb_access_manager($assign);
            if (
                $sebaccessmanager->seb_required()
                && $sebaccessmanager->is_using_seb()
            ) {
                    $PAGE->set_pagelayout('secure');
                    $PAGE->blocks->show_only_fake_blocks();
            }
        }
    }
}
