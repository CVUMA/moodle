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
 * Entity model representing assign settings for the seb plugin.
 *
 * @package    assignsubmission_seb
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_seb;

use CFPropertyList\CFArray;
use CFPropertyList\CFBoolean;
use CFPropertyList\CFDictionary;
use CFPropertyList\CFNumber;
use CFPropertyList\CFString;
use core\invalid_persistent_exception;
use core\lang_string;
use core\url;
use core\exception\coding_exception;
use core\exception\invalid_parameter_exception;
use core\exception\moodle_exception;
use ReflectionMethod;
use assign_submission_seb;
use stdClass;
use quizaccess_seb\property_list;
use quizaccess_seb\hook\seb_add_settings_to_config_hook;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/assign/locallib.php');

/**
 * Entity model representing assign settings for the seb plugin.
 *
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class seb_assign_settings {
    /** @var array The settings data. Copied from persistent class */
    private $data = [];

    /** @var bool If the data was already validated. Copied from persistent class */
    private $validated = false;

    /** @var property_list $plist The SEB config represented as a Property List object. */
    private $plist;

    /** @var string $config The SEB config represented as a string. */
    private $config;

    /** @var string $configkey The SEB config key represented as a string. */
    private $configkey;

    /** @var array The list of validation errors. */
    private $errors = [];

    /**
     * Create an instance of this class.
     *
     * @param int $id If set, this is the id of an existing record, used to load the data.
     * @param stdClass $record If set will be passed to {@link self::from_record()}.
     */
    public function __construct($id = 0, ?stdClass $record = null) {

        if (isset($record->assignid)) {
            $this->read($record->assignid);
        }

        if (!empty($record)) {
            $this->from_record($record);
        }
    }


    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function properties_definition(): array {
        return [
            'assignid' => [
                'type' => PARAM_INT,
            ],
            'cmid' => [
                'type' => PARAM_INT,
            ],
            'templateid' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'requiresafeexambrowser' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'showsebtaskbar' => [
                'type' => PARAM_INT,
                'default' => 1,
                'null' => NULL_ALLOWED,
            ],
            'showwificontrol' => [
                'type' => PARAM_INT,
                'default' => 0,
                'null' => NULL_ALLOWED,
            ],
            'showreloadbutton' => [
                'type' => PARAM_INT,
                'default' => 1,
                'null' => NULL_ALLOWED,
            ],
            'showtime' => [
                'type' => PARAM_INT,
                'default' => 1,
                'null' => NULL_ALLOWED,
            ],
            'showkeyboardlayout' => [
                'type' => PARAM_INT,
                'default' => 1,
                'null' => NULL_ALLOWED,
            ],
            'allowuserquitseb' => [
                'type' => PARAM_INT,
                'default' => 1,
                'null' => NULL_ALLOWED,
            ],
            'quitpassword' => [
                'type' => PARAM_TEXT,
                'default' => '',
                'null' => NULL_ALLOWED,
            ],
            'linkquitseb' => [
                'type' => PARAM_URL,
                'default' => '',
                'null' => NULL_ALLOWED,
            ],
            'userconfirmquit' => [
                'type' => PARAM_INT,
                'default' => 1,
                'null' => NULL_ALLOWED,
            ],
            'enableaudiocontrol' => [
                'type' => PARAM_INT,
                'default' => 0,
                'null' => NULL_ALLOWED,
            ],
            'muteonstartup' => [
                'type' => PARAM_INT,
                'default' => 0,
                'null' => NULL_ALLOWED,
            ],
            'allowspellchecking' => [
                'type' => PARAM_INT,
                'default' => 0,
                'null' => NULL_ALLOWED,
            ],
            'allowreloadinexam' => [
                'type' => PARAM_INT,
                'default' => 1,
                'null' => NULL_ALLOWED,
            ],
            'activateurlfiltering' => [
                'type' => PARAM_INT,
                'default' => 0,
                'null' => NULL_ALLOWED,
            ],
            'filterembeddedcontent' => [
                'type' => PARAM_INT,
                'default' => 0,
                'null' => NULL_ALLOWED,
            ],
            'expressionsallowed' => [
                'type' => PARAM_TEXT,
                'default' => '',
                'null' => NULL_ALLOWED,
            ],
            'regexallowed' => [
                'type' => PARAM_TEXT,
                'default' => '',
                'null' => NULL_ALLOWED,
            ],
            'expressionsblocked' => [
                'type' => PARAM_TEXT,
                'default' => '',
                'null' => NULL_ALLOWED,
            ],
            'regexblocked' => [
                'type' => PARAM_TEXT,
                'default' => '',
                'null' => NULL_ALLOWED,
            ],
            'showsebdownloadlink' => [
                'type' => PARAM_INT,
                'default' => 1,
                'null' => NULL_ALLOWED,
            ],
            'allowedbrowserexamkeys' => [
                'type' => PARAM_TEXT,
                'default' => '',
                'null' => NULL_ALLOWED,
            ],
        ];
    }

    /**
     * Load a single record by assign id.
     *
     * @param int $assignid Assignment identifier.
     * @return seb_assign_settings|false SEB assign settings object or false if cannot be created.
     */
    public static function get_record_by_assignid(int $assignid): seb_assign_settings {

        if (empty($assignid)) {
            return false;
        }

        [$course, $cm] = get_course_and_cm_from_instance($assignid, 'assign');
        $context = \context_module::instance($cm->id);
        $assign = new \assign($context, $cm, $course);
        $plugin = new \assign_submission_seb($assign, 'seb');
        $record = $plugin->get_config();
        $record->assignid = $assignid;
        $record->cmid = $cm->id;
        return $record ? new static(0, $record) : false;
    }

    /**
     * Load a single record by course module id.
     *
     * @param int $cmid Course module identifier.
     * @return seb_assign_settings|false SEB assign settings object or false if cannot be created.
     */
    public static function get_record_by_cmid(int $cmid): seb_assign_settings {
         [$course, $cm] = get_course_and_cm_from_cmid($cmid, 'assign');
        $context = \context_module::instance($cm->id);
        $assign = new \assign($context, $cm, $course);
        $plugin = new \assign_submission_seb($assign, 'seb');
        $record = $plugin->get_config();
        return $record ? new static(0, $record) : false;
    }

    /**
     * Get assigns that use a template.
     *
     * @param int $templateid SEB template id.
     * @return array SEB assign settings for allassigns that uses template.
     */
    public static function get_records_by_templateid(int $templateid): array {
        global $DB;

        $dbparams = ['subtype' => 'assignsubmission',
            'plugin' => 'seb',
            'name' => ' templateid',
            'value' => $templateid,
        ];
        $records = $DB->get_records('assign_plugin_config', $dbparams, '', 'assignment');

        $assigns = [];

        foreach ($records as $assignid => $recordid) {
            $assigns[] = self::get_record_by_assignid($assignid);
        }
        return $assigns;
    }



    /**
     * Return an instance by assign id.
     *
     * This method gets data from cache before doing any DB calls.
     *
     * @param int $assignid Assignment id.
     * @return seb_assign_settings|false Assign settings object or false if cannot be created.
     */
    public static function get_by_assign_id(int $assignid) {
        if ($data = self::get_assign_settings_cache()->get($assignid)) {
            return new static(0, $data);
        }

        return self::get_record_by_assignid($assignid);
    }

    /**
     * Return cached SEB config represented as a string by assign ID.
     *
     * @param int $assignid Assignment id.
     * @return string|null
     */
    public static function get_config_by_assign_id(int $assignid): ?string {
        $config = self::get_config_cache()->get($assignid);

        if ($config !== false) {
            return $config;
        }

        $config = null;
        if ($settings = self::get_by_assign_id($assignid)) {
            $config = $settings->get_config();
            self::get_config_cache()->set($assignid, $config);
        }

        return $config;
    }

    /**
     * Return cached SEB config key by assign ID.
     *
     * @param int $assignid Assignment id.
     * @return string|null
     */
    public static function get_config_key_by_assign_id(int $assignid): ?string {
        $configkey = self::get_config_key_cache()->get($assignid);

        if ($configkey !== false) {
            return $configkey;
        }

        $configkey = null;
        if ($settings = self::get_by_assign_id($assignid)) {
            $configkey = $settings->get_config_key();
            self::get_config_key_cache()->set($assignid, $configkey);
        }

        return $configkey;
    }

    /**
     * Return SEB config key cache instance.
     *
     * @return \cache_application
     */
    private static function get_config_key_cache(): \cache_application {
        return \cache::make('assignsubmission_seb', 'configkey');
    }

    /**
     * Return SEB config cache instance.
     *
     * @return \cache_application
     */
    private static function get_config_cache(): \cache_application {
        return \cache::make('assignsubmission_seb', 'config');
    }

    /**
     * Return assign settings cache object,
     *
     * @return \cache_application
     */
    private static function get_assign_settings_cache(): \cache_application {
        return \cache::make('assignsubmission_seb', 'assignsettings');
    }

    /**
     * As there is no hook for before both create and update, this function is called by both hooks.
     */
    private function before_save() {
        // Set template to 0 if using anything different to template.
        if ($this->get('requiresafeexambrowser') != settings_provider::USE_SEB_TEMPLATE) {
            $this->set('templateid', 0);
        }

        // Process configs to make sure that all data is set correctly.
        $this->process_configs();
    }

    /**
     * Helper method to execute common stuff after create and update.
     */
    private function after_save() {
        self::get_assign_settings_cache()->set($this->get('assignid'), $this->to_record());
        self::get_config_cache()->set($this->get('assignid'), $this->config);
        self::get_config_key_cache()->set($this->get('assignid'), $this->configkey);
    }

    /**
     * Removes unnecessary stuff from db.
     */
    public function delete_config() {
        $key = $this->get('assignid');
        self::get_assign_settings_cache()->delete($key);
        self::get_config_cache()->delete($key);
        self::get_config_key_cache()->delete($key);
    }

    /**
     * Validate the browser exam keys string.
     *
     * @param string $keys Newline separated browser exam keys.
     * @return true|lang_string If there is an error, an error string is returned.
     */
    protected function validate_allowedbrowserexamkeys($keys) {
        $keys = $this->split_keys($keys);
        foreach ($keys as $i => $key) {
            if (!preg_match('~^[a-f0-9]{64}$~', $key)) {
                return new lang_string('allowedbrowserkeyssyntax', 'assignsubmission_seb');
            }
        }
        if (count($keys) != count(array_unique($keys))) {
            return new lang_string('allowedbrowserkeysdistinct', 'assignsubmission_seb');
        }
        return true;
    }

    /**
     * Generate seb config files from settings.
     */
    public function regenerate_config() {
        $key = $this->get('assignid');
        self::get_config_cache()->delete($key);
    }

    /**
     * Get the browser exam keys as a pre-split array instead of just as a string.
     *
     * @return array
     */
    protected function get_allowedbrowserexamkeys(): array {
        $keysstring = $this->get('allowedbrowserexamkeys');
        $keysstring = empty($keysstring) ? '' : $keysstring;
        return $this->split_keys($keysstring);
    }

    /**
     * Before validate hook.
     */
    protected function before_validate() {
        // Template can't be null.
        if (is_null($this->get('templateid'))) {
            $this->set('templateid', 0);
        }
    }

    /**
     * Create or update the config string based on the current assign settings.
     */
    private function process_configs() {
        switch ($this->get('requiresafeexambrowser')) {
            case settings_provider::USE_SEB_NO:
                $this->process_seb_config_no();
                break;

            case settings_provider::USE_SEB_CONFIG_MANUALLY:
                $this->process_seb_config_manually();
                break;

            case settings_provider::USE_SEB_TEMPLATE:
                $this->process_seb_template();
                break;

            case settings_provider::USE_SEB_UPLOAD_CONFIG:
                $this->process_seb_upload_config();
                break;

            default: // Also settings_provider::USE_SEB_CLIENT_CONFIG.
                $this->process_seb_client_config();
        }

        // Generate config key based on given SEB config.
        if (!empty($this->config)) {
            $this->configkey = config_key::generate($this->config)->get_hash();
        } else {
            $this->configkey = null;
        }
    }

    /**
     * Return SEB config key.
     *
     * @return string|null
     */
    public function get_config_key(): ?string {
        $this->process_configs();

        return $this->configkey;
    }

    /**
     * Return string representation of the config.
     *
     * @return string|null
     */
    public function get_config(): ?string {
        $this->process_configs();

        return $this->config;
    }

    /**
     * Case for USE_SEB_NO.
     */
    private function process_seb_config_no() {
        $this->config = null;
    }

    /**
     * Case for USE_SEB_CONFIG_MANUALLY. This creates a plist and applies all settings from the posted form, along with
     * some defaults.
     */
    private function process_seb_config_manually() {
        // If at any point a configuration file has been uploaded and parsed, clear the settings.
        $this->plist = new property_list();

        $this->process_bool_settings();
        $this->process_quit_password_settings();
        $this->process_quit_url_from_settings();
        $this->process_url_filters();
        $this->process_required_enforced_settings();

        // Dispatch a hook for plugins to add their settings to config PList.
        $hook = new seb_add_settings_to_config_hook($this->plist, $this->get('cmid'));
        \core\di::get(\core\hook\manager::class)->dispatch($hook);

        // One of the requirements for USE_SEB_CONFIG_MANUALLY is setting examSessionClearCookiesOnStart to false.
        $this->plist->set_or_update_value('examSessionClearCookiesOnStart', new CFBoolean(false));
        $this->plist->set_or_update_value('allowPreferencesWindow', new CFBoolean(false));
        $this->config = $this->plist->to_xml();
    }

    /**
     * Case for USE_SEB_TEMPLATE. This creates a plist from the template uploaded, then applies the quit password
     * setting and some defaults.
     */
    private function process_seb_template() {
        $template = template::get_record(['id' => $this->get('templateid')]);
        $this->plist = new property_list($template->get('content'));

        $this->process_bool_setting('allowuserquitseb');
        $this->process_quit_password_settings();
        $this->process_quit_url_from_template_or_config();
        $this->process_required_enforced_settings();

        $this->config = $this->plist->to_xml();
    }

    /**
     * Case for USE_SEB_UPLOAD_CONFIG. This creates a plist from an uploaded configuration file, then applies the assign
     * password settings and some defaults.
     */
    private function process_seb_upload_config() {
        $file = settings_provider::get_module_context_sebconfig_file($this->get('cmid'));

        // If there was no file, create an empty plist so the rest of this wont explode.
        if (empty($file)) {
            throw new moodle_exception('noconfigfilefound', 'assignsubmission_seb', '', $this->get('cmid'));
        } else {
            $this->plist = new property_list($file->get_content());
        }

        $this->process_quit_url_from_template_or_config();
        $this->process_required_enforced_settings();

        $this->config = $this->plist->to_xml();
    }

    /**
     * Case for USE_SEB_CLIENT_CONFIG. This creates an empty plist to remove the config stored.
     */
    private function process_seb_client_config() {
        $this->config = null;
    }

    /**
     * Sets or updates some sensible default settings, these are the items 'startURL' and 'sendBrowserExamKey'.
     */
    private function process_required_enforced_settings() {
        global $CFG;

        $assignurl = new url($CFG->wwwroot . "/mod/assign/view.php", ['id' => $this->get('cmid')]);
        $this->plist->set_or_update_value('startURL', new CFString($assignurl->out(true)));
        $this->plist->set_or_update_value('sendBrowserExamKey', new CFBoolean(true));

        // Use the modern WebView and JS API if the SEB version supports it.
        // Documentation: https://safeexambrowser.org/developer/seb-config-key.html .
        // "Set the key browserWindowWebView to the policy "Prefer Modern" (value 3)".
        $this->plist->set_or_update_value('browserWindowWebView', new CFNumber(3));
    }

    /**
     * Use the boolean map to add Moodle boolean setting to config PList.
     */
    private function process_bool_settings() {
        $settings = $this->to_record();
        $map = $this->get_bool_seb_setting_map();
        foreach ($settings as $setting => $value) {
            if (isset($map[$setting])) {
                $this->process_bool_setting($setting);
            }
        }
    }

    /**
     * Process provided single bool setting.
     *
     * @param string $name Setting name matching one from self::get_bool_seb_setting_map.
     */
    private function process_bool_setting(string $name) {
        $map = $this->get_bool_seb_setting_map();

        if (!isset($map[$name])) {
            throw new \coding_exception('Provided setting name can not be found in known bool settings');
        }

        $enabled = $this->get($name) == 1 ? true : false;
        $this->plist->set_or_update_value($map[$name], new CFBoolean($enabled));
    }

    /**
     * Turn hashed quit password and quit link into PList strings and add to config PList.
     */
    private function process_quit_password_settings() {
        $settings = $this->to_record();
        if (!empty($settings->quitpassword) && is_string($settings->quitpassword)) {
            // Hash quit password.
            $hashedpassword = hash('SHA256', $settings->quitpassword);
            $this->plist->add_element_to_root('hashedQuitPassword', new CFString($hashedpassword));
        } else if (!is_null($this->plist->get_element_value('hashedQuitPassword'))) {
            $this->plist->delete_element('hashedQuitPassword');
        }
    }

    /**
     * Sets the quitURL if found in the seb_assign_settings.
     */
    private function process_quit_url_from_settings() {
        $settings = $this->to_record();
        if (!empty($settings->linkquitseb) && is_string($settings->linkquitseb)) {
            $this->plist->set_or_update_value('quitURL', new CFString($settings->linkquitseb));
        }
    }

    /**
     * Sets the assign_setting's linkquitseb if a quitURL value was found in a template or uploaded config.
     */
    private function process_quit_url_from_template_or_config() {
        // Does the plist (template or config file) have an existing quitURL?
        $quiturl = $this->plist->get_element_value('quitURL');
        if (!empty($quiturl)) {
            $this->set('linkquitseb', $quiturl);
        }
    }

    /**
     * Turn return separated strings for URL filters into a PList array and add to config PList.
     */
    private function process_url_filters() {
        $settings = $this->to_record();
        // Create rules to each expression provided and add to config.
        $urlfilterrules = [];
        // Get all rules separated by newlines and remove empty rules.
        $expallowed = array_filter(explode(PHP_EOL, $settings->expressionsallowed));
        $expblocked = array_filter(explode(PHP_EOL, $settings->expressionsblocked));
        $regallowed = array_filter(explode(PHP_EOL, $settings->regexallowed));
        $regblocked = array_filter(explode(PHP_EOL, $settings->regexblocked));
        foreach ($expallowed as $rulestring) {
            $urlfilterrules[] = $this->create_filter_rule($rulestring, true, false);
        }
        foreach ($expblocked as $rulestring) {
            $urlfilterrules[] = $this->create_filter_rule($rulestring, false, false);
        }
        foreach ($regallowed as $rulestring) {
            $urlfilterrules[] = $this->create_filter_rule($rulestring, true, true);
        }
        foreach ($regblocked as $rulestring) {
            $urlfilterrules[] = $this->create_filter_rule($rulestring, false, true);
        }
        $this->plist->add_element_to_root('URLFilterRules', new CFArray($urlfilterrules));
    }

    /**
     * Create a CFDictionary represeting a URL filter rule.
     *
     * @param string $rulestring The expression to filter with.
     * @param bool $allowed Allowed or blocked.
     * @param bool $isregex Regex or simple.
     * @return CFDictionary A PList dictionary.
     */
    private function create_filter_rule(string $rulestring, bool $allowed, bool $isregex): CFDictionary {
        $action = $allowed ? 1 : 0;
        return new CFDictionary([
                    'action' => new CFNumber($action),
                    'active' => new CFBoolean(true),
                    'expression' => new CFString(trim($rulestring)),
                    'regex' => new CFBoolean($isregex),
                    ]);
    }

    /**
     * Map the settings that are booleans to the Safe Exam Browser config keys.
     *
     * @return array Moodle setting as key, SEB setting as value.
     */
    private function get_bool_seb_setting_map(): array {
        return [
            'activateurlfiltering' => 'URLFilterEnable',
            'allowspellchecking' => 'allowSpellCheck',
            'allowreloadinexam' => 'browserWindowAllowReload',
            'allowuserquitseb' => 'allowQuit',
            'enableaudiocontrol' => 'audioControlEnabled',
            'filterembeddedcontent' => 'URLFilterEnableContentFilter',
            'muteonstartup' => 'audioMute',
            'showkeyboardlayout' => 'showInputLanguage',
            'showreloadbutton' => 'showReloadButton',
            'showsebtaskbar' => 'showTaskBar',
            'showtime' => 'showTime',
            'showwificontrol' => 'allowWlan',
            'userconfirmquit' => 'quitURLConfirm',
        ];
    }

    /**
     * This helper method takes list of browser exam keys in a string and splits it into an array of separate keys.
     *
     * @param string|null $keys the allowed keys.
     * @return array of string, the separate keys.
     */
    public function split_keys($keys): array {
        $keys = preg_split('~[ \t\n\r,;]+~', $keys ?? '', -1, PREG_SPLIT_NO_EMPTY);
        foreach ($keys as $i => $key) {
            $keys[$i] = strtolower($key);
        }
        return $keys;
    }

    /*****************************************************************/
    /**                                                              */
    /**    Functions copied to simulate \core\persistent functions   */
    /**                                                              */
    /*****************************************************************/

    /**
     * Returns whether or not a property is required.
     *
     * By definition a property with a default value is not required.
     *
     * @param  string $property The property name.
     * @return boolean
     * @see persistent::is_property_required()
     */
    private function is_property_required($property) {
        $properties = $this->properties_definition();
        return !array_key_exists('default', $properties[$property]);
    }

    /**
     * Gets the default value for a property.
     *
     * This assumes that the property exists.
     *
     * @param string $property The property name.
     * @return mixed
     * @see persistent::get_property_default_value()
     */
    private function get_property_default_value($property) {
        $properties = $this->properties_definition();
        if (!isset($properties[$property]['default'])) {
            return null;
        }
        $value = $properties[$property]['default'];
        if ($value instanceof \Closure) {
            return $value();
        }
        return $value;
    }

    /**
     * Data getter.
     *
     * This is the main getter for all the properties.
     *
     * @param  string $property The property name.
     * @return mixed
     * @see persistent::get()
     */
    public function get($property) {
        if (!$this->has_property($property)) {
            throw new coding_exception('Unexpected property \'' . s($property) .'\' requested.');
        }

        if (!array_key_exists($property, $this->data) && !$this->is_property_required($property)) {
            $this->set($property, $this->get_property_default_value($property));
        }

        $value = isset($this->data[$property]) ? $this->data[$property] : null;

        $properties = $this->properties_definition();

        // Deliberately cast boolean types as such, because clean_param will cast them to integer.
        if ($properties[$property]['type'] === PARAM_BOOL) {
            return (bool)$value;
        }

        return clean_param($value, $properties[$property]['type']);
    }

    /**
     * Returns whether or not a property was defined.
     *
     * @param  string $property The property name.
     * @return boolean
     * @see persistent::has_property()
     */
    private function has_property($property) {
        $properties = $this->properties_definition();
        return isset($properties[$property]);
    }

    /**
     * Data setter.
     *
     * This is the main setter for all the properties.
     *
     * @param  string $property The property name.
     * @return $this
     * @see persistent::set()
     */
    public function set($property, $value) {
        if (!array_key_exists($property, $this->data) || $this->data[$property] != $value) {
            // If the value is changing, we invalidate the model.
            $this->validated = false;
        }
        $this->data[$property] = $value;
    }

    /**
     * Populate this class with data from a DB record.
     *
     * Note that this does not use any custom setter because the data here is intended to
     * represent what is stored in the database.
     *
     * @param \stdClass $record A DB record.
     * @return static
     * @see persistent::from_record()
     */
    public function from_record(\stdClass $record) {
        $record = (array) $record;
        foreach ($record as $property => $value) {
            $this->set($property, $value);
        }
        return $this;
    }

    /**
     * Create a DB record from this class.
     *
     * Note that this does not use any custom getter because the data here is intended to
     * represent what is stored in the database.
     *
     * @return \stdClass
     * @see persistent::to_record()
     */
    public function to_record() {
        $data = new \stdClass();
        $properties = $this->properties_definition();
        foreach ($properties as $property => $definition) {
            $data->$property = $this->get($property);
        }
        return $data;
    }

    /**
     * Load the data from the DB.
     *
     * @return static
     */
    private function read($assignid) {
        [$course, $cm] = get_course_and_cm_from_instance($assignid, 'assign');
        $context = \context_module::instance($cm->id);
        $assign = new \assign($context, $cm, $course);
        $plugin = new \assign_submission_seb($assign, 'seb');
        $config = $plugin->get_config();
        unset($config->enabled);
        $this->from_record($config);

        // Validate the data as it comes from the database.
        $this->validated = true;

        return $this;
    }

    /**
     * Save the data to DB.
     *
     * @param assign_submission_seb $plugin
     */
    public function save(assign_submission_seb $plugin) {

        if (!$errors = $this->validate()) {
            throw new invalid_persistent_exception($errors);
        }

        // Before create hook.
        $this->before_save();

        $settings = (array)$this->to_record();
        unset($settings['cmid']);
        unset($settings['assignid']);
        unset($settings['timecreated']);
        unset($settings['timemodified']);
        unset($settings['usermodified']);

        foreach ($settings as $name => $value) {
            $plugin->set_config($name, $value);
        }

        $this->validated = true;

        // After create hook.
        $this->after_save();
    }

    /**
     * Validate config.
     *
     * @return boolean|\lang_string[] An array of error messages for properties with errors or true if there are no errors.
     */
    private function validate() {
        global $CFG;

        $errors = [];

        // Before validate hook.
        $this->before_validate();

        // If this object has not been validated yet.
        if ($this->validated !== true) {
            $properties = $this->properties_definition();
            foreach ($properties as $property => $definition) {
                // Get the data, bypassing the potential custom getter which could alter the data.
                $value = $this->get($property);

                // Check if the property is required.
                if ($value === null && $this->is_property_required($property)) {
                    $errors[$property] = new lang_string('requiredelement', 'form');
                    continue;
                }

                // Check that type of value is respected.
                try {
                    if ($definition['type'] === PARAM_BOOL && $value === false) {
                        // Validate_param() does not like false with PARAM_BOOL, better to convert it to int.
                        $value = 0;
                    }
                    if ($definition['type'] === PARAM_CLEANHTML) {
                        // We silently clean for this type. It may introduce changes even to valid data.
                        $value = clean_param($value, PARAM_CLEANHTML);
                    }
                    if (!isset($definition['null'])) {
                        $definition['null'] = NULL_ALLOWED;
                    }
                    validate_param($value, $definition['type'], $definition['null']);
                } catch (invalid_parameter_exception $e) {
                    $errors[$property] = $this->get_property_error_message($property);
                    continue;
                }

                // Check that the value is part of a list of allowed values.
                if (isset($definition['choices']) && !in_array($value, $definition['choices'])) {
                    $errors[$property] = $this->get_property_error_message($property);
                    continue;
                }

                // Call custom validation method.
                $method = 'validate_' . $property;
                if (method_exists($this, $method)) {
                    // Warn the developers when they are doing something wrong.
                    if ($CFG->debugdeveloper) {
                        $reflection = new ReflectionMethod($this, $method);
                        if (!$reflection->isProtected()) {
                            throw new coding_exception('The method ' . get_class($this) . '::' . $method . ' should be protected.');
                        }
                    }

                    $valid = $this->{$method}($value);
                    if ($valid !== true) {
                        if (!($valid instanceof lang_string)) {
                            throw new coding_exception('Unexpected error message.');
                        }
                        $errors[$property] = $valid;
                        continue;
                    }
                }
            }

            $this->validated = true;
            $this->errors = $errors;
        }

        return empty($this->errors) ? true : $this->errors;
    }

    /**
     * Returns the validation errors.
     *
     * @return array
     */
    final public function get_errors() {
        $this->validate();
        return $this->errors;
    }

    /**
     * Return error messages for a property.
     *
     * @param unknown $property
     * @return \lang_string|string The error message for a property.
     */
    private function get_property_error_message($property) {
        $properties = $this->properties_definition();
        if (!isset($properties[$property]['message'])) {
            return new lang_string('invaliddata', 'error');
        }
        return $properties[$property]['message'];
    }
}
