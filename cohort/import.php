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
 * Cohort import functions.
 *
 * @package    core_cohort
 * @copyright  2013 Michel Beloshitsky  {@link mailto:mbeloshitsky@gmail.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../config.php');
require($CFG->dirroot.'/course/lib.php');
require($CFG->dirroot.'/cohort/lib.php');
require($CFG->dirroot.'/cohort/edit_form.php');

include_once('import_form.php');

require_login();

$context = context_system::instance();

require_capability('moodle/cohort:manage', $context);

$returnurl = new moodle_url('/cohort/index.php', array('contextid'=>$context->id));


$strimportcohorts = get_string('importcohorts', 'cohort');

$PAGE->set_context($context);
$PAGE->set_url('/cohort/import.php', array());
$PAGE->set_context($context);

$mform_post = new cohorts_import_form(null);

// If a file has been uploaded, then process it
if ($mform_post->is_cancelled()) {
    redirect($returnurl);

} else if ($mform_post->get_data()) {
    echo $OUTPUT->header();

    $csv_encode = '/\&\#44/';
    if (isset($CFG->CSV_DELIMITER)) {
        $csv_delimiter = $CFG->CSV_DELIMITER;

        if (isset($CFG->CSV_ENCODE)) {
            $csv_encode = '/\&\#' . $CFG->CSV_ENCODE . '/';
        }
    } else {
        $csv_delimiter = ",";
    }

    $text = $mform_post->get_file_content('userfile');
    $text = preg_replace('!\r\n?!',"\n",$text);

    $rawlines = explode("\n", $text);
    unset($text);

    // make arrays of valid fields for error checking
    $required = array("cohortid" => 1);
    $optional = array("cohortname" => 1, "description" => 1);

    // --- get header (field names) ---
    $header = explode($csv_delimiter, array_shift($rawlines));
    // check for valid field names
    foreach ($header as $i => $h) {
        $h = trim($h); $header[$i] = $h; // remove whitespace
        if (!(isset($required[$h]) or isset($optionalDefaults[$h]) or isset($optional[$h]))) {
                print_error('invalidfieldname', 'error', 'import.php?id='.$id, $h);
            }
        if (isset($required[$h])) {
            $required[$h] = 2;
        }
    }
    // check for required fields
    foreach ($required as $key => $value) {
        if ($value < 2) {
            print_error('fieldrequired', 'error', 'import.php?id='.$id, $key);
        }
    }
    $linenum = 2; // since header is line 1

    foreach ($rawlines as $rawline) {

        $cohort = new stdClass();//to make Martin happy

    	$cohort->id          = 0;
	$cohort->contextid   = $context->id;
	$cohort->name        = '';
	$cohort->description = '';

        //Note: commas within a field should be encoded as &#44 (for comma separated csv files)
        //Note: semicolon within a field should be encoded as &#59 (for semicolon separated csv files)
        $line = explode($csv_delimiter, $rawline);
        foreach ($line as $key => $value) {
            //decode encoded commas
            $record[$header[$key]] = preg_replace($csv_encode, $csv_delimiter, trim($value));
        }
        if ($record[$header[0]]) {
            // add a new group to the database

            // add fields to object $user
            foreach ($record as $name => $value) {
                // check for required values
                if (isset($required[$name]) and !$value) {
                    print_error('missingfield', 'error', 'import.php?id='.$id, $name);
                } else if ($name == "cohortname") {
                    $cohort->name = $value;
                } else if ($name == "cohortid") {
                    $cohort->idnumber = $value;
                } else {
                // normal entry
                    $cohort->{$name} = $value;
		}

		if (!$cohort->name && $cohort->idnumber) {
			$cohort->name = $cohort->idnumber;
		}
	    }

	    if(cohort_add_cohort($cohort)) {
		    echo $OUTPUT->notification(get_string('cohortaddedsuccesfully', 'cohort', $cohort->name), 'notifysuccess');
	    } else {
		    echo $OUTPUT->notification(get_string('cohortnotaddederror', 'cohort', $cohort->name));
	    }

            unset ($cohort);
        }
    }

    echo $OUTPUT->single_button($returnurl, get_string('continue'), 'get');
    echo $OUTPUT->footer();
    die;
}

echo $OUTPUT->header();
echo $OUTPUT->heading_with_help($strimportcohorts, 'importcohorts', 'cohort');
echo $mform_post->display();
echo $OUTPUT->footer();

