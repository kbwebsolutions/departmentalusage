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
 * script for downloading of user lists
 *
 * @package report_departmentalusage
 * @copyright 2013 Kieran Briggs - The Sheffield College
 * @email: kieran.briggs@sheffcol.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/report/departmentalusage/lib.php');

require_login();
require_capability('moodle/user:update', context_system::instance());

$return = $CFG->wwwroot.'/'.$CFG->dirroot.'/report/departmentalusage.php';

$format 		= optional_param('format', '', PARAM_ALPHA);
$hod			= optional_param('hod', 0, PARAM_INT); // Hod id number
$timefrom   	= optional_param('date', 0, PARAM_INT); // how far back to look...
$showteachers   = optional_param('showteachers', 0, PARAM_INT); // Show teachers in results or not

$params = array();

$params['hod'] = $hod;
$params['showteachers'] = $showteachers;
$params['timeframe'] = $timefrom;



if ($format) {
    if ($showteachers == 1) {
    	$fields = array(
    				'depthead'			=> 'Department Head',
    				'coursename'   		=> 'Course Name',
                    'editingteachers'	=> 'Editing Teachers',
                    'createdon'  		=> 'Created On',
                    'enrolledstudents'	=> 'Enrolled Students',
                    'logins' 			=> 'Logins',
                    'lastlogin'  		=> 'Last Login',
                    'lastupdate'  		=> 'Last Update',
                    'activites' 		=> 'Learning Objects',
                    'resources' 		=> 'Resources');
                    
    } else {
		$fields = array(
					'depthead'			=> 'Department Head',
    				'coursename'    	=> 'Course Name',
                    'createdon'  		=> 'Created On',
                    'enrolledstudents'	=> 'Enrolled Students',
                    'logins' 			=> 'Logins',
                    'lastlogin'  		=> 'Last Login',
                    'lastupdate'  		=> 'Last Update',
                    'activites' 		=> 'Learning Objects',
                    'resources' 		=> 'Resources');
                    

    }

    switch ($format) {
        case 'csv' : user_download_csv($fields, $params);
        //case 'ods' : user_download_ods($fields, $params);
        //case 'xls' : user_download_xls($fields, $params);

    }
    die;
}

/* These functions are still being worked on

	function user_download_ods($fields) {
	    global $CFG, $SESSION, $DB;
	
	    require_once("$CFG->libdir/odslib.class.php");
	    require_once($CFG->dirroot.'/user/profile/lib.php');
	
	    $filename = clean_filename(get_string('users').'.ods');
	
	    $workbook = new MoodleODSWorkbook('-');
	    $workbook->send($filename);
	
	    $worksheet = array();
	
	    $worksheet[0] = $workbook->add_worksheet('');
	    $col = 0;
	    foreach ($fields as $fieldname) {
	        $worksheet[0]->write(0, $col, $fieldname);
	        $col++;
	    }
	
	    $row = 1;
	    foreach ($SESSION->bulk_users as $userid) {
	        if (!$user = $DB->get_record('user', array('id'=>$userid))) {
	            continue;
	        }
	        $col = 0;
	        profile_load_data($user);
	        foreach ($fields as $field=>$unused) {
	            $worksheet[0]->write($row, $col, $user->$field);
	            $col++;
	        }
	        $row++;
	    }
	
	    $workbook->close();
	    die;
	}
	
	function user_download_xls($fields, $params) {
	    global $CFG, $DB;
	
	    require_once("$CFG->libdir/excellib.class.php");
	    
	    $usersql = 'SELECT CONCAT_WS(" ", firstname, lastname) AS depthead FROM mdl_user WHERE mdl_user.id ='.$params['hod'];
		$user = $DB->get_record_sql($usersql);
		
		$filename = $user->depthead.'-'.get_string('reportname', 'report_departmentalusage').'.xls';
	    
		$workbook = new MoodleExcelWorkbook('-');
	    $workbook->send($filename);
	
	    $worksheet = array();
	
	    $worksheet[0] = $workbook->add_worksheet('');
	    $col = 0;
	    
	    //$data = get_download_data($params);
	    $data = array('coursename'=>'Test Course A', 'resources'=>'3');
	    foreach ($fields as $fieldname) {
	        $worksheet[0]->write(0, $col, $fieldname);
	        $col++;
	    }
	
	    $row = 1;
	    foreach ($data as $rows) {
	        $col = 0;
	        //foreach ($rows as $r) {
	          $worksheet[0]->write($row, $col, $rows->coursename);
	            $col++;
	            $worksheet[0]->write($row, $col, $rows->r);
	        //}
	        $row++;
	    }
	
	    $workbook->close();
	    die;
	}
*/

function user_download_csv($fields, $params) {
    global $CFG, $DB;
    require_once($CFG->libdir . '/csvlib.class.php');
    $data = get_download_data($params);
    
    $usersql = 'SELECT CONCAT_WS(" ", firstname, lastname) AS depthead FROM mdl_user WHERE mdl_user.id ='.$params['hod'];
	$user = $DB->get_record_sql($usersql);
	
	$filename = $user->depthead.'-'.get_string('reportname', 'report_departmentalusage');
    $csvexport = new csv_export_writer();
    $csvexport->set_filename($filename);
    $csvexport->add_data($fields);
    foreach($data as $d) {
	    $csvexport->add_data($d);
    }
    
    $csvexport->download_file();
    die;
}
