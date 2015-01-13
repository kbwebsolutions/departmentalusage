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
 * Departmental Report.
 *
 * This report shows a summary of all courses which a user with a specific role type has access to.
 *
 * @package report_departmentalusage
 * @copyright 2013 Kieran Briggs - The Sheffield College
 * @email: kieran.briggs@sheffcol.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
require('../../config.php');
require_once($CFG->dirroot.'/lib/statslib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/report/departmentalusage/lib.php');

require_login();

global $USER;

$uid = (int)$USER->id;

/** Page Settings **/
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Departmental Report');
$PAGE->set_heading('Departmental Report', 3);
$PAGE->set_url('/report/departmentalusage/index.php');
$PAGE->set_pagelayout('report');
$PAGE->add_body_class('departmentreport');

/** Navigation Bar **/
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('pluginname', 'report_departmentalusage'), new moodle_url('/report/departmentalusage/index.php'));

$hod			= optional_param('hod', 0, PARAM_INT); // Hod id number
$timefrom   	= optional_param('timefrom', 0, PARAM_INT); // how far back to look...
$showteachers   = optional_param('showteachers', false, PARAM_BOOL); // Show teachers in results or not
$downloadtype  	= optional_param('reporttype', 'display', PARAM_RAW); // Show teachers in results or not

$admins = get_admins();
$isadmin = false;
foreach ($admins as $admin) {
        if ($USER->id == $admin->id) {
            $isadmin = true;
            break;
        }
}
    if ($isadmin) {
        // Show all
    } else {
        // Check institution 
    }

$params = array();


if($isadmin) {
	$params['hod'] = $hod;
} else {
	$params['hod'] = $uid;
}
$params['showteachers'] = $showteachers;
$params['timeframe'] = $timefrom;
if(!$downloadtype) {
	$params['type'] = 'csv';
} else {
	$params['type'] = $downloadtype;
}

$hods = get_hods();
$timeoptions = get_time();
$outputtypes = array('xls'=>'*.xls file', 'csv'=>'*.csv file');

$data = array();
$data = get_data($params);
$results = display_data($data, $params);

echo $OUTPUT->header();

// Options Form
echo '<div id="options"><form class="settingsform" action="'.$CFG->wwwroot.'/report/departmentalusage/index.php" method="get">';

if  ($isadmin) {
echo '<label for="hod">'.get_string('filter', 'report_departmentalusage').'</label>'."\n";
echo html_writer::select($hods, "hod", $hod).' | ';
}

echo '<label for="timefrom">'.get_string('loginlength', 'report_departmentalusage').'</label>'."\n";
echo html_writer::select($timeoptions,'timefrom',$timefrom);

echo '  |  <label for="showteachers">'.get_string('showteachers', 'report_departmentalusage').'</label> '."\n";
echo html_writer::checkbox('showteachers', true, false);

echo '<span style="float:right; margin-top:-0.5em; "><input class="btn btn-success btn-xs" type="submit" value="Run Report" /></span></form></div>';

echo '<hr style="clear:both" />';

// Data Table
//$results = get_data($params);

echo $results;
echo '<p><em>Click on the course title for a more indepth report on that course.</em></p>';

echo '<hr />';
echo '<div id="downloadoptions"><form class="settingsform" action="'.$CFG->wwwroot.'/report/departmentalusage/download.php " method="get">';

echo '<input type="hidden" name="format" value="csv">';
echo '<input type="hidden" name="hod" value="'.$params['hod'].'">';

echo '<input type="hidden" name="date" value="'.$params['timeframe'].'">';
echo '<input type="hidden" name="showteachers" value="'.$params['showteachers'].'">';

echo ' <input type="submit" class="btn btn-xs btn-primary" value="Download Report" /></form></div>';

echo $OUTPUT->footer();