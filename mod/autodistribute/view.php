<?php

/**
 * View autdistribution form.
 *
 * @package    mod_autodistribute
 * @subpackage <subpackage>
 * @copyright  Michel Beloshitsky, mbeloshitsky@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/** TODO: Check access rights */

require(dirname(__FILE__).'/../../config.php');
require_once($CFG->libdir.'/gradelib.php');
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/grader/lib.php';

require(dirname(__FILE__).'/locallib.php');
require(dirname(__FILE__).'/confirm_form.php');
require_once($CFG->libdir.'/completionlib.php');

$id        = required_param('id', PARAM_INT);               // Course Module ID
$action    = optional_param('action', 'none', PARAM_ALPHA);
$edit      = optional_param('edit', -1, PARAM_BOOL);        // Edit mode

if ($id != NULL) {
    $cm = get_coursemodule_from_id('autodistribute', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
    $autodistribute = $DB->get_record('autodistribute', array('id'=>$cm->instance), '*', MUST_EXIST);
}

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/autodistribute:distribute', $context);

echo $OUTPUT->header();
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/mod/autodistribute/autodistribute.js') );

$grade_items = get_grade_items($course, $context);

$mform = new autodistribute_confirm_form(
    new moodle_url('/mod/autodistribute/view.php', array('id'=>$id)),
    array());

if ($fromform = $mform->get_data()) {

    if ($autodistribute->completed > 0) {
        echo $OUTPUT->error_text(get_string('error_already_done', 'autodistribute'));
        echo $OUTPUT->footer();
        exit();
    }

    list ($students, $studentinfo, $studentgrades) = prepare_data($course, $context, $autodistribute->grade_id, $autodistribute->group_id);

    echo $OUTPUT->heading(get_string('total_dist', 'autodistribute', count($students)));

    do_autodistribute($students, $autodistribute->students_per_subgroup, $autodistribute->name_prefix, $course, $autodistribute);

} else {
    if ($autodistribute->completed == 0) {
        list ($students, $studentinfo, $studentgrades) = prepare_data($course, $context, $autodistribute->grade_id, $autodistribute->group_id);

        echo $OUTPUT->heading(get_string('total_for_dist', 'autodistribute', count($students)));

        autodistribute_preview($students, $autodistribute->students_per_subgroup, $autodistribute->name_prefix, $studentinfo);

        $mform->display();    
    } else {
        list ($students, $studentinfo, $studentgrades) = prepare_data($course, $context, $autodistribute->grade_id, $autodistribute->group_id);

        $groups = $DB->get_records('autodistribute_groups', array('autodist_id'=>$cm->instance));
        $dstudents = array();

        foreach ($groups as $group) {
            echo '<div group_id="'.$group->group_id.'">';
            echo $OUTPUT->heading(groups_get_group_name($group->group_id));
            $members  = groups_get_members($group->group_id);
            foreach ($members as $member) {
                $dstudents[$member->id] = 1;
                $gradestring = '';
                foreach ($studentgrades[$member->id] as $gid => $gvalue) {
                    if ($gradestring != '')
                        $gradestring .= ' - ';
		    if(!empty($gvalue->finalgrade))
	                $gradestring .= $gvalue->finalgrade;
                }
                echo '<span id="'.$member->id.'"><a class="moveup" href="javascript:">↑</a> <a class="movedown" href="javascript:">↓</a> '.fullname($member).' - '.$students[$member->id].' ('.$gradestring.')<br /></span>';
            }
            echo '</div>';
        }

        echo $OUTPUT->heading('Undistributed students');
        $undistributed_count = 0;
        foreach($studentinfo as $sid => $sinfo) {
            if (!$dstudents[$sid]) {
                $undistributed_count += 1;
                echo '<span id="'.$sid.'"> '.fullname($studentinfo[$sid]).' - '.$students[$sid].'<br /></span>';    
            }
        }
        echo $OUTPUT->heading('Undistributed/Total ('.$undistributed_count.'/'.count($studentinfo).')');
    }   
}

echo $OUTPUT->footer();
