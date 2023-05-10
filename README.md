# Filterable course list

## Installation

Install like any other filter plugin - put into your /filter subfolder and enable in site filter settings. 

## Usage

Add ''{{ courselist'' to your text, followed by filter parameters. All parameters are optional.

### Parameters
- enrolled: if set to true,  only lists courses the user is enrolled in, if set to false, only lists courses the user is not enrolled in - *eg: enrolled=true*
- courseid: comma-separated list inside square brackets - only lists courses with this ID - *eg: courseid=[2,4]*
- categoryid: only lists courses from this category. You can specify multiple categories inside square brackets, separated by a comma.
- sort: can use any course field to sort - *eg: sort=startdate*
- reverse: reverses the sort order - *eg: reverse*
- filters: comma-separated list inside square brackets - additional filters for any course fields (including custom course fields). supports multiple operators (<, >, =) and the php keyword *NOW* for the current timestamp - *eg: filters=[startdate<NOW,mycustomfield=1]*
- number: maximum number of courses to display.

### A note on including custom course fields
- When including custom course fields, be aware of how the values are saved, in order for filters to work. For example, the value of a dropdown menu is not the text of the selected option, but its index.

### Example with all possible parameters: 
{{ coursefilterlist categoryid=8 courseid=[1,3] coursedate>NOW, courseenddate<NOW, enrolled=true number=3 sort=coursedate sort_direction=ASC }}