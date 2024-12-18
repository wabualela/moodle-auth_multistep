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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/user/editlib.php');
require_once('lib.php');

class login_signup_form extends moodleform implements renderable, templatable
    {
    protected $step;

    function definition()
        {
        global $USER, $CFG;

        $mform = $this->_form;

        $this->step = optional_param('step', 1, PARAM_INT);

        switch ($this->step) {
            case 1:
                $this->step1_fields($mform);
                break;

            case 2:
                $this->step2_fields($mform);
                break;

            case 3:
                $this->step3_fields($mform);
                break;
            }

        }

    function export_for_template(renderer_base $output)
        {
        ob_start();
        $this->display();
        $formhtml = ob_get_contents();
        ob_end_clean();
        $context = [
            'formhtml' => $formhtml
        ];
        return $context;
        }

    public function step1_fields(&$mform)
        {
        global $CFG;

        //First name & Last name
        $namefields = useredit_get_required_name_fields();
        foreach ($namefields as $field) {
            $mform->addElement('text', $field, get_string($field), 'maxlength="100" size="30"');
            $mform->setType($field, core_user::get_property_type('firstname'));
            $stringid = 'missing' . $field;
            if (! get_string_manager()->string_exists($stringid, 'moodle')) {
                $stringid = 'required';
                }
            $mform->addRule($field, get_string($stringid), 'required', null, 'client');
            }

        //sex
        $sex = ['ذكر', 'انثي'];
        $mform->addElement('select', 'sex', get_string('gender', 'auth_multistep'), $sex, ['style' => 'width:100%']);

        //nationality
        $nationality             = get_string_manager()->get_list_of_countries();
        $default_nationality[''] = get_string('selectacountry');
        $nationality             = array_merge($default_nationality, $nationality);
        $mform->addElement('select', 'nationality', get_string('nationality', 'auth_multistep'), $nationality, ['style' => 'width:100%']);

        if (! empty($CFG->country)) {
            $mform->setDefault('nationality', $CFG->country);
            } else {
            $mform->setDefault('nationality', '');
            }
        //Date of birth
        $mform->addElement('date_selector', 'assesstimefinish', get_string('dateofbirth', 'auth_multistep'));
        //Marital status
        $marital_status = ['male', 'female'];
        $mform->addElement('select', 'country', get_string('country'), $marital_status, ['style' => 'width:100%']);

        //City
        $mform->addElement('text', 'city', get_string('city'), 'maxlength="120" size="20"');
        $mform->setType('city', core_user::get_property_type('city'));
        if (! empty($CFG->defaultcity)) {
            $mform->setDefault('city', $CFG->defaultcity);
            }

        //County
        $country             = get_string_manager()->get_list_of_countries();
        $default_country[''] = get_string('selectacountry');
        $country             = array_merge($default_country, $country);
        $mform->addElement('select', 'country', get_string('country'), $country, ['style' => 'width:100%']);

        if (! empty($CFG->country)) {
            $mform->setDefault('country', $CFG->country);
            } else {
            $mform->setDefault('country', '');
            }


        $mform->addElement('hidden', 'step', $this->step + 1);
        $mform->setType('step', PARAM_INT);

        // buttons
        $this->set_display_vertical();
        $mform->addElement('submit', 'next', get_string('next', 'auth_multistep'));

        // $this->add_action_buttons(true, get_string('next', 'auth_multistep'));

        }

    public function step2_fields(&$mform)
        {
        // Phone
        $mform->addElement('float', 'phone1', get_string('phone1'), ['maxlength' => '120', 'size' => '20', 'style' => 'width:100%']);
        $mform->setType('phone1', core_user::get_property_type('phone1'));

        // education
        $education = [
            'غير ذلك',
            'الاعدادية المتوسطة',
            'ثانوية  ',
            'دبلوم',
            'بكالريوس',
            'ماجستير',
            'دكتوراه',
        ];
        $mform->addElement('select', 'education', get_string('education', 'auth_multistep'), $education, ['style' => 'width:100%']);

        // Speciality
        $mform->addElement('float', 'speciality', get_string('speciality', 'auth_multistep'), ['maxlength' => '120', 'size' => '20', 'style' => 'width:100%']);
        $mform->setType('speciality', PARAM_TEXT);

        // Job
        $mform->addElement('float', 'job', get_string('job', 'auth_multistep'), ['maxlength' => '120', 'size' => '20', 'style' => 'width:100%']);
        $mform->setType('job', PARAM_TEXT);


        $mform->addElement('hidden', 'step', $this->step + 1);
        $mform->setType('step', PARAM_INT);

        // buttons        
        $this->set_display_vertical();
        $this->add_action_buttons(true, get_string('next', 'auth_multistep'));

        }

    public function step3_fields(&$mform)
        {

        // full name (certificaitons)
        $mform->addElement('float', 'fullname', get_string('fullname', 'auth_multistep'), ['maxlength' => '120', 'size' => '20', 'style' => 'width:100%']);
        $mform->setType('fullname', PARAM_TEXT);
        //- email
        //- password
        //- username
        //- site policy

        }
    }

