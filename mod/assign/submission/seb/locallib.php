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
 * This file contains the definition for the library class for seb submission plugin
 *
 * This class provides all the functionality for the new assign module.
 *
 * @package    assignsubmission_seb
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use assignsubmission_seb\link_generator;
use assignsubmission_seb\seb_access_manager;
use assignsubmission_seb\seb_assign_settings;
use assignsubmission_seb\settings_provider;
use assignsubmission_seb\event\access_prevented;
use core\context;
use core\url;
use core\output\html_writer;
use quizaccess_seb\hook\seb_save_form_settings_hook;

defined('MOODLE_INTERNAL') || die();

// File area for seb submission assignment.
define('ASSIGNSUBMISSION_SEB_FILEAREA', 'assignsubmissions_seb');

/**
 * library class for seb submission plugin extending submission plugin base class
 *
 * @package    assignsubmission_seb
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_submission_seb extends assign_submission_plugin {
    /** @var seb_access_manager $accessmanager Instance to manage the access to the assign for this plugin. */
    protected ?seb_access_manager $accessmanager = null;

    /** Returns assign access manager.
     *
     * @return seb_access_manager Access manager
     */
    private function get_seb_accessmanager(): seb_access_manager {
        if ($this->accessmanager == null) {
            $this->accessmanager = new seb_access_manager($this->assignment);
        }

        return $this->accessmanager;
    }

    /**
     * Get a list of file areas associated with the plugin configuration.
     * This is used for backup/restore.
     *
     * @return array names of the fileareas, can be an empty array
     */
    public function get_config_file_areas() {
        return ['filemanager_sebconfigfile'];
    }

    /**
     * Overwrite this method to show launch seb button to students if seb is enabled.
     *
     * @return bool - if false - this plugin will not accept submissions
     */
    public function is_enabled() {
        global $USER, $DB, $PAGE, $SESSION, $CFG;

        // CAM-5020 Evitamos un error cuando se añade una tarea y no se selecciona ningún tipo de entrega (mestebanez - 11.2024)
        // Hack: No se comprueba nada si se llama a este método mientras se añade una tarea ya que
        // todavía no se ha guardado la información en la BD y al intentar recuperarla más adelante en la función da error.
        if ($PAGE->url->get_path() == '/course/modedit.php' && $PAGE->url->get_param('add') == 'assign') {
            return parent::is_enabled();
        }

        if (
            $this->get_seb_accessmanager()->seb_required()
                && $this->get_seb_accessmanager()->is_using_seb()
        ) {
            if (!isset($SESSION->seb_starttime)) {
                $SESSION->seb_starttime = 1000 * time();
            }
            $PAGE->requires->js_amd_inline("
                require(['jquery', 'core/ajax'], function($, Ajax) {
                    // warninglimit igual que en lib/amd/src/network.js, linea 34
                    var warninglimit = (Math.min((" . $CFG->sessiontimeout . " / 10), 600)) * 2;
                    var keepAliveFrequency = ((" . $CFG->sessiontimeout . " - (warninglimit + 60)) * 1000);
                    var seb_starttime = " . $SESSION->seb_starttime . ";
                    var maxRenewTime = 3 * keepAliveFrequency;
                    var renewSession = function() {
                        if (new Date().getTime() - seb_starttime < maxRenewTime) {
                            var request = {
                                methodname: 'core_session_touch',
                                args: { }
                            };

                            return Ajax.call([request], true, true, false)[0].then(function() {
                                setTimeout(renewSession, keepAliveFrequency);
                                return true;
                            }).fail(function() {
                                setTimeout(renewSession, keepAliveFrequency);
                                return true;
                            });
                        }
                    }
                    renewSession();
                });
            ");
        }

        if (
            parent::is_enabled()
            && $this->get_seb_accessmanager()->seb_required()
            && (!$this->get_seb_accessmanager()->is_using_seb() || !$this->get_seb_accessmanager()->validate_session_access())
        ) {
            $launchseb = false;
            $action = optional_param('action', 'view', PARAM_ALPHA);

            // For students SEB is launched always except when viewing submissions page.
            $submission = $this->assignment->get_user_submission($USER->id, true);
            $assignnotsubmitted = (!$submission || $submission->status != ASSIGN_SUBMISSION_STATUS_SUBMITTED);

            $context = \context_course::instance($this->assignment->get_course()->id);
            $isstudent = false;
            $isteacher = false;
            $isswitchedtostudentrole = false;
            if (is_role_switched($this->assignment->get_course()->id)) {
                if ($switchedrole = $DB->get_record('role', ['id' => $USER->access['rsw'][$context->path]])) {
                    if ('student' == $switchedrole->shortname) {
                        $isswitchedtostudentrole = true;
                    }
                }
            }

            $teacherroleids = array_keys(get_archetype_roles('editingteacher') + get_archetype_roles('teacher'));
            foreach ($teacherroleids as $roleid) {
                if ($isteacher = user_has_role_assignment($USER->id, $roleid, $context->id)) {
                    break;
                }
            }

            if (!$isteacher) {
                $studentroleids = array_keys(get_archetype_roles('student'));
                foreach ($studentroleids as $roleid) {
                    if ($isstudent = user_has_role_assignment($USER->id, $roleid, $context->id)) {
                        break;
                    }
                }
            }

            if (
                ($isstudent || $isswitchedtostudentrole)
                && ('view' <> $action || $assignnotsubmitted)
            ) {
                $launchseb = true;
            }

            // For teachers SEB is launched when adding, modifying or deleting own submissions.
            if (
                $isteacher
                && in_array($action, ['editsubmission', 'removesubmission', 'removesubmissionconfirm'])
            ) {
                $userid = optional_param('userid', $USER->id, PARAM_INT);
                if ($userid == $USER->id) {
                    $launchseb = true;
                }
            }

            if ($launchseb) {
                redirect(new url(
                    '/mod/assign/submission/seb/launch_seb.php',
                    ['id' => $this->assignment->get_course_module()->id]
                ));
            }
        }

        return parent::is_enabled();
    }

    /**
     * If this plugin should not include a column in the grading table or a row on the summary page
     * then return false
     *
     * @return bool
     */
    public function has_user_summary() {
        return false;
    }

    /**
     * Get the name of the seb submission plugin
     * @return string
     */
    public function get_name() {
        return get_string('seb', 'assignsubmission_seb');
    }

    /**
     * This allows a plugin to render an introductory section which is displayed
     * right below the activity's "intro" section on the main assignment page.
     *
     * @return string
     */
    public function view_header() {
        $output = '';

        $messages = $this->get_launch_seb_messages();

        foreach ($messages as $message) {
            $output .= html_writer::tag('p', $message, ['class' => 'text-start']);
        }

        return $output;
    }

    /**
     * Get the settings for seb submission plugin
     *
     * @param MoodleQuickForm $mform The form to add elements to
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform) {
        settings_provider::add_seb_settings_fields($this->assignment, $mform);
        if (!empty($this->assignment->get_default_instance())) {
            foreach ($this->get_config() as $name => $value) {
                $mform->setDefault("seb_$name", $value);
            }
        }

        $mform->addFormRule([$this, 'custom_validation']);
    }

    /**
     * Validate the data from form fields.
     *
     * If the managing user cannot configure SEB by either lack of permissions or locked
     * settings, then the form fields will be frozen and no validation will be done.
     *
     * @param array $data the submitted form data.
     * @param array $files information about any uploaded files.
     * @return array the array of errors.
     */
    public function custom_validation($data, $files) {
        $errors = [];

        $assignid = $data['instance'];
        $cmid = $data['coursemodule'];
        $courseid = $data['course'];
        if ($cmid) {
            $context = \context_module::instance($cmid);
        } else {
            $context = \context_course::instance($courseid);
        }

        if (!settings_provider::can_configure_seb($context)) {
            return $errors;
        }

        if (settings_provider::is_seb_settings_locked($this->assignment)) {
            return $errors;
        }

        if (settings_provider::is_conflicting_permissions($context)) {
            return $errors;
        }

        $settings = settings_provider::filter_plugin_settings((object) $data);

        // Validate basic settings using persistent class.
        $assignsettings = (new seb_assign_settings())->from_record($settings);
        // Set non-form fields.
        $assignsettings->set('assignid', $assignid);
        $assignsettings->set('cmid', $cmid);

        // Add any errors to list.
        foreach ($assignsettings->get_errors() as $name => $error) {
            $name = settings_provider::add_prefix($name); // Re-add prefix to match form element.
            $errors[$name] = $error->out();
        }

        // Edge case for filemanager_sebconfig.
        if ($assignsettings->get('requiresafeexambrowser') == settings_provider::USE_SEB_UPLOAD_CONFIG) {
            $errorvalidatefile = settings_provider::validate_draftarea_configfile($data['filemanager_sebconfigfile']);
            if (!empty($errorvalidatefile)) {
                $errors['filemanager_sebconfigfile'] = $errorvalidatefile;
            }
        }

        // Edge case to force user to select a template.
        if ($assignsettings->get('requiresafeexambrowser') == settings_provider::USE_SEB_TEMPLATE) {
            if (empty($data['seb_templateid'])) {
                $errors['seb_templateid'] = get_string('invalidtemplate', 'assignsubmission_seb');
            }
        }

        return $errors;
    }

    /**
     * Save the settings for seb submission plugin
     *
     * @param stdClass $data
     * @return bool
     */
    public function save_settings(stdClass $data) {
        if (!settings_provider::can_configure_seb($this->assignment->get_context())) {
            return true;
        }

        $isnew = !empty($data->add) || empty($data->instance);

        if (!$isnew) {
            if (settings_provider::is_seb_settings_locked($this->assignment)) {
                return true;
            }

            if (settings_provider::is_conflicting_permissions($this->assignment->get_context())) {
                return true;
            }
        }

        $settings = settings_provider::filter_plugin_settings($data);
        $settings->assignid = $this->assignment->get_instance()->id;
        $settings->cmid = $data->coursemodule;

        // Get existing settings or create new settings if none exist.
        if ($isnew) {
            unset($settings->assignid);
            $assignsettings = new seb_assign_settings(0, $settings);
            $settings->assignid = $this->assignment->get_instance()->id;
            $assignsettings->set('assignid', $settings->assignid);
        } else {
            $assignsettings = seb_assign_settings::get_by_assign_id($settings->assignid);
            $assignsettings->from_record($settings);
        }

        // Process uploaded files if required.
        if ($assignsettings->get('requiresafeexambrowser') == settings_provider::USE_SEB_UPLOAD_CONFIG) {
            $draftitemid = file_get_submitted_draft_itemid('filemanager_sebconfigfile');
            settings_provider::save_filemanager_sebconfigfile_draftarea($draftitemid, $data->coursemodule);
        } else {
            settings_provider::delete_uploaded_config_file($data->coursemodule);
        }

        if ($assignsettings->get('requiresafeexambrowser') == settings_provider::USE_SEB_NO) {
            $assignsettings->delete_config();
        }

        // Dispatch a hook for plugins to save their settings.
        $hook = new seb_save_form_settings_hook($data);
        \core\di::get(\core\hook\manager::class)->dispatch($hook);

        $assignsettings->save($this);

        return true;
    }

    /**
     * Upgrade the settings from the old assignment to the new plugin based one
     *
     * @param context $oldcontext - the database for the old assignment context
     * @param stdClass $oldassignment - the database for the old assignment instance
     * @param string $log record log events here
     * @return bool Was it a success?
     */
    public function upgrade_settings(context $oldcontext, stdClass $oldassignment, &$log) {
        // No settings to upgrade.
        return true;
    }

    /**
     * Upgrade the submission from the old assignment to the new one
     *
     * @param context $oldcontext - the database for the old assignment context
     * @param stdClass $oldassignment The data record for the old assignment
     * @param stdClass $oldsubmission The data record for the old submission
     * @param stdClass $submission The data record for the new submission
     * @param string $log Record upgrade messages in the log
     * @return bool true or false - false will trigger a rollback
     */
    public function upgrade(context $oldcontext, stdClass $oldassignment, stdClass $oldsubmission, stdClass $submission, &$log) {
        return true;
    }

    /**
     * The assignment has been deleted - cleanup
     *
     * @return bool
     */
    public function delete_instance() {
        return true;
    }

    /**
     * The submission seb plugin has no submission component so should not be counted
     * when determining whether to show the edit submission link.
     * @return boolean
     */
    public function allow_submissions() {
        return false;
    }

    /**
     * Determine if a submission is empty
     *
     * This is distinct from is_empty in that it is intended to be used to
     * determine if a submission made before saving is empty.
     *
     * @param stdClass $data The submission data
     * @return bool
     */
    public function submission_is_empty(stdClass $data) {
        return true;
    }

    /**
     * Return messages to display with launch seb button.
     *
     * @return string[] Array of messages to display
     */
    public function get_launch_seb_messages() {
        global $PAGE;

        $messages = [get_string('sebrequired', 'assignsubmission_seb')];

        // Display download SEB config link for those who can bypass using SEB.
        if ($this->get_seb_accessmanager()->can_bypass_seb() && $this->get_seb_accessmanager()->should_validate_config_key()) {
            $messages[] = $this->display_buttons($this->get_download_config_button());
        }

        if (!$this->prevent_access()) {
            $messages[] = $this->display_buttons($this->get_quit_button());
        } else {
            $PAGE->requires->js_call_amd(
                'assignsubmission_seb/validate_assign_access',
                'init',
                [$this->assignment->get_course_module()->id, (bool)get_config('assignsubmission_seb', 'autoreconfigureseb')]
            );
        }

        return $messages;
    }

    /**
     * Returns launch seb buttons HTML code for being displayed on the screen.
     *
     *
     * @return string HTML code of the provided buttons.
     */
    public function get_launch_seb_buttons() {
        $output = '';

        if (
            $this->get_seb_accessmanager()->should_validate_basic_header()
            && !$this->get_seb_accessmanager()->validate_basic_header()
        ) {
            access_prevented::create_strict($this->get_seb_accessmanager(), $this->get_reason_text('not_seb'))->trigger();
            $output = $this->get_require_seb_error_message();
        } else if (
            $this->get_seb_accessmanager()->should_validate_config_key()
            && !$this->get_seb_accessmanager()->validate_config_key()
        ) {
            if ($this->should_redirect_to_seb_config_link()) {
                $this->redirect_to_seb_config_link();
            }

            access_prevented::create_strict(
                $this->get_seb_accessmanager(),
                $this->get_reason_text('invalid_config_key')
            )->trigger();
            $output = $this->get_invalid_key_error_message();
        } else if (
            $this->get_seb_accessmanager()->should_validate_browser_exam_key()
            && !$this->get_seb_accessmanager()->validate_browser_exam_key()
        ) {
            access_prevented::create_strict(
                $this->get_seb_accessmanager(),
                $this->get_reason_text('invalid_browser_key')
            )->trigger();
            $output = $this->get_invalid_key_error_message();
        }

        return $output;
    }

    /**
     * Returns reason for access prevention as a text.
     *
     * @param string $identifier Reason string identifier.
     * @return string
     */
    private function get_reason_text(string $identifier): string {
        if (in_array($identifier, ['not_seb', 'invalid_config_key', 'invalid_browser_key'])) {
            return get_string($identifier, 'assignsubmission_seb');
        }

        return get_string('unknown_reason', 'assignsubmission_seb');
    }

    /**
     * Return error message when a SEB key is not valid.
     *
     * @return string
     */
    private function get_invalid_key_error_message(): string {
        // Return error message with download link and links to get the seb config.
        return ($this->get_seb_accessmanager()->is_using_seb() ? get_string('invalidkeys', 'assignsubmission_seb') : '')
        . $this->display_buttons($this->get_action_buttons());
    }

    /**
     * Return error message when a SEB browser is not used.
     *
     * @return string
     */
    private function get_require_seb_error_message(): string {
        $message = get_string('clientrequiresseb', 'assignsubmission_seb');

        if ($this->should_display_download_seb_link()) {
            $message .= $this->display_buttons($this->get_download_seb_button());
        }

        // Return error message with download link.
        return $message;
    }

    /**
     * Check if we should display a link to download Safe Exam Browser.
     *
     * @return bool
     */
    private function should_display_download_seb_link(): bool {
        return !empty($this->get_config('showsebdownloadlink'));
    }

    /**
     * Prepare buttons HTML code for being displayed on the screen.
     *
     * @param string $buttonshtml Html string of the buttons.
     * @param string $class Optional CSS class (or classes as space-separated list)
     * @param array $attributes Optional other attributes as array
     *
     * @return string HTML code of the provided buttons.
     */
    private function display_buttons(string $buttonshtml, $class = '', ?array $attributes = null): string {
        $html = '';

        if (!empty($buttonshtml)) {
            $html = html_writer::div($buttonshtml, $class, $attributes);
        }

        return $html;
    }

    /**
     * Get buttons to prompt user to download SEB or config file or launch SEB.
     *
     * @return string Html block of all action buttons.
     */
    private function get_action_buttons(): string {
        $buttons = '';

        if ($this->should_display_download_seb_link()) {
            $buttons .= $this->get_download_seb_button();
        }

        // Get config for displaying links.
        $linkconfig = explode(',', get_config('assignsubmission_seb', 'showseblinks'));

        // Display links to download config/launch SEB only if required.
        if ($this->get_seb_accessmanager()->should_validate_config_key()) {
            if (in_array('seb', $linkconfig)) {
                $buttons .= $this->get_launch_seb_button();
            }

            if (in_array('http', $linkconfig)) {
                $buttons .= $this->get_download_config_button();
            }
        }

        return html_writer::div($buttons, 'assignsubmission_seb_buttons');
        ;
    }

    /**
     * Get a button to download Safe Exam Browser config.
     *
     * @return string A link to launch Safe Exam Browser.
     */
    private function get_download_config_button(): string {

        $httplink = link_generator::get_link($this->assignment->get_course_module()->id, false, is_https());

        $buttonlink = html_writer::start_tag('div', ['class' => 'singlebutton']);
        $buttonlink .= html_writer::link(
            $httplink,
            get_string('httplinkbutton', 'assignsubmission_seb'),
            ['class' => 'btn btn-secondary', 'title' => get_string('httplinkbutton', 'assignsubmission_seb')]
        );
        $buttonlink .= html_writer::end_tag('div');

        return $buttonlink;
    }

    /**
     * Get a button to launch Safe Exam Browser.
     *
     * @return string A link to launch Safe Exam Browser.
     */
    private function get_launch_seb_button(): string {
        global $OUTPUT;

        $seblink = link_generator::get_link($this->assignment->get_course_module()->id, true, is_https());
        return $OUTPUT->single_button($seblink, get_string('seblinkbutton', 'assignsubmission_seb'), 'get');
    }

    /**
     * Get a button to download SEB.
     *
     * @return string A link to download SafeExam Browser.
     */
    private function get_download_seb_button(): string {
        global $OUTPUT;

        $button = '';

        if (!empty($this->get_seb_download_url())) {
            $button = $OUTPUT->single_button(
                $this->get_seb_download_url(),
                get_string('sebdownloadbutton', 'assignsubmission_seb')
            );
        }

        return $button;
    }

    /**
     * Returns SEB download URL.
     *
     * @return string
     */
    private function get_seb_download_url(): string {
        return get_config('assignsubmission_seb', 'downloadlink');
    }

    /**
     * Whether the user should be blocked from make a submission continuing
     * a submission now.
     *
     * @return string false if access should be allowed, a message explaining the
     *      reason if access should be prevented.
     */
    public function prevent_access() {
        global $PAGE;

        if (!$this->get_seb_accessmanager()->seb_required()) {
            return false;
        }

        if ($this->get_seb_accessmanager()->can_bypass_seb()) {
            return false;
        }

        // If the rule is active, enforce a secure view whilst making the submission.
        $PAGE->set_pagelayout('secure');
        $this->prevent_display_blocks();

        // Access has previously been validated for this session and submission.
        if ($this->get_seb_accessmanager()->validate_session_access()) {
            return false;
        }

        if (!$this->get_seb_accessmanager()->validate_basic_header()) {
            access_prevented::create_strict($this->get_seb_accessmanager(), $this->get_reason_text('not_seb'))->trigger();
            return $this->get_require_seb_error_message();
        }

        if (!$this->get_seb_accessmanager()->validate_config_key()) {
            if ($this->get_seb_accessmanager()->should_redirect_to_seb_config_link()) {
                $this->get_seb_accessmanager()->redirect_to_seb_config_link();
            }

            access_prevented::create_strict($this->get_seb_accessmanager(), $this->get_reason_text('invalid_config_key'))
            ->trigger();
            return $this->get_invalid_key_error_message();
        }

        if (!$this->get_seb_accessmanager()->validate_browser_exam_key()) {
            access_prevented::create_strict($this->get_seb_accessmanager(), $this->get_reason_text('invalid_browser_key'))
            ->trigger();
            return $this->get_invalid_key_error_message();
        }

        // Set the state of the access for this Moodle session.
        $this->get_seb_accessmanager()->set_session_access(true);

        return false;
    }

    /**
     * Prevent block displaying as configured.
     */
    private function prevent_display_blocks() {
        global $PAGE, $USER;
        if (
            $PAGE->has_set_url() &&
            $PAGE->url->get_path() === '/mod/assign/view.php' &&
            $PAGE->url->get_param('id') == $this->assignment->get_course_module()->id
        ) {
            // For students SEB is launched always except when viewing submissions page.
            $submission = $this->assignment->get_user_submission($USER->id, false);
            $assignnotsubmitted = (!$submission || $submission->status != ASSIGN_SUBMISSION_STATUS_SUBMITTED);

            // Don't display blocks before starting an attempt.
            if ($assignnotsubmitted && !get_config('assignsubmission_seb', 'displayblocksbeforestart')) {
                $PAGE->blocks->show_only_fake_blocks();
            }

            // Don't display blocks after finishing an attempt.
            if (!$assignnotsubmitted && !get_config('assignsubmission_seb', 'displayblockswhenfinished')) {
                $PAGE->blocks->show_only_fake_blocks();
            }
        }
    }


    /**
     * Helper function to display an Exit Safe Exam Browser button if configured to do so and submissions are > 0.
     *
     * @return string empty or a button which has the configured seb quit link.
     */
    private function get_quit_button(): string {
        $quitbutton = '';

        if (!$this->get_seb_accessmanager()->get_assign()->has_submissions_or_grades()) {
            return $quitbutton;
        }

        // Only display if the link has been configured and attempts are greater than 0.
        if (!empty($this->get_config('linkquitseb'))) {
            $quitbutton = html_writer::link(
                $this->get_config('linkquitseb'),
                get_string('exitsebbutton', 'assignsubmission_seb'),
                ['class' => 'btn btn-secondary']
            );
        }

        return $quitbutton;
    }

    /**
     * Check if we need to redirect to SEB config link.
     * @return bool
     */
    private function should_redirect_to_seb_config_link(): bool {
        return $this->get_seb_accessmanager()->is_using_seb() && get_config('assignsubmission_seb', 'autoreconfigureseb');
    }

    /**
     * Redirect to SEB config link. This will force Safe Exam Browser to be reconfigured.
     */
    private function redirect_to_seb_config_link() {
        global $PAGE;

        $seblink = link_generator::get_link($this->assignment->get_course_module()->id, true, is_https());
        $PAGE->requires->js_amd_inline("document.location.replace('" . $seblink . "')");
    }
}
