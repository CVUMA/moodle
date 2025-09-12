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

namespace local_sebprogram\local;

use CFPropertyList\CFArray;
use CFPropertyList\CFBoolean;
use CFPropertyList\CFDictionary;
use CFPropertyList\CFNumber;
use CFPropertyList\CFString;
use local_sebprogram\program_manager;
use local_sebprogram\program_module;
use quizaccess_seb\hook\seb_add_settings_to_config_hook;
use quizaccess_seb\hook\seb_add_form_settings_hook;
use quizaccess_seb\hook\seb_save_form_settings_hook;

/**
 * Hook listener for SEB programs
 *
 * @package    local_sebprogram
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class seb_hooks_handler {
    /**
     * Add allowed programs setting to the SEb settings form.
     *
     * @param seb_add_form_settings_hook $hook the add SEB form settings hook.
     */
    public static function add_form_settings(seb_add_form_settings_hook $hook): void {
        global $USER, $OUTPUT;

        $usercontext = \context_user::instance($USER->id);

        $manager = new program_manager();

        $availableprograms = $manager->get_available_programs($usercontext, $hook->course, true);

        $allowedprograms = [];
        if (get_coursemodule_from_id('', $hook->cmid)) {
            $allowedprograms = $manager->get_allowed_programs($hook->cmid);
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
        $allowedprogramsgroup[] =& $hook->mform->createElement(
            'autocomplete',
            'allowed_programs',
            get_string('allowedprograms', 'local_sebprogram'),
            $availableprograms,
            $options
        );
        $hook->mform->setType('allowed_programs', PARAM_RAW);
        $hook->mform->setDefault('allowed_programs', array_keys($allowedprograms));

        if (has_capability('local/sebprogram:manageprograms', \context_course::instance($hook->course->id))) {
            $url = new \moodle_url('/local/sebprogram/manage.php', ['returncmid' => $hook->cmid]);
            $allowedprogramsgroup[] =& $hook->mform->createElement(
                'html',
                $OUTPUT->action_link($url, get_string('manageprograms', 'local_sebprogram'))
            );
        }

        $hook->mform->addGroup(
            $allowedprogramsgroup,
            'allowedprograms_group',
            get_string('allowedprograms', 'local_sebprogram'),
            ' ',
            false
        );
        $hook->mform->addHelpButton('allowedprograms_group', 'allowedprograms', 'local_sebprogram');

        if (!empty($hook->before)) {
            if ($hook->mform->elementExists($hook->before)) {
                $hook->mform->insertElementBefore($hook->mform->removeElement("allowedprograms_group", false), $hook->before);
            }
        }

        foreach ($hook->hideifs as $rule) {
            $hook->mform->hideIf('allowedprograms_group', $rule['fieldname'], $rule['condition'], $rule['value']);
        }
    }

    /**
     * Save allowed programs setting from the SEB settings form.
     *
     * @param seb_save_form_settings_hook $hook The save SEB form settings hook.
     */
    public static function save_form_settings(seb_save_form_settings_hook $hook): void {

        $manager = new program_manager();

        $selectedprograms = $hook->data->allowed_programs;
        $currentprograms = array_keys($manager->get_allowed_programs($hook->data->coursemodule));

        $addprgrams = array_diff($selectedprograms, $currentprograms);
        $deleteprgrams = array_diff($currentprograms, $selectedprograms);

        $programmoduledata = new \stdClass();
        $programmoduledata->cmid = $hook->data->coursemodule;

        // Add new programs.
        foreach ($addprgrams as $programid) {
            $programmoduledata->programid = $programid;
            $programmodule = new program_module(0, $programmoduledata);
            $programmodule->create();
        }

        // Delete removed programs.
        foreach ($deleteprgrams as $programid) {
            $programmodule = program_module::get_record(['cmid' => $programmoduledata->cmid, 'programid' => $programid]);
            $programmodule->delete();
        }
    }

    /**
     * Add allowed programs to the SEB config file.
     *
     * @param seb_add_settings_to_config_hook $hook the add settings to SEB config file hook.
     */
    public static function add_config_settings(seb_add_settings_to_config_hook $hook): void {

        $manager = new program_manager();
        $allowedprograms = $manager->get_allowed_programs($hook->cmid, false, true);
        if (!empty($allowedprograms)) {
            $dictprogram = [];
            foreach ($allowedprograms as $program) {
                $dictprogram[] = self::create_allowed_program_rule(
                    true,
                    $program->get('title'),
                    $program->get('executable'),
                    $program->get('originalname'),
                    $program->get('visible'),
                    $program->get('path')
                );
            }
            $hook->plist->add_element_to_root('permittedProcesses', new CFArray($dictprogram));
        }
    }

    /**
     * Create a CFDictionary represeting a allowed program.
     *
     * @param bool $active Program is active.
     * @param string $titlestring Program title.
     * @param string $exestring Program executable.
     * @param string $namestring Program name.
     * @param bool $visible Program is visible.
     * @param string $pathstring Program path.
     * @return CFDictionary A PList dictionary.
     */
    private static function create_allowed_program_rule(
        bool $active,
        string $titlestring,
        string $exestring,
        string $namestring,
        bool $visible,
        string $pathstring = ''
    ): CFDictionary {

        return new CFDictionary([
            'active' => new CFBoolean($active),
            'autostart' => new CFBoolean(false),
            'iconInTaskbar' => new CFBoolean($visible),
            'runInBackground' => new CFBoolean(false),
            'allowUserToChooseApp' => new CFBoolean(false),
            'strongKill' => new CFBoolean(false),
            'os' => new CFNumber('1'),
            'title' => new CFString($titlestring),
            'description' => new CFString(''),
            'executable' => new CFString($exestring),
            'originalName' => new CFString($namestring),
            'windowHandlingProcess' => new CFString(''),
            'path' => new CFString($pathstring),
            'identifier' => new CFString(''),
            'arguments' => new CFArray(),
        ]);
    }
}
