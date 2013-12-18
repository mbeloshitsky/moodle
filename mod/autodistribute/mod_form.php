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
 * Instance add/edit form
 *
 * @package    mod_autodistribute
 * @copyright  2013, Michel Beloshitsky {link: mailto:mbeloshitsky@gmail.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/lib/grouplib.php');
require(dirname(__FILE__).'/locallib.php');

class mod_autodistribute_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $DB, $COURSE;

        $mform = $this->_form;

        $mform->addElement('text', 'name', 'Name', array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('text', 'students_per_subgroup', get_string('students_per_subgroup', 'autodistribute'));
        $mform->setDefault('students_per_subgroup', 12);
        $mform->addRule('students_per_subgroup', null, 'required', null, 'client');

        $mform->addElement('text', 'name_prefix', get_string('name_prefix', 'autodistribute'));
        $mform->setDefault('name_prefix', get_string('default_name_prefix', 'autodistribute'));

        $course = $DB->get_record('course', array('id'=>$COURSE->id), '*', MUST_EXIST);
        $grade_items = get_grade_items($course, $this->context);
        $mform->addElement('select', 'grade_id', get_string('dist_grade', 'autodistribute'), $grade_items);
        end($grade_items);
        $mform->setDefault('grade_id', key($grade_items));
        $mform->addRule('grade_id', null, 'required', null, 'client');

        $groups = groups_get_all_groups($COURSE->id);
        $group_items = array(0=>get_string('all_members', 'autodistribute'));
        foreach($groups as $group) {
            $group_items[$group->id] = $group->name;
        }
        $mform->addElement('select', 'group_id', get_string('group', 'autodistribute'), $group_items);
        $mform->setDefault('group_id', 0);
        $mform->addRule('group_id', null, 'required', null, 'client');

        $this->standard_coursemodule_elements();

        $mform->setDefault('visible', 0);

        $this->add_action_buttons();
    }
}
