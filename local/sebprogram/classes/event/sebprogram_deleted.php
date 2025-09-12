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
 * SEB program deleted event.
 *
 * @package    local_sebprogram
 * @copyright  2024 Universidad de Málaga
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_sebprogram\event;

defined('MOODLE_INTERNAL') || die();

/**
 * SEB program deleted event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - string title: Program title.
 *      - string executable: Program executable.
 *      - string originalname: Program original name.
 *      - string path: Program path.
 *      - int visible: Program visibility.
 *      - string dependencies: Comma separated list of other programs required.
 * }
 *
 * @package    local_sebprogram
 * @copyright  2024 Universidad de Málaga
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sebprogram_deleted extends \core\event\base {
    /**
     * Initialise the event data.
     */
    protected function init() {
        $this->data['objecttable'] = 'local_sebprogram_program';
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventsebprogramdeleted', 'local_sebprogram');
    }

    /**
     * Returns non-localised description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $data = [];
        $data['userid'] = $this->userid;
        $data['title'] = $this->other['title'];
        $data['path'] = $this->other['path'];
        $data['executable'] = $this->other['executable'];

        return get_string('eventsebprogramdeleted_description', 'local_sebprogram', $data);
    }

    /**
     * Returns the name of the legacy event.
     *
     * @return string legacy event name
     */
    public static function get_legacy_eventname() {
        return '';
    }
}
