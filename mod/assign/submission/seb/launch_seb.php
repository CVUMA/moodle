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
 * This file is the entry point to the assign module. All pages are rendered from here
 *
 * @package    assignsubmission_seb
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use core\url;
use core\output\html_writer;

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once($CFG->dirroot . '/mod/assign/submission/seb/locallib.php');

$id = required_param('id', PARAM_INT);

 [$course, $cm] = get_course_and_cm_from_cmid($id, 'assign');

require_login($course, true, $cm);

$context = context_module::instance($cm->id);

require_capability('mod/assign:view', $context);

$assign = new assign($context, $cm, $course);

$url = new url('/mod/assign/submission/seb/launch_seb.php', ['id' => $id]);
$PAGE->set_url($url);
$PAGE->requires->jquery();

// If the rule is active, enforce a secure view whilst adding a submission.
$PAGE->set_pagelayout('secure');
$PAGE->blocks->show_only_fake_blocks();

$PAGE->set_title($course->shortname . ': ' . $assign->get_context()->get_context_name());
$PAGE->set_heading($course->fullname);

$plugin = new \assign_submission_seb($assign, 'seb');

if (!$plugin->prevent_access()) {
    redirect(new url(
        '/mod/assign/view.php',
        ['id' => $id]
        ));
}

echo $OUTPUT->header();

echo html_writer::start_tag('div', ['align' => 'center']);

echo implode(html_writer::tag('br', ''), $plugin->get_launch_seb_messages());
echo html_writer::tag('p', '');
echo $plugin->get_launch_seb_buttons();
echo html_writer::tag('p', '');
echo $OUTPUT->single_button(
    new url('/course/view.php', ['id' => $course->id]),
    get_string('sebbacktocoursebutton', 'assignsubmission_seb'),
    'get',
    ['class' => 'continuebutton']
);

echo html_writer::end_tag('div');

echo $OUTPUT->footer();
