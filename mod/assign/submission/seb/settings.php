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
 * Global configuration settings for the assignsubmission_seb plugin.
 *
 * @package    assignsubmission_seb
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

global $ADMIN;

$settings->add(new admin_setting_configcheckbox(
    'assignsubmission_seb/default',
    new lang_string('default', 'assignsubmission_seb'),
    new lang_string('default_help', 'assignsubmission_seb'),
    0
));

if ($hassiteconfig) {
    $settings->add(new admin_setting_heading(
        'assignsubmission_seb/supportedversions',
        '',
        $OUTPUT->notification(get_string('setting:supportedversions', 'assignsubmission_seb'), 'warning')
    ));

    $settings->add(new admin_setting_configcheckbox(
        'assignsubmission_seb/autoreconfigureseb',
        get_string('setting:autoreconfigureseb', 'assignsubmission_seb'),
        get_string('setting:autoreconfigureseb_desc', 'assignsubmission_seb'),
        '1'
    ));

    $links = [
            'seb' => get_string('setting:showseblink', 'assignsubmission_seb'),
            'http' => get_string('setting:showhttplink', 'assignsubmission_seb'),
    ];
    $settings->add(new admin_setting_configmulticheckbox(
        'assignsubmission_seb/showseblinks',
        get_string('setting:showseblinks', 'assignsubmission_seb'),
        get_string('setting:showseblinks_desc', 'assignsubmission_seb'),
        $links,
        $links
    ));

    $settings->add(new admin_setting_configtext(
        'assignsubmission_seb/downloadlink',
        get_string('setting:downloadlink', 'assignsubmission_seb'),
        get_string('setting:downloadlink_desc', 'assignsubmission_seb'),
        'https://safeexambrowser.org/download_en.html',
        PARAM_URL
    ));

    $settings->add(new admin_setting_configcheckbox(
        'assignsubmission_seb/assignpasswordrequired',
        get_string('setting:assignpasswordrequired', 'assignsubmission_seb'),
        get_string('setting:assignpasswordrequired_desc', 'assignsubmission_seb'),
        '0'
    ));


    $settings->add(new admin_setting_configcheckbox(
        'assignsubmission_seb/displayblocksbeforestart',
        get_string('setting:displayblocksbeforestart', 'assignsubmission_seb'),
        get_string('setting:displayblocksbeforestart_desc', 'assignsubmission_seb'),
        '0'
    ));

    $settings->add(new admin_setting_configcheckbox(
        'assignsubmission_seb/displayblockswhenfinished',
        get_string('setting:displayblockswhenfinished', 'assignsubmission_seb'),
        get_string('setting:displayblockswhenfinished_desc', 'assignsubmission_seb'),
        '1'
    ));
}
