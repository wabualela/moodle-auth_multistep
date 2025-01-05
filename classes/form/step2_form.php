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

use moodleform;
use renderable;
use templatable;
use renderer_base;

require_once "$CFG->libdir/formslib.php";
require_once "$CFG->dirroot/auth/multistep/lib.php";

/**
 * Class step2_form
 *
 * @package    auth_multistep
 * @copyright  2025 Wail Abualela wailabualela@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class step2_form extends moodleform implements renderable, templatable {
    protected function definition() {
        global $CFG, $SITE;

        $mform = $this->_form;

        $mform->addElement('html', "<h1 class=\"login-heading mb-4\">{$SITE->fullname}</h1>");
        $mform->addElement('html', "<hr>");

        profile_signup_fields_by_shortnames($mform, ['dob', 'marital_status', 'gender']);

        $country             = get_string_manager()->get_list_of_countries();
        $default_country[''] = get_string('selectacountry');
        $country             = array_merge($default_country, $country);

        $mform->addElement('select', 'profile_field_nationality', get_string('nationality', 'auth_multistep'), $country);
        $mform->setType('profile_field_nationality', PARAM_TEXT);
        $mform->addRule('profile_field_nationality', get_string('required'), 'required', null, 'client');

        $mform->addElement('select', 'country', get_string('country'), $country);
        $mform->addRule('country', get_string('required'), 'required', null, 'client');

        if (! empty($CFG->country)) {
            $mform->setDefault('country', $CFG->country);
        } else {
            $mform->setDefault('country', '');
        }

        $mform->addElement('text', 'city', get_string('city'), 'maxlength="120" size="20"');
        $mform->setType('city', \core_user::get_property_type('city'));
        if (! empty($CFG->defaultcity)) {
            $mform->setDefault('city', $CFG->defaultcity);
        }

        $mform->addElement('html', "<hr>");
        $this->add_action_buttons(true, get_string('next'));

    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        ob_start();
        $this->display();
        $formhtml = ob_get_contents();
        ob_end_clean();
        $context = [
            'formhtml' => $formhtml
        ];
        return $context;
    }
}
