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
* Course list filter settings
*
* @package    filter_courselist 
* @copyright  2023 think-modular
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $default = '<ul class="list-group">{{ coursecards }}</ul>';

    $settings->add(new admin_setting_configtextarea(
        'filter_courselist/alttemplate_container',
        get_string('setting_alttemplate_container', 'filter_courselist'),
        get_string('setting_alttemplate_container_desc', 'filter_courselist'),
        $default));

    $default = '<li class="list-group-item course-listitem border-left-0 border-right-0 border-top-0 px-2 rounded-0" data-region="course-content" data-course-id="{{ id }}">
            <div class="row">
                <div class="col-md-2 d-flex align-items-center mb-sm-3 mb-md-0">
                    <a href="/course/view.php?id={{ id }}" tabindex="-1" class="mw-100 w-100">
                        <div class="card-img dashboard-list-img mw-100" style="background-image: url({{ courseimage }});">
                            <span class="sr-only">{{#str}}aria:courseimage, core_course{{/str}}</span>
                        </div>
                    </a>
                </div>
                <div class="col-md-9 d-flex flex-column">
                    <a href="https://moodle.develop-modular.com/moodle-4.1/course/view.php?id=2" class="aalink coursename">                
                        <span class="sr-only">
                            {{#str}}aria:coursename, core_course{{/str}}
                        </span>
                        {{ fullname }}
                    </a>
                        <div class="text-muted muted d-flex flex-wrap">
                            <span class="sr-only">
                                {{#str}}coursecategory, core_course{{/str}}
                            </span>
                            <span class="categoryname">
                                {{ coursecategory }}
                            </span>
                        </div>                
                </div>
            </div>
        </li>
    ';

    $settings->add(new admin_setting_configtextarea(
        'filter_courselist/alttemplate',
        get_string('setting_alttemplate', 'filter_courselist'),
        get_string('setting_alttemplate_desc', 'filter_courselist'),
        $default));
      
    
}
    
    
    