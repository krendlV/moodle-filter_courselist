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
     * Returns a list of all courses depending on categoryid and courseid.
     * 
     * @param array $courseids
     * @param array $categoryids
     * @param string $fields
     * @param string $sort
     * @return array $courses
     * 
     */
    protected function get_all_courses($courseids, $categoryids, $fields, $sort) {

        global $DB;
        
        // Get by courseid.
        if ($courseids) {            
            $courses = $DB->get_records_list('course', 'id', $courseids, $sort, $fields);

            // Filter by category IDs afterwards if necessary.
            if ($categoryids) {
                foreach ($courses as $key => $course) {
                    if (!in_array($course->category, $categoryids)) {
                        unset($courses[$key]);
                    }            
                }
            }

            // Revert to sort specified in input if sort is empty.
            if (!$sort) {
                $unsorted_courses = $courses;
                $courses = array();
                foreach ($courseids as $courseid) {
                    if (array_key_exists($courseid, $unsorted_courses)) {                    
                        $courses[$courseid] = $unsorted_courses[$courseid];
                    }
                }
            }

            // Remove courseid 1 (front page)
            unset($courses[1]);

            return $courses;

        // Get by categoryid.
        } elseif ($categoryids) {
            return $DB->get_records_list('course', 'category', $categoryids, $sort, $fields);
        
        // Get all.
        } else {
            $courses = $DB->get_records('course', null, $sort, $fields);
            
            // Remove courseid 1 (front page)
            unset($courses[1]);

            return $courses;
        }        
    }


    /**
     * Returns a list of courses according to filter params.
     * 
     * @param string $text 
     * @return string
     */
    protected function get_courses($text) {  
        global $PAGE;
        
        $output = "";        
        $fields = 'id,category,shortname,fullname,idnumber,startdate,enddate,visible,groupmode';
        $valid_fields = explode(',', $fields);

        // Filter param "search": Include search form.
        if (strpos($text, 'search')) {     
            $output = $this->searchbox();
        }        

        // Filter param "sort": Get sort criteria.
        if (strpos($text, 'sort=')) {                 
            $sort = explode('sort=', $text)[1];
            $sort = explode(' ', $sort)[0];                    
        } else {
            $sort = null;
        }
        if (!in_array($sort, $valid_fields)) {
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

        // Filter param "categoryids": Only courses from selected categories.
        if (strpos($text, 'categoryids=[')) {   
            $categoryids = explode('categoryids=[', $text)[1];
            $categoryids = explode(']', $categoryids)[0];
            $categoryids = explode(',', $categoryids);            
        } else {
            $categoryids = array();
        }

        // Filter param "enrolled": Get all courses, or only enrolled / not enrolled.
        if (strpos($text, 'enrolled=true')) {                 
            $courses = enrol_get_my_courses($fields, $sort);

            // Filter by category IDs afterwards if necessary.
            if ($categoryids) {
                foreach ($courses as $key => $course) {
                    if (!in_array($course->category, $categoryids)) {
                        unset($courses[$key]);
                    }            
                }
            }
        } else if (strpos($text, 'enrolled=false')) {                 
            $enrolled_courses = enrol_get_my_courses();
            $courses = $this->get_all_courses($courseids, $categoryids, $fields, $sort);
            foreach ($courses as $key => $value) {
                if (in_array($key, array_keys($enrolled_courses))) {
                    unset($courses[$key]);
                }
            }
        } else {
            $courses = $this->get_all_courses($courseids, $categoryids, $fields, $sort);
        }

        // Add customfields to courses.        
        foreach ($courses as $key => $course) {  
            if (is_object($course)) {       
                $handler = course_handler::create($course->id);               
                $customfields = $handler->export_instance_data($course->id);
                foreach ($customfields as $customfield) {
                    $fieldname = $customfield->get_shortname();                
                    $course->$fieldname = $customfield->get_data_controller()->get_value();                                                
                }   
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
        
        // Process courses.
        if ($courses) {

            $courserenderer = $PAGE->get_renderer('core', 'course');

            // Render from alternative template.
            if (strpos($text, 'template=')) {

                global $DB, $OUTPUT, $USER;

                // Get name of alttemplate.
                $alttemplate = explode('template=', $text)[1];
                $alttemplate = explode(' ', $alttemplate)[0];   

                // Write courses using alternative Mustache template.                                       
                foreach ($courses as $course) {
                    
                    // Get values for additional placeholders.                                        
                    $course->courseimage = $courserenderer->get_generated_image_for_id($course->id);
                    $course->coursecategory = $DB->get_record('course_categories', array('id' => $course->category), 'name')->name;
                    $progress = \core_completion\progress::get_course_progress_percentage($course, $USER->id);                    
                    if ($progress !== null) {                        
                        $course->courseprogress = round($progress, 0);
                    } 
                    $course->startdate = userdate($course->startdate);
                    $course->enddate = userdate($course->enddate);                    

                    // Convert to array for template export.
                    $data['courses'][] = json_decode(json_encode ( $course ) , true);
                }     
                $output .= $OUTPUT->render_from_template('filter_courselist/' . $alttemplate, $data);   
            
            // Render coursecards.                            
            } else {                
                $output .= $courserenderer->courses_list($courses);

                // Filter param "title": Include title.
                if (strpos($text, 'title=')) {                 
                    $title = explode('title=', $text)[1];                
                    $title = explode('"', $title)[1];                

                    // Activate other filters.
                    $title = str_replace('[[', '{{', $title);
                    $title = str_replace(']]', '}}', $title);           

                    $output = $title . $output;
                } 
            }            

        // Filter param "noresults": include noresults message.
        } else {
            if (strpos($text, 'noresults=')) {                 
                $noresults = explode('noresults=', $text)[1];
                $noresults = explode('"', $noresults)[1];                

                // Activate other filters.
                $noresults = str_replace('[[', '{{', $noresults);
                $noresults = str_replace(']]', '}}', $noresults);           

                $output .= $noresults;
            }                   
        }

        // Filter param "showall": display button to show all courses.        
        if (!array_key_exists('courselist_showall', $_GET) && strpos($text, 'showall')
                && $showallbutton) {
            $url = "$_SERVER[REQUEST_URI]";
            $url .= (count($_GET) > 0 ? '&' : '?');
            $url .= 'courselist_showall=1';
            $output .= '<a class="btn btn-primary" href="' . $url . '">'
                . get_string('showall', 'filter_courselist') . '</a>';
        } 

        return $output;
        
    }


    /**
     * Renders the searchbox.     
     *      
     * @return string
     */
    protected function searchbox() {
        global $OUTPUT;

        // Get parameters.
        $placeholder = get_string('searchcourses');
        $searchvalue = (array_key_exists('courselist_search', $_GET)) ? $_GET['courselist_search'] : null;

        // Render searchbox.
        $data = array('placeholder' => $placeholder, 'searchvalue' => $searchvalue);
        return $OUTPUT->render_from_template('filter_courselist/searchbox', $data);           
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