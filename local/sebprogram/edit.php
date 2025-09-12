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
 * Adds a new program to be allowed for use in SEB
 * or edits an existing one.
 *
 * @package    local_sebprogram
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\url;
use local_sebprogram\program;
use local_sebprogram\program_dependency;
use local_sebprogram\program_manager;

require_once('../../config.php');
require_once('edit_form.php');

$programid = optional_param('id', 0, PARAM_INT);
$contextid = optional_param('contextid', context_system::instance()->id, PARAM_INT);
$returncmid = optional_param('returncmid', 0, PARAM_INT);

$program = null;

if ($programid) {
    $program = new program($programid);
    $contextid = $program->get('contextid');
}

require_login();

$context = \context::instance_by_id($contextid);

if ($context instanceof \context_system) {
    require_capability('local/sebprogram:manageprograms', $context);
}

$usercontext = context_user::instance($USER->id);


$PAGE->set_url('/local/sebprogram/edit.php', ['id' => $programid, 'contextid' => $contextid, 'returncmid' => $returncmid]);
$PAGE->set_pagelayout('admin');
$PAGE->set_context($context);
if ($context instanceof context_system) {
    $PAGE->set_heading($SITE->fullname);
}

if ($context instanceof context_system) {
    $returnurl = new url('/admin/settings.php', ['section' => 'local_sebprogram']);
} else {
    $params = ['contextid' => $contextid];
    if ($returncmid) {
        $params['returncmid'] = $returncmid;
    }
    $returnurl = new url('/local/sebprogram/manage.php', $params);
}

$customdata = [
        'persistent' => $program,
        'context' => $context,
        'returncmid' => $returncmid,
];
$form = new local_sebprogram_program_form($PAGE->url->out(false), $customdata);

if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $form->get_data()) {
    try {
        $selecteddependencies = $data->program_dependencies;
        $currentdependencies = $form->get_current_dependencies();

        $adddependencies = array_diff($selecteddependencies, $currentdependencies);
        $deletedependencies = array_diff($currentdependencies, $selecteddependencies);

        unset($data->program_dependencies);
        unset($data->returncmid);
        $isnew = false;
        if (empty($data->id)) { // Create program.
            $isnew = true;
            $data->contextid = $contextid;

            $program = new program(0, $data);
            $program = $program->create();
            $data->id = $program->get('id');
        } else {// Update program.
            $program->from_record($data);
            $program->update();
        }

        $programdependencydata = new \stdClass();
        $programdependencydata->programid = $data->id;

        // Add new program dependencies.
        foreach ($adddependencies as $programid) {
            $programdependencydata->requiredprogramid = $programid;
            $programdependency = new program_dependency(0, $programdependencydata);
            $programdependency->create();
        }

        // Remove deleted program dependencies.
        foreach ($deletedependencies as $programid) {
            $programdependency = program_dependency::get_record(['programid' => $programdependencydata->programid,
                'requiredprogramid' => $programid]);
            $programdependency->delete();
        }

        // Allow plugins handle changes in program.
        program_manager::program_updated($data->id);

        core\notification::success(get_string(
            ($isnew ? 'addedprogram' : 'updatedprogram'),
            'local_sebprogram',
            $program->get('title')
        ));
    } catch (\Exception $e) {
        core\notification::error($e->getMessage());
    }
    redirect($returnurl);
}

$title = get_string((empty($program) ? 'addingprogram' : 'editingprogram'), 'local_sebprogram');

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
$form->display();
echo $OUTPUT->footer();
