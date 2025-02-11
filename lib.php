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

require_once "$CFG->dirroot/user/profile/lib.php";

/**
 * functions for the multistep authentication plugin
 *
 * @package    auth_multistep
 * @copyright  2024 Wail Abualela wailabualela@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function multistep_extend_navigation(global_navigation $navigation) {
    global $PAGE;

    $PAGE->requires->css('/auth/multistep/styles.css');
}


/**
 * Adds code snippet to a moodle form object for custom profile fields that
 * should appear on the signup page
 * @param MoodleQuickForm $mform moodle form object
 */
function profile_signup_fields_by_shortnames(MoodleQuickForm $mform, array $shortnames = []) : void {

    if ($fields = profile_get_signup_fields()) {
        foreach ($fields as $field) {
            if (! in_array($field->object->field->shortname, $shortnames)) {
                continue;
            }
            $field->object->edit_field($mform);
        }
    }
}