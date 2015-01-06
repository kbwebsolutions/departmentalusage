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
 * Has a number of functions for the report
 *
 * @package report_departmentalusage
 * @copyright 2013 Kieran Briggs - The Sheffield College
 * @email: kieran.briggs@sheffcol.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Given a course id number, this function
 * gets returns a list of teachers on that course
 */
function get_teachers($cid){
	global $CFG, $DB;
	
	$teacherssql = 'SELECT DISTINCT usr.id AS uid, CONCAT_WS(" ",firstname, lastname) AS teacher FROM mdl_user AS usr
					JOIN mdl_role_assignments AS asg ON asg.userid = usr.id
					JOIN mdl_context AS cont ON asg.contextid = cont.id AND cont.contextlevel = 50
					JOIN mdl_course AS crs ON cont.instanceid = crs.id
					WHERE crs.id = '.$cid .' AND (asg.roleid = 3 OR asg.roleid = 4)';
	
	$teachers = $DB->get_records_sql($teacherssql);
	
	
	if($teachers) {
		$content = '';
		foreach($teachers as $t) {
			$content .= '<a href="'.$CFG->wwwroot.'/user/profile.php?id='.$t->uid.'">'.$t->teacher.'</a>, ';
		}
	} else {
		$content = 'No Teachers';
	}
	
	return $content;
}

/**
 * This report gets a list of all the users with
 * the role which was set in the settings page
 */
function get_hods() {
	global $CFG, $DB;
	
	// Get the ID of the role used in the report.
	$sql = "SELECT DISTINCT usr.id, CONCAT_WS(' ',firstname,lastname) AS hod, department AS dept
			FROM {user} AS usr
			JOIN {role_assignments} AS ra ON ra.userid = usr.id
			WHERE ra.roleid = :managerrole";
	
	$params['managerrole'] = get_config('report_departmentalusage', 'managerroleid');
	$hods = $DB->get_records_sql($sql, $params);
	
	foreach($hods as $h) {
		if ($h->dept) {
			$content[$h->id] = $h->hod.' ('.$h->dept.')';
		} else {
			$content[$h->id] = $h->hod;
		}
	}
	return $content;
}

/** 
 * This function sets the time to go back through
 * the logs for login details 
 */
function get_time() {
	global $CFG, $DB;
	
	$timeoptions = array();
	// get minimum log time for this course
	$minlog = $DB->get_field_sql('SELECT min(time) FROM {log}');//S WHERE course = ?', array($course->id));
	
	$now = usergetmidnight(time());
	
	// days
	for ($i = 1; $i < 7; $i++) {
	    if (strtotime('-'.$i.' days',$now) >= $minlog) {
	        $timeoptions[strtotime('-'.$i.' days',$now)] = get_string('numdays','moodle',$i);
	    }
	}
	// weeks
	for ($i = 1; $i < 10; $i++) {
	    if (strtotime('-'.$i.' weeks',$now) >= $minlog) {
	        $timeoptions[strtotime('-'.$i.' weeks',$now)] = get_string('numweeks','moodle',$i);
	    }
	}
	// months
	for ($i = 2; $i < 12; $i++) {
	    if (strtotime('-'.$i.' months',$now) >= $minlog) {
	        $timeoptions[strtotime('-'.$i.' months',$now)] = get_string('nummonths','moodle',$i);
	    }
	}
	// try a year
	if (strtotime('-1 year',$now) >= $minlog) {
	    $timeoptions[strtotime('-1 year',$now)] = get_string('lastyear');
	}
	
	return $timeoptions;
}

/** 
 * Gets a list of the categories that the manager has access to
 *
 */
 
