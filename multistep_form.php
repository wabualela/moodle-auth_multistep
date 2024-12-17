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
 * TODO describe file multistep_form
 *
 * @package    auth_multistep
 * @copyright  2024 Wail Abualela wailabualela@hotmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->libdir/formslib.php");

class multistep_signup_form extends moodleform
    {
    protected $step;

    public function definition()
        {
        global $CFG;

        $mform      = $this->_form;
        $this->step = optional_param('step', 1, PARAM_INT);

        switch ($this->step) {
            case 1:
                $this->add_step1_fields($mform);
                break;

            case 2:
                $this->add_step2_fields($mform);
                break;

            case 3:
                $this->add_step3_fields($mform);
                break;
            }

        // Add navigation buttons
        $mform->addElement('hidden', 'step', $this->step + 1);
        $mform->setType('step', PARAM_INT);
        $mform->addElement('submit', 'next', get_string('next', 'auth_multistep'));
        }

    private function add_step1_fields(&$mform)
        {
        $mform->addElement('text', 'firstname', get_string('firstname'));
        $mform->setType('firstname', PARAM_NOTAGS);
        $mform->addRule('firstname', null, 'required', null, 'client');

        $mform->addElement('text', 'lastname', get_string('lastname'));
        $mform->setType('lastname', PARAM_NOTAGS);
        $mform->addRule('lastname', null, 'required', null, 'client');
        }

    private function add_step2_fields(&$mform)
        {
        $mform->addElement('text', 'username', get_string('username'));
        $mform->setType('username', PARAM_USERNAME);
        $mform->addRule('username', null, 'required', null, 'client');

        $mform->addElement('password', 'password', get_string('password'));
        $mform->setType('password', PARAM_RAW);
        $mform->addRule('password', null, 'required', null, 'client');
        }

    private function add_step3_fields(&$mform)
        {
        $mform->addElement('text', 'phone', get_string('phone', 'auth_multistep'));
        $mform->setType('phone', PARAM_TEXT);
        }
    }
