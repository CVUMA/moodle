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

defined('MOODLE_INTERNAL') || die();

/**
 * Provides the ability to save settings from SEB settings form
 *
 * @package    quizaccess_seb
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class seb_save_form_settings_hook implements described_hook {
    /**
     * Create a new instance of the hook.
     *
     * @param \stdClass $data Data from the form.
     */
    public function __construct(
        /** @var \stdClass data from the form */
        public \stdClass $data
    ) {
    }

    /**
     * Describes the hook purpose.
     *
     * @return string
     */
    public static function get_hook_description(): string {
        return 'Save SEB form settings';
    }

    /**
     * List of tags that describe this hook.
     *
     * @return string[]
     */
    public static function get_hook_tags(): array {
        return ['seb', 'form'];
    }
}