function get_dept_cats($managerrole, $userid) {
    global $CFG, $DB;
    $cats = "SELECT cc.id AS cid, cc.name
             FROM mdl_course_categories AS cc
                INNER JOIN mdl_context AS cx on cc.id = cx.instanceid
                AND cx.contextlevel = 40
                    INNER JOIN mdl_role_assignments AS ra ON cx.id = ra.contextid
                    INNER JOIN mdl_user AS usr ON ra.userid = usr.id
                    WHERE userid = :usr AND roleid = :managerrole";
    
    $params['usr'] = $userid;
    $params['managerrole'] = $managerrole;
    
    $content = $DB->get_records_sql($cats, $params);
    
    return $content;
    
}
/**
 * This is the main function which creates the table
 * in the report
 * @$params - Array including the userid to search on and time for logins
 */
function get_data($params) {
	
	global $CFG, $DB, $USER;
    $courses = get_report_data($params);
    
	$table = new html_table();
	if($params['showteachers'] == 1) {
		$table->align = array('left', 'center','center', 'center', 'center', 'center', 'center', 'center', 'center');
        $table->head  = array(get_string('course', 'report_departmentalusage'), 
                              get_string('teachers', 'report_departmentalusage'), 
                              get_string('created', 'report_departmentalusage'), 
                              get_string('enrolled', 'report_departmentalusage'), 
                              get_string('logins', 'report_departmentalusage'), 
                              get_string('lastlogin', 'report_departmentalusage'), 
                              get_string('update', 'report_departmentalusage'), 
                              get_string('activities', 'report_departmentalusage'), 
                              get_string('resources', 'report_departmentalusage')
                             );

	} else {
	    $table->align = array('left', 'center', 'center', 'center', 'center', 'center', 'center', 'center');
        $table->head  = array(get_string('course', 'report_departmentalusage'), 
                              get_string('created', 'report_departmentalusage'), 
                              get_string('enrolled', 'report_departmentalusage'), 
                              get_string('logins', 'report_departmentalusage'), 
                              get_string('lastlogin', 'report_departmentalusage'), 
                              get_string('update', 'report_departmentalusage'), 
                              get_string('activities', 'report_departmentalusage'), 
                              get_string('resources', 'report_departmentalusage')
                             );
	}
	
	$coursedata = array();
	foreach ($courses as $c) {
		$teach = get_teachers($c->cid);
		
		$repdata['cid'] = $c->cid;
		$repdata['course'] = $c->course;
		$repdata['teachers'] = $teach;
		$repdata['createddate'] = $c->created;
		
		// Gets the students based on context level 50 - course and course id from $c
		$studentssql = 'SELECT count(asg.id) AS students 
                        FROM mdl_role_assignments as asg 
                        JOIN mdl_context AS context ON asg.contextid = context.id AND context.contextlevel = 50 
                        JOIN mdl_user AS usr on usr.id = asg.userid JOIN mdl_course AS course ON context.instanceid = course.id 
                        WHERE asg.roleid = 5 AND course.id ='.$c->cid;
		$students = $DB->get_record_sql($studentssql);
		$repdata['students'] = $students->students;
		
		// Gets a count of resources in the mdl_resource table with course = $c
		$resourcesql = 'SELECT count(id) AS res 
		                FROM mdl_resource 
		                WHERE course = '. $c->cid;
		$resource = $DB->get_record_sql($resourcesql);
		$repdata['resources'] = $resource->res;
		
		// Gets a count of activites in the mdl_course_modules table with course = $c
		// TODO Find out why SCORM has a problem
		$modulesql   = 'SELECT count(*) AS mods 
		                FROM mdl_course_modules AS cm 
		                WHERE cm.course ='. $c->cid;
		$module = $DB->get_record_sql($modulesql);
		$repdata['los'] = $module->mods;
		
		// Get total number of logins to a course
		$totallogins = 'SELECT count(id) AS logins
		                FROM {logstore_standard_log}
		                WHERE timecreated > :timeframe AND courseid = '. $c->cid . ' AND action = "viewed" AND edulevel = 2';
        $parameters['timeframe'] = $params['timeframe'];
        $logins = $DB->get_record_sql($totallogins, $parameters);
        $repdata['logins'] = $logins->logins;
        
        // Get the last time a user logged into a specific course
		$viewsql     = 'SELECT DISTINCT MAX(timecreated) AS lastlogin 
		                FROM {logstore_standard_log} 
		                WHERE courseid = '. $c->cid . ' AND action = "viewed" AND edulevel = 2';
		$view = $DB->get_record_sql($viewsql);
		$repdata['lastlogin'] = $view->lastlogin;
		
		// Get the last time an add or update action was taken on a course = $c
		$updatesql   = 'SELECT MAX(timecreated) AS Updated 
		                FROM {logstore_standard_log}
                        WHERE (action LIKE "created" OR action = "updated" OR action = "deleted") AND courseid = '.$c->cid . ' AND target = "course_module"';
		$update = $DB->get_record_sql($updatesql);
		$repdata['updated'] = $update->updated;
		
		$coursedata[$c->cid] = $repdata;
		
	}
	
	return $coursedata;
}

