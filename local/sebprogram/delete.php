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
 * Delete a program.
 *
 * @package    local_sebprogram
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\url;
use core\output\html_writer;
use core\output\single_button;

require_once('../../config.php');

$programid  = required_param('id', PARAM_INT);
$returncmid = optional_param('returncmid', 0, PARAM_INT);
$confirm    = optional_param('confirm', 0, PARAM_BOOL);

$program = new local_sebprogram\program($programid);
$contextid = $program->get('contextid');

require_login();

$context = \context::instance_by_id($contextid);

$usercontext = \context_user::instance($USER->id);
if ($usercontext <> $context) {
    require_capability('local/sebprogram:manageprograms', $context);
}

$PAGE->set_url('/local/sebprogram/delete.php', ['id' => $programid, 'returncmid' => $returncmid, 'confirm' => $confirm]);
$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);
if ($context instanceof \context_system) {
    $PAGE->set_heading($SITE->fullname);
}

$params = ['contextid' => $contextid];
if ($returncmid) {
    $params['returncmid'] = $returncmid;
}
$returnurl = new url('/local/sebprogram/manage.php', $params);

$programtitle = $program->get('title');

if ($dependents = $program->get_dependent()) {
    echo $OUTPUT->header();
    echo get_string('deleteprogramcannotdelete', 'local_sebprogram', $programtitle);
    $dependentprograms = array_map(fn($program) => $program->get('title'), $dependents);
    html_writer::alist($dependentprograms);
    echo $OUTPUT->single_button($returnurl, get_string('continue'));
    echo $OUTPUT->footer();
    die();
}

if ($confirm && confirm_sesskey()) {
    try {
        $manager = new \local_sebprogram\program_manager();

        $manager->delete_program($program);

        core\notification::success(get_string('deletedprogram', 'local_sebprogram', $programtitle));
    } catch (\Exception $e) {
        core\notification::error($e->getMessage());
    }

    redirect($returnurl);
}

$confirmmessage = get_string('deleteprogramconfirmmessage', 'local_sebprogram', $programtitle);

if ($uses = $program->get('uses')) {
    $a = new \stdClass();
    $a->programtitle = $programtitle;
    $usestext = $uses . ' ' . get_string($uses > 1 ? 'activities' : 'activity');
    $a->useslink = html_writer::link(new url(
        '/local/sebprogram/modules.php',
        ['id' => $program->get('id'), 'returncmid' => $returncmid]
    ), $usestext);
    $confirmmessage .= html_writer::div(get_string('deleteprogramconfirmwarning', 'local_sebprogram', $a), "warning");
}

echo $OUTPUT->header();

$yesurl = new url(
    '/local/sebprogram/delete.php',
    ['id' => $programid, 'returncmid' => $returncmid, 'confirm' => 1, 'sesskey' => sesskey()]
);
$yesbutton = new single_button(
    $yesurl,
    get_string(($uses > 1 ? 'deleteprogramconfirmused' : 'deleteprogramconfirm'), 'local_sebprogram', $programtitle)
);
$nobutton = new single_button($returnurl, get_string('deleteprogramcancel', 'local_sebprogram', $programtitle), 'get');
echo $OUTPUT->confirm($confirmmessage, $nobutton, $yesbutton);
echo $OUTPUT->footer();
