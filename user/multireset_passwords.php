<?php

require_once('../config.php');
require_once($CFG->libdir.'/gdlib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/user/editlib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot.'/cohort/lib.php');

$context = context_system::instance();

class multireset_password_form extends moodleform {

    function definition() {
        global $USER, $CFG, $COURSE, $context;

        $mform =& $this->_form;

        $cohorts_result = cohort_get_cohorts($context->id, 0, 0);
        $cohorts_options = array();
        foreach($cohorts_result['cohorts'] as $id => $cohort) {
            $cohorts_options[$id] = $cohort->name;
        }

        $cohort_select = $mform->addElement('select', 'cohorts',
            'Cohorts',
            $cohorts_options,
            array('multiple'=>'multiple', 'size'=>16));
        $cohort_select->setMultiple(true);


        $this->add_action_buttons();

    }
}

$multireset_form = new multireset_password_form();

echo $OUTPUT->header();

if ($cohorts_to_reset = $userform->get_data()) {
    echo print_r($cohorts_to_reset);
} else {
    $multireset_form->display();
}

echo $OUTPUT->footer();



