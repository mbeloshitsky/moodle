<?php

/**
 * <description>
 *
 * @package    usurt
 * @subpackage <subpackage>
 * @copyright  Michel Beloshitsky, mbeloshitsky@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/gradelib.php');
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/grader/lib.php';

require_once($CFG->dirroot.'/group/lib.php');

function autodistribute_students ($students, $students_per_group) {
    asort($students);
    $student_subgroups = array_chunk(array_reverse($students, true), $students_per_group, true);

    $last_subgroup = end($student_subgroups);

    return $student_subgroups;
}

class subgroups_namer {

    private $names = array();

    public function get_name($pfx, $min, $max) {
        $i = 1;
        $sfx = '';
        $name = $pfx.'_'.round($min).'..'.round($max);
        while (array_key_exists("$name$sfx", $this->names)) {
            $sfx = " ($i)";
            $i++;
        }
        $this->names["$name$sfx"] = true;
        return "$name$sfx";
    }
}

function do_autodistribute ($students, $students_per_group, $group_prefix, $course, $coursemodule) {
    global $OUTPUT, $DB;

    $student_subgroups = autodistribute_students($students, $students_per_group);
    $subgroup_namer = new subgroups_namer();

    foreach ($student_subgroups as $subgroup) {
        $subgroup_data = new stdClass();
        $subgroup_data->courseid = $course->id;
        $max_r = end($subgroup);
        $min_r = reset($subgroup);
        $subgroup_data->name = $subgroup_namer->get_name($group_prefix, $min_r, $max_r);
        $subgroup_id = groups_create_group($subgroup_data);

        $subgroup_link = new stdClass();
        $subgroup_link->autodist_id = $coursemodule->id;
        $subgroup_link->group_id = $subgroup_id;

        $DB->insert_record('autodistribute_groups', $subgroup_link);

        $group_members_count = 0;
        foreach ($subgroup as $student_id => $grade) {
            $group_members_count++;
            groups_add_member($subgroup_id, $student_id);
        }

        $cm_update = new stdClass();
        $cm_update->completed = time();
        $cm_update->id = $coursemodule->id;
        $DB->update_record('autodistribute', $cm_update);
        echo $OUTPUT->notification(get_string('group_creation', 'autodistribute', array('name' => $subgroup_data->name, 'count'=>$group_members_count)));
    }
}

function autodistribute_preview ($students, $students_per_group, $group_prefix, $users_info) {
    global $OUTPUT;

    $student_subgroups = autodistribute_students($students, $students_per_group);
    $subgroup_namer = new subgroups_namer();

    foreach ($student_subgroups as $subgroup) {
        $max_r = end($subgroup);
        $min_r = reset($subgroup);
        echo $OUTPUT->heading($subgroup_namer->get_name($group_prefix, $min_r, $max_r));
        foreach ($subgroup as $student_id => $grade) {
            echo fullname($users_info[$student_id]) . ' - ' . $grade . '<br />';
        }
    }
}

function prepare_data($course, $context, $distribute_grade_id, $distribute_group_id) {
    /// return tracking object
    $gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'grader', 'courseid'=>$course->id, 'page'=>0));

    grade_regrade_final_grades($course->id);

    // Selecting students, whose final grades exist
    $students = array();
    $studentinfo = array();
    $studentgrades = array();

    $page = 0;

    do {
        $report = new grade_report_grader($course->id, $gpr, $context, $page, 0);
        $users = $report->load_users();
        $report->load_final_grades();

        foreach($report->grades as $studentid => $user_grades) {
            $distribution_grade = $user_grades[$distribute_grade_id]->finalgrade;

            if ($distribute_group_id == 0 || groups_is_member($distribute_group_id, $studentid)) {
                if ($distribution_grade != '') {
                    $students[$studentid] = $distribution_grade;
                }   
                $studentinfo[$studentid] = $users[$studentid];
                $studentgrades[$studentid] = $user_grades;
            }
        }
        $page++;
    } while ($page*$report->get_students_per_page() < $report->get_numusers());

    return array($students, $studentinfo, $studentgrades);

}

function get_grade_items($course, $context) {
    $gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'grader', 'courseid'=>$course->id, 'page'=>0));

    $report = new grade_report_grader($course->id, $gpr, $context, 0, 0);

    $grade_items = array();
    foreach($report->gtree->get_items() as $itemid => $grade_item) {
        $grade_items[$itemid] = $grade_item->itemname == '' ? get_string('final_grade', 'autodistribute') : $grade_item->itemname;
    }

    return $grade_items;
}

