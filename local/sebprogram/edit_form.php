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
 * This file contains the form to create and edit programs
 *
 * @package    local_sebprogram
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Program settings form.
 *
 */
class local_sebprogram_program_form extends \core\form\persistent {
    /** @var string Persistent class name. */
    protected static $persistentclass = 'local_sebprogram\\program';

    /** @var array Fields to remove from the persistent validation. */
    protected static $foreignfields = ['program_dependencies', 'returncmid'];

    /** @var array Current dependencies. */
    private $currentdependencies = [];

    /**
     * Form definition.
     */
    protected function definition() {
        $mform = $this->_form;

        $context = $this->_customdata['context'];

        $mform->addElement('hidden', 'returncmid', $this->_customdata['returncmid']);
        $mform->setType('returncmid', PARAM_INT);

        $mform->addElement('text', 'title', get_string('title', 'local_sebprogram'));
        $mform->addRule('title', get_string('missingtitle', 'local_sebprogram'), 'required', null, 'client');

        $mform->addElement('text', 'executable', get_string('executable', 'local_sebprogram'));
        $mform->addRule('executable', get_string('missingexecutable', 'local_sebprogram'), 'required', null, 'client');
        $mform->addHelpButton('executable', 'executable', 'local_sebprogram');

        $mform->addElement('text', 'originalname', get_string('originalname', 'local_sebprogram'));
        $mform->addHelpButton('originalname', 'originalname', 'local_sebprogram');

        $mform->addElement('text', 'path', get_string('path', 'local_sebprogram'));
        $mform->addHelpButton('path', 'path', 'local_sebprogram');

        $mform->addElement('selectyesno', 'visible', get_string('visible', 'local_sebprogram'));
        $mform->addHelpButton('visible', 'visible', 'local_sebprogram');

        $programid = $this->get_persistent()->get('id');

        $manager = new \local_sebprogram\program_manager();
        $availableprograms = $manager->get_available_programs($context);

        if ($programid) {
            unset($availableprograms[$programid]);
        }

        $options = [
                'multiple' => true,
                'noselectionstring' => get_string('nodependencies', 'local_sebprogram'),
                'placeholder' => get_string('chooseprograms', 'local_sebprogram'),
        ];
        $mform->addElement(
            'autocomplete',
            'program_dependencies',
            get_string('dependencies', 'local_sebprogram'),
            $availableprograms,
            $options
        );
        $mform->setType('program_dependencies', PARAM_RAW);
        $mform->addHelpButton('program_dependencies', 'dependencies', 'local_sebprogram');

        if ($programid > 0) {
            $dependencies = $this->get_persistent()->get_dependencies();

            $this->currentdependencies = array_map(fn($program) => $program->get('id'), $dependencies);

            $mform->setDefault('program_dependencies', $this->currentdependencies);
        }

        $this->add_action_buttons();
    }

    /**
     * Extra validation.
     *
     * @param  \stdClass $data Data to validate.
     * @param  array $files Array of files.
     * @param  array $errors Currently reported errors.
     * @return string[] of additional errors, or overridden errors.
     */
    protected function extra_validation($data, $files, array &$errors): array {
        global $DB;

        $newerrors = [];

        if (empty($data->title)) {
            $newerrors['title'] = get_string('missingtitle', 'quizaccess_sebprogram');
        } else {
            $conditions = ['contextid = ?', $DB->sql_compare_text('title') . ' = ?'];
            $params = [$this->_customdata['context']->id, $data->title];
            if ($this->get_persistent()) {
                $conditions[] = 'id <> ?';
                $params[] = $this->get_persistent()->get('id');
            }

            if (local_sebprogram\program::record_exists_select(implode(' AND ', $conditions), $params)) {
                $newerrors['title'] = get_string('duplicateprogram', 'local_sebprogram');
            }
        }

        if (empty($data->executable)) {
            $newerrors['executable'] = get_string('missingexecutable', 'quizaccess_sebprogram');
        }

        return $newerrors;
    }

    /**
     * Get the current program dependencies
     *
     * @return array The current program dependencies.
     */
    public function get_current_dependencies() {
        return $this->currentdependencies;
    }
}
