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

        $courses = enrol_get_my_courses();

        foreach ($courses as $course) {
            if ($course instanceof stdClass) {
                $course = new core_course_list_element($course);
            }        
        }
        
        return $courserenderer->courses_list($courses);
        
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