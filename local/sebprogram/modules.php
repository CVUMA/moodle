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
 * List of all modules that allow a program in SEB.
 *
 * @package    local_sebprogram
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\url;
use core\output\html_writer;
use core\output\pix_icon;
use core_table\output\html_table;

require_once('../../config.php');

$id = required_param('id', PARAM_INT);
$returncmid = optional_param('returncmid', 0, PARAM_INT);

require_login();

$program = new local_sebprogram\program($id);
$contextid = $program->get('contextid');

$context = \context::instance_by_id($contextid);

$usercontext = \context_user::instance($USER->id);
if ($usercontext <> $context) {
    require_capability('local/sebprogram:manageprograms', $context);
}

$programmodules = $program->get_program_allowed_modules();

$courses = [];
foreach ($programmodules as $programmodule) {
    if ($cm = get_coursemodule_from_id('', $programmodule->get('cmid'), 0, false)) {
        if (!isset($courses[$cm->course])) {
            $courses[$cm->course] = [];
        }
        $courses[$cm->course][] = $cm;
    }
}

$cachefullnames = $DB->get_records_list('course', 'id ', array_keys($courses), '', 'id,fullname');

uksort($courses, fn($a, $b) => strnatcmp($cachefullnames[$a]->fullname, $cachefullnames[$b]->fullname));

$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
if ($context instanceof context_system) {
    $PAGE->set_heading($SITE->fullname);
}

$title = get_string('modulestitle', 'local_sebprogram', $program->get('title'));

$PAGE->set_url('/local/sebprogram/modules.php', ['id' => $id, 'returncmid' => $returncmid]);
$PAGE->set_title($title);
$PAGE->navbar->add($title);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

$table->head  = [get_string('course'), get_string('activities')];
$table->align = ['left', 'left'];

foreach ($courses as $courseid => $cms) {
    $course = get_fast_modinfo($courseid);

    $activities = [];

    foreach ($cms as $cm) {
        $cm = $course->get_cm($cm->id);

        $icon = html_writer::img($cm->get_icon_url(), $cm->get_module_type_name(), ['class' => 'activityicon']);
        $url = $cm->url ?: new url("/mod/{$cm->modname}/view.php", ['id' => $cm->id]);
        $editlink = new url("/course/modedit.php", ['update' => $cm->id]);
        $editicons = $OUTPUT->action_icon($editlink, new pix_icon(
            't/edit',
            get_string('edit'),
            'core',
            ['class' => 'iconsmall action-icon']
        ));

        $activities[] = html_writer::div(html_writer::link(
            $url,
            $icon . $cm->get_formatted_name()
        ) . $editicons, 'allowed_activity');
    }
    $table->data[] = [
            $cachefullnames[$courseid]->fullname,
            implode('', $activities)];
}

echo html_writer::table($table);

echo $OUTPUT->footer();
