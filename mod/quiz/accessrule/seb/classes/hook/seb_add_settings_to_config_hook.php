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

namespace quizaccess_seb\hook;

use core\hook\described_hook;
use quizaccess_seb\property_list;

defined('MOODLE_INTERNAL') || die();

/**
 * Provides the ability to add settings to SEB config file
 *
 * @package    quizaccess_seb
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class seb_add_settings_to_config_hook implements described_hook {
    /**
     * Create a new instance of the hook.
     *
     * @param property_list $plist the SEB config represented as a Property List object.
     * @param int $cmid Course module id.
     */
    public function __construct(
        /** @var property_list the SEB config represented as a Property List object. */
        public property_list $plist,
        /** @var int course module id */
        public int $cmid
    ) {
    }

    /**
     * Describes the hook purpose.
     *
     * @return string
     */
    public static function get_hook_description(): string {
        return 'Add settings to SEB config file';
    }

    /**
     * List of tags that describe this hook.
     *
     * @return string[]
     */
    public static function get_hook_tags(): array {
        return ['seb', 'config'];
    }
}
