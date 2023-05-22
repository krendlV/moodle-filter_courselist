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
 * Strings for component 'filter_courselist'
 *
 * @package   filter_courselist
 * @copyright 2023 think-modular
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['filtername'] = 'Course List';
$string['errormsg'] = 'Malformed courselist filter found - ignoring all courselist elements in this text.';
$string['showall'] = 'Show all courses';
$string['privacy:metadata'] = 'The Course List filter plugin does not store any personal data.';
$string['setting_alttemplate_container'] = 'Alternative template container';
$string['setting_alttemplate_container_desc'] = '<p>If you want to use an alternative Mustache template instead of a the course cards, you can specify its container here. 
Use the placeholder "{{ coursecards }}" to mark where the actual Mustache for the individual courses, specified below, will be inserted. 
The default is similar to the list view of the myoverview block.</p>
<p>Use <strong>alttemplate</strong> to invoke this template in the filter.</p>';
$string['setting_alttemplate'] = 'Alternative template';
$string['setting_alttemplate_desc'] = '<p>Specify the Mustache code for the template to use for each course here. 
In addition to field names from the course object, you can use the following placeholders: <ul>
<li>{{ courseimage }}</li>
<li>{{ coursecategory }}</li>
<li>{{ courseprogress }}</li>
</ul>
The default is similar to the list view of the myoverview block.</p>
<p>Use <strong>alttemplate</strong> to invoke this template in the filter.</p>';