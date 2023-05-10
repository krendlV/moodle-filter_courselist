# Filterable course list

## Installation

Install like any other filter plugin - put into your /filter subfolder and enable in site filter settings.

## Usage

Add ''{{ courselist'' to your text, followed by filter parameters. All parameters are optional.

### Parameters
- number: maximum number of courses to display.
- enrolled: if true, only lists courses the user is enrolled in, if false, only lists courses the user is not enrolled in.
- categoryid: only lists courses from this category. You can specify multiple categories inside square brackets, separated by a comma.
- courseid: only lists courses with this ID. You can specify multiple courseids inside square brackets, separated by a comma.

- coursedate: filters courses by their start or end date

Example: {{ coursefilterlist categoryid=8 courseid=[1,3] coursedate>NOW, courseenddate<NOW, enrolled=true number=3 sort=coursedate sort_direction=ASC }}