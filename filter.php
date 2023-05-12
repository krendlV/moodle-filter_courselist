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
        global $CFG, $PAGE;        
        
        if (empty($text) or is_numeric($text)) {
            return $text;
        }                        

        if (strpos($text, self::TOKEN) !== false) {                   
            return $this->apply($text);
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
    protected function apply($text) {        

        // Split text into parts, keeping delimiter.
        $regex = '@(?=' . self::TOKEN . ')@';
        $parts = preg_split($regex, $text);        
        
        foreach ($parts as $key => $part) {                        
            
            if (strpos($part, self::TOKEN) === 0) {                   
                
                $atoms = explode(' }}', $part);                
                if (count($atoms) == 2) {                    
                    $atoms[0] = $this->get_courses($atoms[0]);
                    $parts[$key] = implode($atoms);
                } else {
                    return $this->return_error($text);
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
    protected function get_courses($text) {  
        global $PAGE;
        
        $coursecards = "";
        $courserenderer = $PAGE->get_renderer('core', 'course');
        $fields = ['*'];

        // Filter param "search": Include search form.
        if (strpos($text, 'search')) {     
            $coursecards = $this->searchbox();
        }
        

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
        if (!array_key_exists('courselist_showall', $_GET) && (strpos($text, 'number='))) {            
            $number = explode('number=', $text)[1];
            $number = intval(explode(' ', $number)[0]);
            if (count($courses) > $number) {
                $courses = array_slice($courses, 0, $number);
                $showallbutton = true;                    
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
                                unset($courses[$key]);
                            }
                        } else if ($operator == ">" || $operator == "&gt;") {
                            if ($course->$property < $value) {                                
                                unset($courses[$key]);
                            }
                        } else if ($operator == "<" || $operator == "&lt;") {
                            if ($course->$property > $value) {                                
                                unset($courses[$key]);
                            }
                        }
                    }
                }                
            }            
        }

        // Filter for searchbox entry.
        if (array_key_exists('courselist_search', $_GET)) {            
            if ($searchterm = $_GET['courselist_search']) {
                $search_properties = ['shortname', 'fullname', 'summary'];
                
                foreach ($courses as $key => $course) {
                    $found = false;
                    foreach ($search_properties as $property) {
                        if (stripos($course->$property, $searchterm) !== false) {  
                            $found = true;
                        } 
                    }
                    if (!$found) {
                        unset($courses[$key]);
                    }
                }                
            }
        }

        // Filter param "reverse": reverses sort order.
        if (strpos($text, 'reverse')) {   
            $courses = array_reverse($courses, true);
        }        
        
        // Render coursecards.
        $coursecards .= $courserenderer->courses_list($courses);

        // Filter param "showall": display button to show all courses.        
        if (!array_key_exists('courselist_showall', $_GET) && strpos($text, 'showall')
                && $showallbutton) {
            $url = "$_SERVER[REQUEST_URI]";
            $url .= (count($_GET) > 0 ? '&' : '?');
            $url .= 'courselist_showall=1';
            $coursecards .= '<a class="btn btn-primary" href="' . $url . '">'
                . get_string('showall', 'filter_courselist') . '</a>';
        } 

        return $coursecards;
        
    }


    /**
     * Renders the searchbox.     
     *      
     * @return string
     */
    protected function searchbox() {
        global $CFG;
        
        // Get mustache template.
        $templatePath = $CFG->dirroot . '/filter/courselist/templates/searchbox.mustache';
        $template = file_get_contents($templatePath);

        // Get parameters.
        $placeholder = get_string('searchcourses');
        $searchvalue = (array_key_exists('courselist_search', $_GET)) ? $_GET['courselist_search'] : null;

        // Render searchbox.
        $data = array('placeholder' => $placeholder, 'searchvalue' => $searchvalue);
        $mustache = new Mustache_Engine();
        return $mustache->render($template, $data);

    }


    /**
     * Returns original text plus error message.
     * 
     * @param string $text 
     * @return string
     */
    protected function return_error($text) {
        $errormsg = get_string('errormsg', 'filter_courselist');
        return '<div class="alert alert-danger">' . $errormsg . '</div>' . $text;
    }
     
}