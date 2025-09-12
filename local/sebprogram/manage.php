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
 * Lets users manage programs.
 *
 * @package    local_sebprogram
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


use core\url;

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/my/lib.php');

$contextid = optional_param('contextid', 0, PARAM_INT);
$returncmid = optional_param('returncmid', 0, PARAM_INT);

require_login();

if (empty($contextid)) {
    $contextid = context_user::instance($USER->id)->id;
}

$context = \context::instance_by_id($contextid);

if ($context instanceof \context_system) {
    require_capability('local/sebprogram:manageprograms', $context);
}

$url = new url('/local/sebprogram/manage.php', ['contextid' => $contextid, 'returncmid' => $returncmid]);

$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);
if ($context instanceof context_system) {
    $PAGE->set_heading($SITE->fullname);
}
$title = get_string('manageprograms', 'local_sebprogram');
$PAGE->navbar->add($title, $url);
$PAGE->set_title($title);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

if ($returncmid) {
    $returnurl = new url('/course/modedit.php', ['update' => $returncmid]);
    echo $OUTPUT->single_button($returnurl, get_string('returntomodule', 'local_sebprogram'), 'get');
}

$params = ['contextid' => $context->id];
if ($returncmid) {
    $params['returncmid'] = $returncmid;
}

$addurl = new url('/local/sebprogram/edit.php', $params);
echo $OUTPUT->single_button($addurl, get_string('addprogram', 'local_sebprogram'));

$manager = new \local_sebprogram\program_manager();

$manager->print_program_list($context->id, $returncmid);

echo $OUTPUT->footer();
