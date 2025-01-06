<?php

use auth_multistep\form\step2_form;
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
 * TODO describe file index
 *
 * @package    auth_multistep
 * @copyright  2025 Wail Abualela wailabualela@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require "../../config.php";
require_once "$CFG->dirroot/user/profile/lib.php";
require_once "$CFG->dirroot/auth/multistep/classes/form/step2_form.php";
require_once "$CFG->dirroot/auth/multistep/classes/form/step3_form.php";

$PAGE->set_url('/auth/multistep/signup.php');
$PAGE->set_context(context_system::instance());

if (! $step = $SESSION->step) {
    redirect(get_login_url(), 'Please start from the beginning', 3);
}

switch ($step) {
    case 2:
        $mform_signup = new step2_form();
        break;
    case 3:
        $mform_signup = new \auth_multistep\form\step3_form();
        break;
    default:
        redirect(get_login_url());
}

if (! $userid = $SESSION->userid) {
    redirect(get_login_url(), 'Please start from the beginning', 3);
}

$user = $DB->get_record('user', ['id' => $SESSION->userid]);
profile_load_data($user);

if ($mform_signup->is_cancelled()) {
    redirect(get_login_url());

} else if ($data = $mform_signup->get_data()) {

    switch ($step) {
        case 2:
            $user->city = $data->city;
            $user->country = $data->country;
            $user->profile_field_nationality = $data->profile_field_nationality;
            $user->profile_field_gender = $data->profile_field_gender;
            $user->profile_field_dob = $data->profile_field_dob;
            $DB->update_record('user', $user);
            profile_save_data($user);
            $SESSION->step = 3;
            redirect(new moodle_url("$CFG->wwwroot/auth/multistep/signup.php"));
        case 3:
            $user->profile_field_education_level = $data->profile_field_education_level;
            $user->profile_field_current_job = $data->profile_field_current_job;
            $user->phone1 = $data->phone1;
            $user->profile_field_marital_status = $data->profile_field_marital_status;
            profile_save_data($user);
            $manager = new \core_privacy\local\sitepolicy\manager();
            if ($manager->is_defined()) {
                $manager->accept();
            }

            $SESSION->step = 0;
            $SESSION->userid = 0;
            $confirmationurl = null;

            if (! send_confirmation_email($user, $confirmationurl)) {
                throw new \moodle_exception('auth_emailnoemail', 'auth_email');
            }

            $emailconfirm = get_string('emailconfirm');
            $PAGE->navbar->add($emailconfirm);
            $PAGE->set_title($emailconfirm);
            $PAGE->set_heading($PAGE->course->fullname);
            echo $OUTPUT->header();
            notice(get_string('emailconfirmsent', '', $user->email), "$CFG->wwwroot/index.php");
            echo $OUTPUT->footer();
    }
}

$newaccount = get_string('newaccount');
$login      = get_string('login');

$PAGE->navbar->add($login);
$PAGE->navbar->add($newaccount);

$PAGE->set_pagelayout('login');
$PAGE->set_title($newaccount);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('login');

echo $OUTPUT->header();
$mform_signup->display();
echo $OUTPUT->footer();
