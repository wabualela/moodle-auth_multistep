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

namespace auth_multistep\form;

require_once "$CFG->libdir/formslib.php";
require_once "$CFG->dirroot/auth/multistep/lib.php";

/**
 * Class form3_form
 *
 * @package    auth_multistep
 * @copyright  2025 Wail Abualela wailabualela@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class step3_form extends \moodleform {
    protected function definition() {
        global $CFG, $SITE;

        $mform = $this->_form;

        $mform->addElement('html', "<h1 class=\"login-heading mb-4\">{$SITE->fullname}</h1>");
        $mform->addElement('html', "<hr>");

        profile_signup_fields_by_shortnames($mform, ['education_level', 'current_job', 'marital_status']);

        $mform->addElement('text', 'phone1', get_string('phone1'), 'maxlength="100" size="25"');
        $mform->setType('phone1', \core_user::get_property_type('phone1'));
        $mform->addRule('phone1', get_string('missingemail'), 'required', null, 'client');
        $mform->setForceLtr('phone1');

        $manager = new \core_privacy\local\sitepolicy\manager();
        if ($manager->is_defined()) {
            $mform->addElement('checkbox', 'sitepolicyagree', '', '<a href="' . $manager->get_redirect_url() . '">' . get_string('sitepolicyagreement', 'auth_multistep') . '</a>');
            $mform->addRule('sitepolicyagree', get_string('required'), 'required', null, 'client');
        }
        $manager->signup_form($mform);

        $mform->addElement('html', "<hr>");
        $this->add_action_buttons(true, get_string('submit'));

    }
}
