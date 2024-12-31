# Filterable course list

## Installation

Install like any other filter plugin - put into your /filter subfolder and enable in site filter settings.

Note: if you want to use this plugin inside of blocks, you have to turn it ON for the whole site, since you cannot selectively turn on specific filters for blocks.

## Usage

Add ''{{ courselist'' to your text, followed by filter parameters. All parameters are optional.

### Basic features
- **title**: a title to put as a h3 over the coursecards. if no results are returned, no title will be shown.
- **search**: will include a searchbox at the top of the coursecards grid, that searches for text inside the course's shortname, fullname and summary. if more than one coursecard grids exist on this page, the search will be applied to all of them, so it does not make sense to include searchboxes in more than one coursegrid.
- **number**: maximum number of courses to display.
- **showall**: includes a show all button at the bottom of the coursecards grid.
- **noresults**: text to show when there are no results
- **resultsummary**: show number of courses that fit the criteria (if *number* is used, it will show how many courses from how many total are currently displayed)

### Course properties
- **enrolled**: if set to true,  only lists courses the user is enrolled in, if set to false, only lists courses the user is not enrolled in - *eg: enrolled=true*
- **showhidden**: show hidden courses even if the user cannot access them (only works without "enrolled=true")
- **courseids**: comma-separated list inside square brackets - only lists courses with this ID - *eg: courseid=[2,4]*
- **categoryids**: only lists courses from this category. You can specify multiple categories inside square brackets, separated by a comma.
- **subcategories**: will also list courses from subcategories of the specified categoryids
- **progress**: will only show courses with a certain progress in course completion for this user - *eg: completion>50*

If a completion higher than a certain value is specified, courses that do not have course completion activated will not be shown.

The keyword "inprogress" will only show courses with a completion higher than zero, but lower than 100, *eg: completion=inprogress*

### Sorting
- **sort**: sort by course field. valid fields are: id, category, shortname, fullname, idnumber, startdate, enddate, visible, groupmode' - *eg: sort=startdate*. If no sort is given and courseids is used, courses will be returned in the order specified in courseids.
- **reverse**: reverses the sort order - *eg: reverse*

### Filtering for fields
- **filters**: comma-separated list inside square brackets - additional filters for any course fields (including custom course fields). supports multiple operators (<, >, =) and the php keyword *NOW* for the current timestamp - *eg: filters=[startdate<NOW,mycustomfield=1]*. < and > will be accepted as &gt; and &lt;.
- **cohortfield**: only shows courses that have a field with the same name as a cohort the user is enrolled in, and the specified value - *eg: cohortfields=1* or *cohortfields>2*

This allows you to make custom course field checkboxes named after cohorts, and control which cohorts the courses are displayed to.

When including custom course fields, be aware of how the values are saved, in order for filters to work. For example, the value of a dropdown menu is not the text of the selected option, but its index (starting at 1, not at 0!).

### Templates
- **template**: alternative mustache template to use instead of of the built-in Moodlecourse cards - *eg: template=list*.

You can put your own templates into the /templates subfolder, or use the existing ones.

You can also use templates from other components by specifying them with their full name, eg *theme_tm_moove/custom_coursecards*

Templates can aggregate the courses using any existing field, if aggregation is used inside the template, the template name has to include *aggregated-by-[criteria]*, eg,
*linkedlist-aggregated-by-coursecategory*.

Right now, the **Moodle Mobile App** will always display a modified version of course cards, and ignore templates.

#### Included templates
- **list**: similar to the "list" view of the myoverview block
- **teaser**: similar to list, but smaller, usable in sidebars
- **slick-carousel**: a carousel using the awesome slick carousel - https://kenwheeler.github.io/slick/
- **textlist-aggregated-by-coursecategory**: a simple list aggregated by course category
- **linkedlist-aggregated-by-coursecategory**: a simple list aggregated by course category, with the courses linked.

#### Special fields in templates.
In addition to the standard fields of the course DB item and any course custom fields, you can use these values in mustache templates:

- {{ categories }} - additional classes to be added for every course category (to support theme_tm_moove's custom category colors)
- {{ coursecategory }} - the name of the course category
- {{ courseimage }} - the url for the course image
- {{ courseprogress }} - the % of course progress
- {{ wwwroot }} - the site's wwwroot

### Options via GET parameters
- **useget**: enables all of the above options to also be applied via GET parameters in the URL. GET parameters will overrule parameters set in the filter - *eg: https://your-moodle-site.com?courselist_filter_startdate="<NOW"&courselist_filter_mycustomfield="=1"&courselist_cohortfields=">2"&courselist_sort=startdate&courselist_number=23&courseids=3,6,23* etc.

For filters and options that support other operators than "=", either give the operator and the value, eg *&courselist_filter_mycustomfield="=1"*, or only the value, to assume = as an operator, eg *&courselist_filter_mycustomfield=1*

**Be aware, that courselist_filter and cohortfield values need to include the operator in the value!** So always use *courselist_filter_mycustomfield="=1"*, and not *courselist_filter_mycustomfield=1*.

### Multi-field text search via GET parameter
- **courselist_search**: searches for the given text fragment in course shortname, fullname, and summary - *eg: https://your-moodle-site.com?courselist_search="asdf"*.

This enables you, for example, to build your own customized search interface. Be aware, that users can enter GET parameters themselves, to potentially see courses that they should not see.

### Usage example of text filter with all possible parameters
{{ courselist useget title="featured courses" search enrolled=true categoryids=[1,2] subcategories showhidden courseids=[3,4] sort=startdate reverse filters=[startdate<NOW,mycustomfield=1] cohortfield>2 number=23 showall noresults="no courses found" template=list nest=coursecategory }}

