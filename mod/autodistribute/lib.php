<?php

/**
 * Subgroup distribution common routines.
 *
 * @package    mod
 * @subpackage autodistribute
 * @copyright  Michel Beloshitsky, mbeloshitsky@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/group/lib.php');

/**
 * Add book instance.
 *
 * @param stdClass $data
 * @param stdClass $mform
 * @return int new book instance id
 */
function autodistribute_add_instance($data, $mform) {
    global $DB;

    $data->intro = ' ';
    $data->introformat = FORMAT_MOODLE;
    $data->timecreated = time();
    $data->timemodified = $data->timecreated;
    if (!isset($data->customtitles)) {
        $data->customtitles = 0;
    }

    return $DB->insert_record('autodistribute', $data);
}

/**
 * Update book instance.
 *
 * @param stdClass $data
 * @param stdClass $mform
 * @return bool true
 */
function autodistribute_update_instance($data, $mform) {
    global $DB;

    $data->timemodified = time();
    $data->id = $data->instance;
    $data->completed = 0;
    if (!isset($data->customtitles)) {
        $data->customtitles = 0;
    }

    $DB->update_record('autodistribute', $data);

    $groups = $DB->get_records('autodistribute_groups', array('autodist_id'=>$data->id));
    foreach ($groups as $group) {
        groups_delete_group($group->group_id);
    }

    $DB->delete_records('autodistribute_groups', array('autodist_id'=>$data->id));

    return true;
}

/**
 * Delete book instance by activity id
 *
 * @param int $id
 * @return bool success
 */
function autodistribute_delete_instance($id) {
    global $DB;

    if (!$autodistribute = $DB->get_record('autodistribute', array('id'=>$id))) {
        return false;
    }

    $groups = $DB->get_records('autodistribute_groups', array('autodist_id'=>$autodistribute->id));
    foreach ($groups as $group) {
        groups_delete_group($group->group_id);
    }

    $DB->delete_records('autodistribute', array('id'=>$autodistribute->id));
    $DB->delete_records('autodistribute_groups', array('autodist_id'=>$autodistribute->id));
    return true;
}


/**
 * No cron in autodistribute.
 *
 * @return bool
 */
function autodistribute_cron () {
    return true;
}

/**
 * Return read actions.
 * @return array
 */
function autodistribute_get_view_actions() {
    global $CFG; // necessary for includes

    return array();
}

/**
 * Return write actions.
 * @return array
 */
function autodistribute_get_post_actions() {
    global $CFG; // necessary for includes

    return array();
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 * See get_array_of_activities() in course/lib.php
 *
 * @global object
 * @param object $coursemodule
 * @return object|null
 */
function autodistribute_get_coursemodule_info($coursemodule) {

    global $DB;

    if ($label = $DB->get_record('autodistribute', array('id'=>$coursemodule->instance), 'id, name, intro, introformat')) {
        if (empty($label->name)) {
            // label name missing, fix it
            $label->name = "autodistribute{$label->id}";
            $DB->set_field('autodistribute', 'name', $label->name, array('id'=>$label->id));
        }

        $info = new stdClass();
        $info->name = $label->name;
        return $info;
    } else {
        return null;
    }
}


/**
 * Returns all other caps used in module
 * @return array
 */
function autodistribute_get_extra_capabilities() {
    // used for group-members-only
    return array('moodle/site:accessallgroups');
}

/**
 * Supported features
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function autodistribute_supports($feature) {
    switch($feature) {
        case FEATURE_IDNUMBER:                return false;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return false;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return false;
        case FEATURE_NO_VIEW_LINK:            return false;

        default: return null;
    }
}