function display_data($data, $params) {
	
	global $CFG;
	
	
    $table = new html_table();
	if($params['showteachers'] == 1) {
		$table->align = array('left', 'center','center', 'center', 'center', 'center', 'center', 'center', 'center');
        $table->head  = array(get_string('course', 'report_departmentalusage'), 
                              get_string('teachers', 'report_departmentalusage'), 
                              get_string('created', 'report_departmentalusage'), 
                              get_string('enrolled', 'report_departmentalusage'), 
                              get_string('logins', 'report_departmentalusage'), 
                              get_string('lastlogin', 'report_departmentalusage'), 
                              get_string('update', 'report_departmentalusage'), 
                              get_string('activities', 'report_departmentalusage'), 
                              get_string('resources', 'report_departmentalusage')
                             );

	} else {
	    $table->align = array('left', 'center', 'center', 'center', 'center', 'center', 'center', 'center');
        $table->head  = array(get_string('course', 'report_departmentalusage'), 
                              get_string('created', 'report_departmentalusage'), 
                              get_string('enrolled', 'report_departmentalusage'), 
                              get_string('logins', 'report_departmentalusage'), 
                              get_string('lastlogin', 'report_departmentalusage'), 
                              get_string('update', 'report_departmentalusage'), 
                              get_string('activities', 'report_departmentalusage'), 
                              get_string('resources', 'report_departmentalusage')
                             );
	}
	
	
    foreach($data as $c) {
        print_object($c);
    	// This section creates a row for the table
		$row = array();
		$row[] = '<a href="'.$CFG->wwwroot.'/report/outline/index.php?id='.$c['cid'].'">'.$c['course'].'</a>';
		if ($params['showteachers'] == 1) {
			$row[] = $c['teachers'];
		}
		$row[] = userdate($c['createddate'], get_string('strftimedatefullshort', 'langconfig'));
		$row[] = '<a href="'.$CFG->wwwroot.'/user/index.php?id='.$c['cid'].'">'.$c['students'].'</a>';
		//$row[] = '<a href="'.$CFG->wwwroot.'/user/index.php?id='.$c->cid.'">'.$c->students.'</a>';
		$row[] = $c['logins'];	
		$row[] = format_time(time() - $c['lastlogin']);
		if (!$c['updated']) {
			$row[] = "Unknown";
		} else {
			$row[] = format_time(time() - $c['updated']);
		}	
		$row[] = $c['los'] - $c['resources'];
		$row[] = $c['resources'];	
		
		$table->data[] = $row;
		
	}
	
	$content = html_writer::table($table);
	
	return $content;
}


function get_report_data($params) {
    global $CFG, $DB; 
    
    $config = get_config('report_departmentalusage');
	
	$cats = get_dept_cats($config->managerroleid, $params['hod']);
    $catno = implode(", ", array_keys($cats));
    	
	$courselistsql = "SELECT c.id AS cid, c.fullname AS course, c.timecreated AS Created, c.visible AS visible
                      FROM {course} c
                      LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)
                      WHERE c.category IN ( :catid ) and c.id <> :sid";
                        
	$sqlparams['contextlevel'] = CONTEXT_COURSE;
	$sqlparams['sid'] = SITEID;
	$sqlparams['catid'] = $catno;

	$courses = $DB->get_records_sql($courselistsql, $sqlparams);
	
    return $courses;
}
/**
 * This function gets the data but ready for downloading
 * into a file rather than displaying
 * @$params - Array including the userid to searh on and time for logins
 */
