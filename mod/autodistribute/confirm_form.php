<?php

/**
 * <description>
 *
 * @package    mod_autodistribute
 * @copyright  Michel Beloshitsky, mbeloshitsky@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/formslib.php");

class autodistribute_confirm_form extends moodleform {

    public function definition() {

        $this->add_action_buttons(true, get_string('distribute', 'autodistribute'));

    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
};