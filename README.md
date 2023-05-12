# Filterable course list

## Installation

Install like any other filter plugin - put into your /filter subfolder and enable in site filter settings. 

Note: if you want to use this plugin inside of blocks, you have to turn it ON for the whole site, since you cannot selectively turn on specific filters for blocks.

## Usage

Add ''{{ courselist'' to your text, followed by filter parameters. All parameters are optional.

### Parameters
- search: will include a searchbox at the top of the coursecards grid, that searches for text inside the course's shortname, fullname and summary. if more than one coursecard grids exist on this page, the search will be applied to all of them, so it does not make sense to include searchboxes in more than one coursegrid.
- enrolled: if set to true,  only lists courses the user is enrolled in, if set to false, only lists courses the user is not enrolled in - *eg: enrolled=true*
- courseid: comma-separated list inside square brackets - only lists courses with this ID - *eg: courseid=[2,4]*
- categoryid: only lists courses from this category. You can specify multiple categories inside square brackets, separated by a comma.
- sort: can use any course field to sort - *eg: sort=startdate*
- reverse: reverses the sort order - *eg: reverse*
- filters: comma-separated list inside square brackets - additional filters for any course fields (including custom course fields). supports multiple operators (<, >, =) and the php keyword *NOW* for the current timestamp - *eg: filters=[startdate<NOW,mycustomfield=1]*
- number: maximum number of courses to display.
- showall: includes a show all button at the bottom of the coursecards grid.

Example with all possible parameters: 
{{ coursefilterlist search enrolled=true categoryid=[1,2] courseid=[3,4] sort=startdate reverse filters=[startdate<NOW,mycustomfield=1] number=23 showall }}

### A note on including custom course fields
- When including custom course fields, be aware of how the values are saved, in order for filters to work. For example, the value of a dropdown menu is not the text of the selected option, but its index.