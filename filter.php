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
 * @package    filter_courselist
 * @copyright  2023 think-modular 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use core_course\customfield\course_handler;

require_once($CFG->dirroot . '/course/renderer.php');

// Returns course cards for all courses that meet the search criteria.

/**
 * Implementation of the Moodle filter API for the Courselist filter.
 * 
 * @copyright  2023 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class filter_courselist extends moodle_text_filter {

    const TOKEN = '{{ courselist ';    

    function filter($text, array $options = array()) {
        global $CFG;        
        
        if (empty($text) or is_numeric($text)) {
            return $text;
        }                        

        if (strpos($text, self::TOKEN) !== false) {                   
            return $this->filter_courselist_apply($text);
        } else {
            return $text;
        }        
    }


    /**
     * Does the actual filtering.
     *
     * @param string $text
     * @return string
     */
    protected function filter_courselist_apply($text) {        

        // Split text into parts, keeping delimiter.
        $regex = '@(?=' . self::TOKEN . ')@';
        $parts = preg_split($regex, $text);        
        
        foreach ($parts as $key => $part) {                        
            
            if (strpos($part, self::TOKEN) === 0) {                   
                
                $atoms = explode(' }}', $part);                
                if (count($atoms) == 2) {                    
                    $atoms[0] = $this->filter_courselist_get_courses($atoms[0]);
                    $parts[$key] = implode($atoms);
                } else {
                    return $this->filter_courselist_return_error($text);
                }
            }     
        }    

        return implode($parts);
        
    }


    /**
     * Returns a list of courses according to filter params.
     * 
     * @param string $text 
     * @return string
     */
    protected function filter_courselist_get_courses($text) {  
        global $PAGE;
        
        $courserenderer = $PAGE->get_renderer('core', 'course');
        $fields = ['*'];

        // Filter param "sort": Get sort criteria.
        if (strpos($text, 'sort=')) {                 
            $sort = explode('sort=', $text)[1];
            $sort = explode(' ', $sort)[0];                    
        } else {
            $sort = null;
        }

        // Filter param "courseids": Get courseids.
        if (strpos($text, 'courseids=[')) {                 
            $courseids = explode('courseids=[', $text)[1];
            $courseids = explode(']', $courseids)[0];
            $courseids = explode(',', $courseids);                        
        } else {
            $courseids = array();
        }

        // Filter param "enrolled": Get all courses, or only enrolled / not enrolled.
        if (strpos($text, 'enrolled=true')) {     
            $courses = enrol_get_my_courses($fields, $sort);
        } else if (strpos($text, 'enrolled=false')) {     
            $courses = enrol_get_my_courses($fields, $sort, 0, $courseids, true);
            $enrolled_courses = enrol_get_my_courses();
            foreach ($courses as $key => $value) {
                if (in_array($key, array_keys($enrolled_courses))) {
                    unset($courses[$key]);
                }
            }
        } else {
            $courses = enrol_get_my_courses($fields, $sort, 0, $courseids, true);
        }

        // Filter param "categoryids": Only courses from selected categories.
        if (strpos($text, 'categoryids=[')) {   
            $categoryids = explode('categoryids=[', $text)[1];
            $categoryids = explode(']', $categoryids)[0];
            $categoryids = explode(',', $categoryids);
            foreach ($courses as $key => $course) {
                if (!in_array($course->category, $categoryids)) {
                    unset($courses[$key]);
                }            
            }
        }

        // Add customfields to courses.        
        foreach ($courses as $key => $course) {         
            $handler = course_handler::create($course->id);               
            $customfields = $handler->export_instance_data($course->id);
            foreach ($customfields as $customfield) {
                $fieldname = $customfield->get_shortname();                
                $course->$fieldname = $customfield->get_data_controller()->get_value();                                                
            }                    
        }
        
        // Filter param "number": limit number of displayed courses.
        if (!array_key_exists('courselist_showmore', $_GET) && (strpos($text, 'number='))) {            
            $number = explode('number=', $text)[1];
            $number = intval(explode(' ', $number)[0]);
            if (count($courses) > $number) {
                $courses = array_slice($courses, 0, $number);
                $showmorebutton = true;                    
            }            
        }

        // Filter param "filter": custom filters.
        if (strpos($text, 'filters=[')) {                 
            $filters = explode('filters=[', $text)[1];
            $filters = explode(']', $filters)[0];
            $filters = explode(',', $filters);          
            foreach ($filters as $filter) {    
                
                // Parse filter into property, operator and value.
                $filter = trim($filter);                                                
                $parts = preg_split('/([><=]|&lt;|&gt;)/', $filter, -1, PREG_SPLIT_DELIM_CAPTURE);                                
                $property = $parts[0];
                $operator = $parts[1];
                $value = $parts[2];

                // Replace NOW value.
                if ($value == "NOW") {
                    $value = time();
                }

                // Test all our custom filters.
                foreach ($courses as $key => $course) {
                    if (property_exists($course, $property)) {

                        // Test all 3 possible operators.
                        if ($operator == "=") {
                            if ($course->$property != $value) {
                                echo $property . $operator . $value;
                                unset($courses[$key]);
                            }
                        } else if ($operator == ">" || $operator == "&gt;") {
                            if ($course->$property < $value) {
                                echo $property . $operator . $value;
                                unset($courses[$key]);
                            }
                        } else if ($operator == "<" || $operator == "&lt;") {
                            if ($course->$property > $value) {
                                echo $property . $operator . $value;
                                unset($courses[$key]);
                            }
                        }
                    }
                }                
            }            
        }

        // Filter param "reverse": reverses sort order.
        if (strpos($text, 'reverse')) {   
            $courses = array_reverse($courses, true);
        }
        
        // Render coursecards.
        $coursecards = $courserenderer->courses_list($courses);

        // Filter param "showall": display button to show all courses.        
        if (!array_key_exists('courselist_showmore', $_GET) && strpos($text, 'showmore')
                && $showmorebutton) {
            $url = "$_SERVER[REQUEST_URI]";
            $url .= (count($_GET) > 0 ? '&' : '?');
            $url .= 'courselist_showmore=1';
            $coursecards .= '<a class="btn btn-primary" href="' . $url . '">'
                . get_string('showmore', 'filter_courselist') . '</a>';
        } 

        return $coursecards;
        
    }


    /**
     * Returns original text plus error message.
     * 
     * @param string $text 
     * @return string
     */
    protected function filter_courselist_return_error($text) {
        $errormsg = get_string('errormsg', 'filter_courselist');
        return '<div class="alert alert-danger">' . $errormsg . '</div>' . $text;
    }
     
}