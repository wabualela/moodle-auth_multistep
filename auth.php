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
 * Main class for the multistep authentication plugin
 *
 * Documentation: {@link https://docs.moodle.org/dev/Authentication_plugins}
 *
 * @package    auth_multistep
 * @copyright  2024 Wail Abualela wailabualela@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once "$CFG->libdir/authlib.php";
require_once "$CFG->dirroot/user/lib.php";
require_once "$CFG->dirroot/user/profile/lib.php";
require_once "$CFG->dirroot/auth/multistep/signup_form.php";


/**
 * Authentication plugin auth_multistep
 *
 * @package    auth_multistep
 * @copyright  2024 Wail Abualela wailabualela@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_plugin_multistep extends auth_plugin_base {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'multistep';
    }

    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist.
     *
     * @param string $username The username
     * @param string $password The password
     * @return bool Authentication success or failure.
     */
    function user_login($username, $password) {
        global $CFG, $DB;
        if ($user = $DB->get_record('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id))) {
            return validate_internal_user_password($user, $password);
        }
        return false;
    }

    /**
     * Updates the user's password.
     *
     * called when the user password is updated.
     *
     * @param  object  $user        User table object  (with system magic quotes)
     * @param  string  $newpassword Plaintext password (with system magic quotes)
     * @return boolean result
     *
     */
    function user_update_password($user, $newpassword) {
        $user = get_complete_user_data('id', $user->id);
        // This will also update the stored hash to the latest algorithm
        // if the existing hash is using an out-of-date algorithm (or the
        // legacy md5 algorithm).
        return update_internal_user_password($user, $newpassword);
    }

    public function can_signup() {
        return true;
    }

    /**
     * Sign up a new user ready for confirmation.
     * Password is passed in plaintext.
     *
     * @param object $user new user object
     * @param boolean $notify print notice with link and terminate
     */
    public function user_signup($user, $notify = true) {
        global $CFG, $DB, $SESSION;

        $user->username  = $user->email;
        $confirmationurl = null;
        $plainpassword   = $user->password;
        $user->password  = hash_internal_user_password($user->password);

        if (empty($user->calendartype)) {
            $user->calendartype = $CFG->calendartype;
        }

        $user->id        = user_create_user($user, false, false);
        $SESSION->userid = $user->id;
        $SESSION->step   = 2;

        user_add_password_history($user->id, $plainpassword);

        // Save wantsurl against user's profile, so we can return them there upon confirmation.
        if (! empty($SESSION->wantsurl)) {
            set_user_preference('auth_multistep_wantsurl', $SESSION->wantsurl, $user);
        }

        // Trigger event.
        \core\event\user_created::create_from_userid($user->id)->trigger();

        redirect(new moodle_url("$CFG->wwwroot/auth/multistep/signup.php"));
    }

    /**
     * Validates the standard sign-up data (except recaptcha that is validated by the form element).
     *
     * @param  array $data  the sign-up data
     * @param  array $files files among the data
     * @return array list of errors, being the key the data element name and the value the error itself
     * @since Moodle 3.2
     */
    public function signup_validate_data($data, $files) {
        global $CFG, $DB;
        $errors     = [];
        $authplugin = get_auth_plugin($CFG->registerauth);

        if (! validate_email($data['email'])) {
            $errors['email'] = get_string('invalidemail');

        } else if (empty($CFG->allowaccountssameemail)) {
            // Emails in Moodle as case-insensitive and accents-sensitive. Such a combination can lead to very slow queries
            // on some DBs such as MySQL. So we first get the list of candidate users in a subselect via more effective
            // accent-insensitive query that can make use of the index and only then we search within that limited subset.
            $sql = "SELECT 'x'
                  FROM {user}
                 WHERE " . $DB->sql_equal('email', ':email1', false, true) . "
                   AND id IN (SELECT id
                                FROM {user}
                               WHERE " . $DB->sql_equal('email', ':email2', false, false) . "
                                 AND mnethostid = :mnethostid)";

            $params = array(
                'email1' => $data['email'],
                'email2' => $data['email'],
                'mnethostid' => $CFG->mnet_localhost_id,
            );

            // If there are other user(s) that already have the same email, show an error.
            if ($DB->record_exists_sql($sql, $params)) {
                $forgotpasswordurl  = new moodle_url('/login/forgot_password.php');
                $forgotpasswordlink = html_writer::link($forgotpasswordurl, get_string('emailexistshintlink'));
                $errors['email']    = get_string('emailexists') . ' ' . get_string('emailexistssignuphint', 'moodle', $forgotpasswordlink);
            }
        }
        if (empty($data['email2'])) {
            $errors['email2'] = get_string('missingemail');

        } else if (core_text::strtolower($data['email2']) != core_text::strtolower($data['email'])) {
            $errors['email2'] = get_string('invalidemail');
        }
        if (! isset($errors['email'])) {
            if ($err = email_is_not_allowed($data['email'])) {
                $errors['email'] = $err;
            }
        }

        // Construct fake user object to check password policy against required information.
        $tempuser = new stdClass();
        // To prevent errors with check_password_policy(),
        // the temporary user and the guest must not share the same ID.
        $tempuser->id        = (int) $CFG->siteguest + 1;
        $tempuser->firstname = $data['firstname'];
        $tempuser->lastname  = $data['lastname'];
        $tempuser->username  = $data['email'];
        $tempuser->email     = $data['email'];

        $errmsg = '';
        if (! check_password_policy($data['password'], $errmsg, $tempuser)) {
            $errors['password'] = $errmsg;
        }

        // Validate customisable profile fields. (profile_validation expects an object as the parameter with userid set).
        $dataobject     = (object) $data;
        $dataobject->id = 0;
        $errors += profile_validation($dataobject, $files);

        return $errors;
    }

    /**
     * Returns true if plugin allows confirming of new users.
     *
     * @return bool
     */
    public function can_confirm() {
        return true;
    }

    /**
     * Confirm the new user as registered.
     *
     * @param string $username
     * @param string $confirmsecret
     */
    function user_confirm($username, $confirmsecret) {
        global $DB, $SESSION;
        $user = get_complete_user_data('username', $username);

        if (! empty($user)) {
            if ($user->auth != $this->authtype) {
                return AUTH_CONFIRM_ERROR;

            } else if ($user->secret === $confirmsecret && $user->confirmed) {
                return AUTH_CONFIRM_ALREADY;

            } else if ($user->secret === $confirmsecret) {   // They have provided the secret key to get in
                $DB->set_field("user", "confirmed", 1, array("id" => $user->id));

                if ($wantsurl = get_user_preferences('auth_email_wantsurl', false, $user)) {
                    // Ensure user gets returned to page they were trying to access before signing up.
                    $SESSION->wantsurl = $wantsurl;
                    unset_user_preference('auth_email_wantsurl', $user);
                }

                return AUTH_CONFIRM_OK;
            }
        } else {
            return AUTH_CONFIRM_ERROR;
        }
    }

    /**
     * Return a form to capture user details for account creation.
     * This is used in /login/signup.php.
     * @return moodleform A form which edits a record from the user table.
     */
    public function signup_form() {
        return new login_signup_form(null, null, 'post', '', ['autocomplete' => 'yes']);
    }

    public function prevent_local_passwords() {
        return false;
    }

    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * @return bool
     */
    function is_internal() {
        return true;
    }

    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     *
     * @return bool
     */
    function can_change_password() {
        return true;
    }

    /**
     * Returns the URL for changing the user's pw, or empty if the default can
     * be used.
     *
     * @return moodle_url
     */
    function change_password_url() {
        return null; // use default internal method
    }

    /**
     * Returns true if plugin allows resetting of internal password.
     *
     * @return bool
     */
    function can_reset_password() {
        return true;
    }

    /**
     * Returns true if plugin can be manually set.
     *
     * @return bool
     */
    function can_be_manually_set() {
        return true;
    }

    /**
     * Returns whether or not the captcha element is enabled.
     * @return bool
     */
    function is_captcha_enabled() {
        return get_config("auth_{$this->authtype}", 'recaptcha');
    }
}