function get_download_data($params) {
		global $CFG, $DB;
	
	$courses = get_report_data($params);
	
	$rows = array();
	$i = 1;
		
	foreach ($courses as $c) {
		$row = array();
		$usersql     = 'SELECT CONCAT_WS(" ", firstname, lastname) AS depthead 
		                FROM mdl_user 
		                WHERE mdl_user.id ='.$params['hod'];
		$user = $DB->get_record_sql($usersql);
		$row["depthead"] = $user->depthead;
		
		$teach = get_teachers($c->cid);
		
		// Gets the students based on context level 50 - course and course id from $c
		$studentssql = 'SELECT count(asg.id) AS students 
                        FROM mdl_role_assignments as asg 
                        JOIN mdl_context AS context ON asg.contextid = context.id AND context.contextlevel = 50 
                        JOIN mdl_user AS usr on usr.id = asg.userid JOIN mdl_course AS course ON context.instanceid = course.id 
                        WHERE asg.roleid = 5 AND course.id ='.$c->cid;
		$students = $DB->get_record_sql($studentssql);
		
		// Gets a count of resources in the mdl_resource table with course = $c
		$resourcesql = 'SELECT count(id) AS res 
		                FROM mdl_resource 
		                WHERE course = '. $c->cid;
		$resource = $DB->get_record_sql($resourcesql);
		
		// Gets a count of activites in the mdl_course_modules table with course = $c
		// TODO Find out why SCORM has a problem
		$modulesql   = 'SELECT count(*) AS mods 
		                FROM mdl_course_modules AS cm 
		                WHERE cm.course ='. $c->cid;
		$module = $DB->get_record_sql($modulesql);
		
		// Get total number of logins to a course
		$totallogins = 'SELECT count(id) AS logins
		                FROM {logstore_standard_log}
		                WHERE timecreated > :timeframe AND courseid = '. $c->cid . ' AND action = "viewed" AND edulevel = 2';
        $parameters['timeframe'] = $params['timeframe'];
        $logins = $DB->get_record_sql($totallogins, $parameters);
        
        // Get the last time a user logged into a specific course
		$viewsql     = 'SELECT DISTINCT MAX(timecreated) AS lastlogin 
		                FROM {logstore_standard_log} 
		                WHERE courseid = '. $c->cid . ' AND action = "viewed" AND edulevel = 2';
		$view = $DB->get_record_sql($viewsql);
		
		// Get the last time an add or update action was taken on a course = $c
		$updatesql   = 'SELECT MAX(timecreated) AS Updated 
		                FROM {logstore_standard_log}
                        WHERE (action LIKE "created" OR action = "updated" OR action = "deleted") AND courseid = '.$c->cid . ' AND target = "course_module"';
		$update = $DB->get_record_sql($updatesql);
		
		$row['coursename'] = $c->course;
		if ($params['showteachers'] == 1) {
			$row['editingteachers'] = rtrim(strip_tags($teach), ",");
		}
		$row['createdon'] = userdate($c->created, get_string('strftimedatefullshort', 'langconfig'));
		$row['enrolledstudents'] = $students->students;
		$row['logins'] = $logins->logins;	
		$row['lastlogin'] = userdate($view->lastlogin, get_string('strftimedatefullshort', 'langconfig'));
		if (!$update->updated) {
    		$row['lastupdate'] = "Unknown";
		} else {
		    $row['lastupdate'] = userdate($update->updated, get_string('strftimedatefullshort', 'langconfig'));	
        }
		$row['activites'] = $module->mods - $resource->res;
		$row['resources'] = $resource->res;	
			

		$rows[$i] = $row;
		$i++;
	}
		
		return $rows;

	
} 
 