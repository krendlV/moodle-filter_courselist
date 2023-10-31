# Filterable course list

## Installation

Install like any other filter plugin - put into your /filter subfolder and enable in site filter settings. 

Note: if you want to use this plugin inside of blocks, you have to turn it ON for the whole site, since you cannot selectively turn on specific filters for blocks.

## Usage

Add ''{{ courselist'' to your text, followed by filter parameters. All parameters are optional.

### Parameters

#### Basic features
- title: a title to put as a h3 over the coursecards. if no results are returned, no title will be shown.
- search: will include a searchbox at the top of the coursecards grid, that searches for text inside the course's shortname, fullname and summary. if more than one coursecard grids exist on this page, the search will be applied to all of them, so it does not make sense to include searchboxes in more than one coursegrid.
- number: maximum number of courses to display.
- showall: includes a show all button at the bottom of the coursecards grid.
- noresults: text to show when there are no results

#### Course properties
- enrolled: if set to true,  only lists courses the user is enrolled in, if set to false, only lists courses the user is not enrolled in - *eg: enrolled=true*
- showhidden: show hidden courses even if the user cannot access them (only works without "enrolled=true")
- courseids: comma-separated list inside square brackets - only lists courses with this ID - *eg: courseid=[2,4]*
- categoryids: only lists courses from this category. You can specify multiple categories inside square brackets, separated by a comma.
- subcategories: will also list courses from subcategories of the specified categoryids

#### Sorting
- sort: sort by course field. valid fields are: id, category, shortname, fullname, idnumber, startdate, enddate, visible, groupmode' - *eg: sort=startdate*. If no sort is given and courseids is used, courses will be returned in the order specified in courseids.
- reverse: reverses the sort order - *eg: reverse*

#### Filtering
- filters: comma-separated list inside square brackets - additional filters for any course fields (including custom course fields). supports multiple operators (<, >, =) and the php keyword *NOW* for the current timestamp - *eg: filters=[startdate<NOW,mycustomfield=1]*. < and > will be accepted as &gt; and &lt;.
- cohortfield: only shows courses that have a field with the same name as a cohort the user is enrolled in, and the value 1. This allows you to make custom course field checkboxes named after cohorts, and control which cohorts the courses are displayed to.

### A note on including custom course fields

- When including custom course fields, be aware of how the values are saved, in order for filters to work. For example, the value of a dropdown menu is not the text of the selected option, but its index.

### Using alternative templates.

You can specify an alternative mustache template using the paramter **template**, eg *template=list*.

This will use an alternative Mustache template instead of the built-in Moodlecourse cards. You can put your own templates into the /templates subfolder, or use the existing ones. 

These templates can aggregate the courses using any existing field, if aggregation is used inside the template, the template name has to include *aggregated-by-[criteria]*, eg, 
*linkedlist-aggregated-by-coursecategory*.

### Included templates

- list: similar to the "list" view of the myoverview block
- teaser: similar to list, but smaller, usable in sidebars
- slick-carousel: a carousel using the awesome slick carousel - https://kenwheeler.github.io/slick/
- textlist-aggregated-by-coursecategory: a simple list aggregated by course category
- linkedlist-aggregated-by-coursecategory: a simple list aggregated by course category, with the courses linked.

### Special fields in templates.

In addition to the standard fields of the course DB item and any course custom fields, you can use these values in mustache templates:

- {{ categories }} - additional classes to be added for every course category (to support theme_tm_moove's custom category colors)
- {{ coursecategory }} - the name of the course category
- {{ courseimage }} - the url for the course image
- {{ courseprogress }} - the % of course progress
- {{ wwwroot }} - the site's wwwroot

## Usage example with all possible parameters

{{ courselist title="featured courses" search enrolled=true categoryids=[1,2] subcategories showhidden courseids=[3,4] sort=startdate reverse filters=[startdate<NOW,mycustomfield=1] number=23 showall noresults="no courses found" template=list nest=coursecategory }}