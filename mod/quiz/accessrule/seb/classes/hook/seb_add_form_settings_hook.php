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
use MoodleQuickForm;
use quizaccess_seb\hideif_rule;

defined('MOODLE_INTERNAL') || die();

/**
 * Provides the ability to add settings to SEB settings form
 *
 * @package    quizaccess_seb
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class seb_add_form_settings_hook implements described_hook {
    /**
     * Create a new instance of the hook.
     *
     * @param \MoodleQuickForm $mform Module settings form.
     * @param \stdClass $course The course object.
     * @param int $cmid Course module id.
     * @param hideif_rule[] $hideifs Rules to show or hide control based on another form fields.
     *                       Array key is the field name and the value is the condition.
     *                       See $dependentOn, $condition in https://moodledev.io/docs/4.5/apis/subsystems/form#disabledif
     * @param string $before Name of the form element before which the settings will be added.
     */
    public function __construct(
        /** @var MoodleQuickForm module settings form */
        public MoodleQuickForm &$mform,
        /** @var \stdClass the course object */
        public \stdClass $course,
        /** @var int course module id */
        public int $cmid,
        /** @var array rules to show or hide control based on another form fields */
        public array $hideifs,
        /** @var string name of the form element before which the settings will be added */
        public string $before
    ) {
    }

    /**
     * Describes the hook purpose.
     *
     * @return string
     */
    public static function get_hook_description(): string {
        return 'Add settings to SEB settings form';
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
