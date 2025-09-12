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
 * local_sebprogram language strings.
 *
 * @package    local_sebprogram
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();


$string['actions'] = 'Actions';
$string['addedprogram'] = 'Added program {$a}';
$string['addingprogram'] = 'Adding a program for SEB';
$string['addprogram'] = 'Add new program';
$string['allowedprograms'] = 'Programs allowed during activity';
$string['allowedprograms_help'] = 'Select the programs that students will be allowed to use while completing this activity.';
$string['chooseprograms'] = 'Choose programs';
$string['cleanorphanedmoduleprogramstask'] = 'Cleanup of SEB allowed programs for modules that no longer exist';
$string['deletedprogram'] = 'Deleted program {$a}';
$string['deleteprogramcancel'] = 'KEEP program "{$a}" and return to manage programs';
$string['deleteprogramcannotdelete'] = 'The program {$a} cannot be deleted because the following programs depend on it. To delete it, you must delete these programs first.';
$string['deleteprogramconfirm'] = 'Remove "{$a}" from all activities where it is allowed and delete it';
$string['deleteprogramconfirmmessage'] = 'Indicate what you want to do with program "{$a}".';
$string['deleteprogramconfirmused'] = 'Delete program "{$a}"';
$string['deleteprogramconfirmwarning'] = 'BE CAREFOUL! There are {$a->useslink} that have the program "{$a->programtitle}" selected as allowed for use in SEB. If you delete it, it will also be removed from the allowed programs for SEB in these activities.';
$string['deleteprogramnot'] = 'The program {$a} cannot be deleted becouse the following programs depend on it. You must delete this programs first in order to delete this one.';
$string['dependencies'] = 'Dependencies';
$string['dependencies_help'] = 'Other programs that it depends on for execution.<br>When this program is allowed, all the programs listed here will also be allowed automatically.';
$string['downloadcsv'] = 'Download CSV';
$string['duplicateprogram'] = 'A program with the same name already exists.';
$string['editingprogram'] = 'Editing a program for SEB';
$string['eventsebprogramdeleted'] = 'SEB program deleted';
$string['eventsebprogramdeleted_description'] = 'The user with id "{$a->userid}" deleted the program "{$a->title}" with executable "{$a->path}/{$a->executable}" from programs available to be allowed in SEB.';
$string['executable'] = 'Executable';
$string['executable_help'] = 'File name of the executable as displayed in the SEB Configuration Tool (Executable).';
$string['manageprograms'] = 'Manage my programs';
$string['missingexecutable'] = 'Missing executable';
$string['missingtitle'] = 'Missing title';
$string['modulestitle'] = 'Modules allowing program {$a}';
$string['nodependencies'] = 'No dependencies';
$string['noprogramsallowed'] = 'No programs are allowed in SEB';
$string['nothingtodisplay'] = 'No programs for SEB available';
$string['originalname'] = 'Originalname';
$string['originalname_help'] = 'Original name file metadata as displayed in the SEB Configuration Tool (Original Name).';
$string['path'] = 'Path';
$string['path_help'] = 'Path as displayed in the SEB Configuration Tool (Path).';
$string['pluginname'] = 'Safe Exam Browser available programs';
$string['programcontexcourseteacher'] = 'teachers';
$string['programcontextuser'] = 'own';
$string['returntomodule'] = 'Return to activity settings';
$string['sebprogram:manageprograms'] = 'Manage programs';
$string['sitesebprogramssetup'] = 'Manage site SEB available programs';
$string['title'] = 'Title';
$string['updatedprogram'] = 'Updated program {$a}';
$string['uses'] = 'Number of uses';
$string['visible'] = 'Visibility';
$string['visible_help'] = 'This setting determines whether the program will appear in the list of programs available to use with SEB in the module\'s SEB settings.';
