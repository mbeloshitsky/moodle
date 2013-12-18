<?php

require_once('../config.php');
require_once($CFG->libdir.'/gdlib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/user/editlib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot.'/cohort/lib.php');

$context = context_system::instance();


function cohort_get_members($cohortid) {
    global $DB;

    $params = array('cohortid'=>$cohortid);
    $fields = 'SELECT id, username, email, firstname, lastname ' . $this->required_fields_sql('u');

    $sql = " FROM {user} u
             LEFT JOIN {cohort_members} cm ON (cm.userid = u.id AND cm.cohortid = :cohortid)
             WHERE cm.id IS NULL";

    return $DB->get_records_sql($fields . $sql, $params);
}

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

if ($cohorts_to_reset = $multireset_form->get_data()) {
    foreach ($cohorts_to_reset['cohorts'] as $cohortid) {
        echo $OUTPUT->heading($cohortid);
        echo print_r(cohort_get_members($cohortid));
    }
} else {
    $multireset_form->display();
}

echo $OUTPUT->footer();



