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
 * Admin setting for site SEB available programs.
 *
 * @package    local_sebprogram
 * @copyright  2024 Universidad de Málaga
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use core\url;
use core\output\html_writer;

/**
 * Admin settings manager for local_sebprogram.
 *
 * @package    local_sebprogram
 * @copyright  2024 Universidad de Málaga
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_sebprogram_admin_setting_manage_programs extends admin_setting {
    /**
     * Calls parent::__construct with specific arguments
     */
    public function __construct() {
        $this->nosave = true;
        parent::__construct('seb_program_manage_programs', get_string('sitesebprogramssetup', 'local_sebprogram'), '', '');
    }

    /**
     * Always returns true, does nothing
     *
     * @return true
     */
    public function get_setting() {
        return true;
    }

    /**
     * Always returns true, does nothing
     *
     * @return true
     */
    public function get_defaultsetting() {
        return true;
    }

    /**
     * Always returns '', does not write anything
     *
     * @param string $data Unused
     * @return string Always returns ''
     */
    public function write_setting($data) {
        // Do not write any setting.
        return '';
    }

    /**
     * Builds the XHTML to display the control
     *
     * @param string $data Unused
     * @param string $query
     * @return string
     */
    public function output_html($data, $query = '') {

        $murl = new url('/local/sebprogram/edit.php');
        $str = html_writer::link(
            $murl,
            get_string('addprogram', 'local_sebprogram'),
            ['class' => 'btn btn-primary', 'style' => 'margin-bottom:10px;']
        );

        $manager = new \local_sebprogram\program_manager();

        ob_start();
        $manager->print_program_list(context_system::instance()->id);
        $table = ob_get_contents();
        ob_end_clean();
        $str .= $table;

        $downloadlink = html_writer::link(new url('/local/sebprogram/download.php'), get_string('downloadcsv', 'local_sebprogram'));
        $str .= html_writer::div($downloadlink);

        return highlight($query, $str);
    }
}
