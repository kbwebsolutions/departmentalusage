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
 * Creates the setting page for the report so an admin can set the role type for the report to run against.
 *
 * @package report_departmentalusage
 * @copyright 2013 Kieran Briggs - The Sheffield College
 * @email: kieran.briggs@sheffcol.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;



// Add a link to the reports admin settings menu.
$ADMIN->add('reports', new admin_externalpage('reportdepartments', get_string('pluginname', 'report_departmentalusage'), "$CFG->wwwroot/report/departmentalusage/index.php", 'moodle/site:viewreports'));


global $DB;
	
if (get_config('report_departmentalusage', 'managerroleid')) {
    $defaultrole = get_config('report_departmentalusage', 'managerroleid');
} else {
    $defaultrole = 4;
};

$roles = $DB->get_records_menu('role',$conditions,'shortname','id, shortname');

$settings->add(new admin_setting_configselect('report_departmentalusage/managerroleid',
                    get_string('chooseroleid', 'report_departmentalusage'),
                    get_string('descchooseroleid', 'report_departmentalusage'),
                    $defaultrole,
                    $roles)
                );    


   //}
