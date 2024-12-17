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
 * TODO describe file signup
 *
 * @package    auth_multistep
 * @copyright  2024 Wail Abualela wailabualela@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/auth/multistep/multistep_form.php');

$PAGE->set_url('/auth/multistep_signup/signup.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'auth_multistep'));

$form = new multistep_signup_form();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/'));
    } else if ($data = $form->get_data()) {
    // Process form submission at each step.
    if ($data->step > 3) {
        // Finalize user creation.
        create_user($data);
        redirect(new moodle_url('/login/index.php'), get_string('accountcreated', 'auth_multistep'));
        } else {
        redirect(new moodle_url('/auth/multistep_signup/signup.php', ['step' => $data->step]));
        }
    }

echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();
