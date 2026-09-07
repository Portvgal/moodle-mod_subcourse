Subcourse module for Moodle
===========================

![Moodle Plugin CI](https://github.com/catalyst/moodle-mod_subcourse/workflows/Moodle%20Plugin%20CI/badge.svg)

The Subcourse activity lets one Moodle course include the result of another
course as a graded activity. It is useful when a programme is split into several
unit courses, but the parent course still needs one grade item and completion
state for each unit.

Subcourse works well with [Course meta link enrolments](https://docs.moodle.org/en/Course_meta_link):
students are enrolled in both the parent course and the referenced course, then
Subcourse fetches the referenced course total into the parent course gradebook.

Supported versions
------------------

This branch declares support for Moodle 4.5 and Moodle 5.0 in `version.php`.

| Moodle version | Branch            |
|----------------|-------------------|
| Moodle 4.1     | MOODLE_401_STABLE |
| Moodle 4.2     | MOODLE_402_STABLE |
| Moodle 4.3     | MOODLE_403_STABLE |
| Moodle 4.4     | MOODLE_404_STABLE |
| Moodle 4.5     | MOODLE_405_STABLE |

Installation
------------

Install this plugin in the standard Moodle way:
<https://docs.moodle.org/en/Installing_plugins#Installing_a_plugin>

The plugin directory must be named `subcourse` and placed under the Moodle
`mod` directory.

Basic usage
-----------

1. Create a parent course.
2. Create one or more referenced courses.
3. Enrol students into both the parent course and the referenced courses.
4. In the parent course, add one Subcourse activity for each referenced course.
5. In the Subcourse activity settings, choose the referenced course in **Fetch
   grades from**.
6. Let students complete work and receive grades in the referenced course.
7. Fetch grades manually or wait for the scheduled grade-fetching task.

Grades
------

Subcourse fetches the referenced course total into the parent course as the grade
for the Subcourse activity.

**Fetch grades as** controls how the referenced course total is copied:

* **Real values** stores the actual final grade from the referenced course.
* **Percentual values** recalculates the fetched grade so that the displayed
  percentage matches the referenced course.

The Subcourse grade item copies relevant course total settings from the
referenced course, including grade type, minimum grade, maximum grade, scale,
hidden state, and grade to pass. Referenced course totals using a non-global
local scale cannot be fetched because the scale cannot be reused outside that
course.

Grades can be fetched manually by a user with the `mod/subcourse:fetchgrades`
capability, automatically after Moodle grade events, or by the scheduled task
`\mod_subcourse\task\fetch_grades`.

Completion
----------

Subcourse supports Moodle's standard activity completion settings and adds rules
for referenced-course workflows.

For the common workflow "complete this Subcourse activity when the student
passes the referenced course", set a grade to pass on the referenced course
total, enable automatic completion on the Subcourse activity, and enable the
Subcourse pass-grade completion rule.

Subcourse uses its own strict pass-grade rule. A fetched failing grade keeps the
activity incomplete until the Subcourse activity grade is greater than or equal
to the activity grade to pass. When possible, the Subcourse activity grade to
pass is defaulted from the referenced course total's grade to pass.

**Also require Moodle course completion** checks Moodle's whole-course
completion status for the referenced course. Use it only when the referenced
course has course completion criteria configured and the parent activity should
wait for that whole-course status.

**Undo this if Moodle course completion is removed** lets the parent Subcourse
activity move back to incomplete when Moodle later removes the student's
completed status in the referenced course. Leave it disabled when a completed
parent activity should remain complete.

The scheduled task `\mod_subcourse\task\check_completed_refcourses` synchronises
Subcourse activities that use referenced-course completion.

Display options
---------------

Subcourse can show referenced-course progress and the fetched referenced-course
grade beside the activity on the parent course page. Site defaults are available
in the Subcourse activity administration settings.

When **Redirect to the referenced course** is enabled, users are sent directly to
the referenced course when opening the Subcourse activity. Users who can fetch
grades manually still see the Subcourse page so they can use the fetch action.

Capabilities
------------

Subcourse uses these capabilities:

* `mod/subcourse:addinstance`: add a Subcourse activity.
* `mod/subcourse:view`: view the Subcourse activity.
* `mod/subcourse:fetchgrades`: manually fetch grades from the referenced course.
* `mod/subcourse:begraded`: receive a grade from the referenced course.

Mobile app
----------

The plugin includes Moodle app support for viewing Subcourse information,
including the referenced course link and current fetched grade.

Support
-------

Free support for this plugin is available in the moodle.org forums:
<https://moodle.org/mod/forum/view.php?id=44>

Commercial support is available from Moodle Partner Catalyst IT:
<https://www.catalyst.net.nz/contact-us>

Author
------

The module was originally written by David Mudrák <david@moodle.com> and is now
maintained by Catalyst IT.

Useful links
------------

* [Bug tracker](https://github.com/catalyst/moodle-mod_subcourse/issues)

License
-------

This program is free software: you can redistribute it and/or modify it under the
terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this
program. If not, see <http://www.gnu.org/licenses/>.
