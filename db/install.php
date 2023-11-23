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
 * Installation file.
 * @package    repository_opencast
 * @copyright  2017 Andreas Wagner, SYNERGY LEARNING
 * @author     Andreas Wagner
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Installation steps for the opencast repository.
 * @return bool
 * @throws repository_exception
 */
function xmldb_repository_opencast_install() {
    global $CFG;

    $result = true;
    require_once($CFG->dirroot . '/repository/lib.php');

    $opencastplugin = new repository_type('opencast', [], true);

    if (!$id = $opencastplugin->create(true)) {
        $result = false;
    }
    return $result;
}
