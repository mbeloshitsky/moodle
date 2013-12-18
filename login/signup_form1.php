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
 * User sign-up form.
 *
 * @package    core
 * @subpackage auth
 * @copyright  1999 onwards Martin Dougiamas  http://dougiamas.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');

class login_signup_form extends moodleform {
    function definition() {
        global $USER, $CFG, $DB;

        $mform = $this->_form;


        $mform->addElement('header', 'supplyinfo', get_string('supplyinfo'),'');

	$filials = array(null=>get_string('selectafilial'));
	$filials_db = $DB->get_records('stud_filials');
	foreach($filials_db as $frow) {
		$filials[$frow->id] = $frow->name;
	}
    
    $mform->addElement('ajaxselect', 'filial', get_string('filial'), $filials);
    $mform->addRule('filial', get_string('missingfilial'), 'required', null, 'server');

    $mform->addElement('ajaxselect', 'group', get_string('group'), array(null=>get_string('selectagroup')));
	$mform->addRule('group', get_string('missinggroup'), 'required', null, 'server');


	$mform->addElement('ajaxselect', 'fio', get_string('fio'), array(null=>get_string('selectafio'),-1=>get_string('manualfio')));
    $mform->addRule('fio', get_string('missingfio'), 'required', null, 'server');

    $mform->addElement('text', 'manualfio', get_string('fio'), 'maxlength="100" size="70"');
    $mform->setType('manualfio', PARAM_NOTAGS);

    $mform->addElement('text', 'email', get_string('email'), 'maxlength="100" size="25"');
    $mform->addRule('email', get_string('missingemail'), 'required', null, 'server');
    $mform->setType('email', PARAM_NOTAGS);

    $mform->addElement('header', 'createuserandpass', get_string('createuserandpass'), '');

    $mform->addElement('text', 'username', get_string('username'), 'maxlength="100" size="12"');
    $mform->setType('username', PARAM_NOTAGS);
    $mform->addRule('username', get_string('missingusername'), 'required', null, 'server');

    if (!empty($CFG->passwordpolicy)){
        $mform->addElement('static', 'passwordpolicyinfo', '', print_password_policy());
    }
    $mform->addElement('passwordunmask', 'password', get_string('password'), 'maxlength="32" size="12"');
    $mform->setType('password', PARAM_RAW);
    $mform->addRule('password', get_string('missingpassword'), 'required', null, 'server');

    if ($this->signup_captcha_enabled()) {
        $mform->addElement('recaptcha', 'recaptcha_element', get_string('recaptcha', 'auth'), array('https' => $CFG->loginhttps));
        $mform->addHelpButton('recaptcha_element', 'recaptcha', 'auth');
    }

        // profile_signup_fields($mform);

        if (!empty($CFG->sitepolicy)) {
            $mform->addElement('header', 'policyagreement', get_string('policyagreement'), '');
            $mform->setExpanded('policyagreement');
            $mform->addElement('static', 'policylink', '', '<a href="'.$CFG->sitepolicy.'" onclick="this.target=\'_blank\'">'.get_String('policyagreementclick').'</a>');
            $mform->addElement('checkbox', 'policyagreed', get_string('policyaccept'));
            $mform->addRule('policyagreed', get_string('policyagree'), 'required', null, 'server');
        }

        // buttons
        $this->add_action_buttons(true, get_string('createaccount'));

    }

    function definition_after_data(){
        $mform = $this->_form;
        $mform->applyFilter('username', 'trim');
    }

    function validation($data, $files) {
        global $CFG, $DB;
        $errors = parent::validation($data, $files);

        $authplugin = get_auth_plugin($CFG->registerauth);

        if ($DB->record_exists('user', array('username'=>$data['username'], 'mnethostid'=>$CFG->mnet_localhost_id))) {
            $errors['username'] = get_string('usernameexists');
        } else {
            //check allowed characters
            if ($data['username'] !== textlib::strtolower($data['username'])) {
                $errors['username'] = get_string('usernamelowercase');
            } else {
                if ($data['username'] !== clean_param($data['username'], PARAM_USERNAME)) {
                    $errors['username'] = get_string('invalidusername');
                }

            }
        }

        if ($data['fio'] == -1 && empty($data['fio'])) {
                $errors['fio'] = get_string('missingfio');
        }

        //check if user exists in external db
        //TODO: maybe we should check all enabled plugins instead
        if ($authplugin->user_exists($data['username'])) {
            $errors['username'] = get_string('usernameexists');
        }


        if (!empty($data['email']) && ! validate_email($data['email'])) {
            $errors['email'] = get_string('invalidemail');

        } else if (!empty($data['email']) && $DB->record_exists('user', array('email'=>$data['email']))) {
            $errors['email'] = get_string('emailexists').' <a href="forgot_password.php">'.get_string('newpassword').'?</a>';
        }
        if (!isset($errors['email'])) {
            if ($err = email_is_not_allowed($data['email'])) {
                $errors['email'] = $err;
            }
        }

        $errmsg = '';
        if (!check_password_policy($data['password'], $errmsg)) {
            $errors['password'] = $errmsg;
        }

        if ($this->signup_captcha_enabled()) {
            $recaptcha_element = $this->_form->getElement('recaptcha_element');
            if (!empty($this->_form->_submitValues['recaptcha_challenge_field'])) {
                $challenge_field = $this->_form->_submitValues['recaptcha_challenge_field'];
                $response_field = $this->_form->_submitValues['recaptcha_response_field'];
                if (true !== ($result = $recaptcha_element->verify($challenge_field, $response_field))) {
                    $errors['recaptcha'] = $result;
                }
            } else {
                $errors['recaptcha'] = get_string('missingrecaptchachallengefield');
            }
        }
        // Validate customisable profile fields. (profile_validation expects an object as the parameter with userid set)
        $dataobject = (object)$data;
        $dataobject->id = 0;
        $errors += profile_validation($dataobject, $files);

        return $errors;

    }

    /**
     * Returns whether or not the captcha element is enabled, and the admin settings fulfil its requirements.
     * @return bool
     */
    function signup_captcha_enabled() {
        global $CFG;
        return !empty($CFG->recaptchapublickey) && !empty($CFG->recaptchaprivatekey) && get_config('auth/email', 'recaptcha');
    }

}
