Subcourse module for Moodle
===========================

![Moodle Plugin CI](https://github.com/catalyst/moodle-mod_subcourse/workflows/Moodle%20Plugin%20CI/badge.svg)

The Subcourse activity lets one Moodle course include the result of another
course as a graded activity.

When a Subcourse activity is added to a course, it points to a referenced course.
The student's final grade from the referenced course is fetched into the current
course gradebook as the grade for the Subcourse activity. Combined with
[Course meta link enrolments](https://docs.moodle.org/en/Course_meta_link), this
allows course designers to organise a programme into separate unit courses while
still reporting grades and activity completion in a parent course.

Typical use cases include:

* A main course that contains several unit courses.
* A parent course that needs one grade item per referenced course.
* A course page that should show each student's progress or grade from the
  referenced course.
* A completion workflow where the parent course activity is completed when the
  student passes, or when Moodle marks the referenced course complete.

What changed
------------

This version improves Subcourse completion and display behaviour:

* Added a clearer grade-based completion workflow for Subcourse activities. The
  activity can now use a strict Subcourse pass-grade rule, so a failing fetched
  grade remains incomplete instead of being treated as complete just because a
  grade exists.
* Added support for defaulting the Subcourse activity's grade to pass from the
  referenced course total's grade to pass.
* Clarified and separated the Moodle Course completion rule from gradebook-based
  completion. Moodle Course completion is now documented as an optional
  whole-course status check, not as the same thing as the Gradebook Course Total.
* Added an optional reversible course-completion behaviour. When enabled, the
  Subcourse activity can return to incomplete if Moodle later removes the
  student's completed status in the referenced course.
* Improved completion synchronisation after grade fetches and through the
  scheduled referenced-course completion check.
* Added course-page display controls for showing referenced-course progress and
  referenced-course grade beside the Subcourse activity.
* Removed unrelated standard form fields from the Subcourse setup workflow so the
  activity settings stay focused on choosing a referenced course, fetching
  grades, link behaviour, appearance, and completion.

How it works
------------

Subcourse reads from the referenced course and writes to the course that contains
the Subcourse activity.

```text
Referenced course
  Gradebook course total
        |
        | fetch grades
        v
Parent course
  Subcourse activity grade item
        |
        | optional activity-completion rules
        v
  Subcourse activity completion
```

The important distinction is that Moodle has two separate concepts:

* **Gradebook Course Total**: the numeric final grade shown in the referenced
  course gradebook. This is what Subcourse fetches as the Subcourse activity
  grade.
* **Moodle Course completion**: Moodle's separate whole-course completion status.
  A course is complete only when that course's own completion criteria say it is
  complete, such as activity completion criteria, self completion, date criteria,
  unenrolment criteria, or other enabled course-completion rules. This is not the
  same thing as receiving a final grade.

For most grade-based workflows, configure the referenced course gradebook and use
the Subcourse passing-grade completion rule. Enable the Moodle course-completion
rule only when the referenced course has Moodle Course completion configured and
you explicitly want the parent activity to wait for that whole-course completion
status.

Branches
--------

The git branches here support the following versions.

| Moodle version | Branch            |
|----------------|-------------------|
| Moodle 4.1     | MOODLE_401_STABLE |
| Moodle 4.2     | MOODLE_402_STABLE |
| Moodle 4.3     | MOODLE_403_STABLE |
| Moodle 4.4     | MOODLE_404_STABLE |
| Moodle 4.5     | MOODLE_405_STABLE |

The current working tree declares support for Moodle 4.5 and Moodle 5.0 in
`version.php`. Moodle 5.2 is not declared as supported by this branch and should
be treated as forward-compatibility testing only.

Installation
------------

Follow the general Moodle plugin installation instructions:
<https://docs.moodle.org/en/Installing_plugins#Installing_a_plugin>

When installing from an uploaded ZIP package or via Git, the `subcourse`
directory must be placed under the `/mod` directory of the Moodle installation.

Basic usage
-----------

1. Create a main course.
2. Create one or more referenced courses that should act as subcourses of the
   main course.
3. Enrol students into both the main course and the referenced courses.
4. In the main course, add one Subcourse activity for each referenced course.
5. In the Subcourse activity settings, choose the referenced course in **Fetch
   grades from**.
6. Let students complete work and receive grades in the referenced course.
7. Fetch grades manually or wait for the scheduled grade-fetching task.
8. Confirm that the referenced course total appears as the Subcourse activity
   grade in the main course gradebook.

Activity settings
-----------------

### Referenced course

**Fetch grades from** selects the course that this Subcourse activity reads from.
The current course cannot reference itself.

The course list contains courses where the editing user can view the gradebook.
If a previously selected course is no longer available to the editing user, the
form allows the existing reference to be kept.

### Grades fetching

**Fetch grades as** controls how the referenced course total is copied into the
Subcourse grade item.

* **Real values** fetches the actual final grade value from the referenced course.
  Use this when the parent course should store the same numeric grade as the
  referenced course total.
* **Percentual values** recalculates the fetched grade so that the percentage
  displayed in the Subcourse activity matches the percentage displayed in the
  referenced course. This is useful when grade ranges differ or when excluded
  grades mean the raw value and displayed percentage do not line up exactly.

The Subcourse grade item copies key grade item settings from the referenced
course total, including grade type, minimum grade, maximum grade, scale, hidden
state, and grade to pass. Referenced course totals that use a non-global local
scale cannot be fetched because the scale is not reusable outside that course.

Grades can be fetched in three ways:

* Manually, by a user with the `mod/subcourse:fetchgrades` capability using
  **Fetch grades now**.
* Automatically when Moodle fires grade-related events for the referenced course.
* By the scheduled task `\mod_subcourse\task\fetch_grades`.

### Subcourse activity link

**Redirect to the referenced course** sends users directly to the referenced
course when they open the Subcourse activity page. Users who can fetch grades
manually still see the Subcourse page so they can use the fetch action.

**Open in a new window** opens the referenced course in a new browser window when
the redirect option is enabled.

### Appearance

**Display progress from referenced course on course page** shows the student's
referenced-course progress beside the Subcourse activity on the parent course
page.

**Display grade from referenced course on course page** shows the student's
fetched referenced-course grade beside the Subcourse activity on the parent
course page.

These options only control display on the parent course page. The Subcourse view
page can still show referenced-course information.

Completion settings
-------------------

Subcourse supports Moodle's standard activity completion settings and adds rules
that are specific to referenced courses.

### Recommended grade-based completion

For the common requirement "complete this Subcourse activity when the student
passes the referenced course":

1. Configure the referenced course gradebook course total and set its grade to
   pass.
2. Add or edit the Subcourse activity in the parent course.
3. Enable automatic activity completion.
4. Enable the passing-grade completion option in Moodle's completion settings.
5. Leave **Also require Moodle course completion** unticked unless the referenced
   course also has Moodle Course completion criteria that must be satisfied.

Subcourse stores this as its own strict pass-grade rule. This matters because
Moodle's core pass-grade activity completion can mark a graded activity as
complete even when the grade is a failing grade. Subcourse's rule keeps the
activity incomplete until the fetched Subcourse grade is greater than or equal to
the activity grade to pass.

When possible, the Subcourse activity grade to pass is defaulted from the
referenced course total's grade to pass.

### Also require Moodle course completion

**Also require Moodle course completion** checks Moodle's whole-course completion
status for the referenced course.

Use this only when the referenced course has course completion enabled and
configured under that course's **Course completion** settings. A referenced
course can have a passing grade without being course-complete, and it can be
course-complete for reasons that are not only its course total grade. These are
different Moodle subsystems.

When this rule is enabled, the Subcourse activity is complete only when Moodle
reports that the student has completed the referenced course. Moodle normally
updates this when the referenced course completion event fires. The scheduled task
`\mod_subcourse\task\check_completed_refcourses` also checks Subcourse instances
that use this rule so missed or older completion records can be synchronised.

### Undo this if Moodle course completion is removed

**Undo this if Moodle course completion is removed** belongs to the Moodle
course-completion rule above.

By default, once Moodle marks the Subcourse activity complete, it stays complete.
Enable this option only if the parent activity should change back to incomplete
when Moodle later removes the student's completed status in the referenced
course.

### Choosing the right completion rule

```text
Do you want the parent activity complete when the student gets a passing grade?
  Yes -> Use the Subcourse passing-grade completion rule.
         This is based on the fetched Gradebook Course Total.

Do you want the parent activity complete only when Moodle says the whole
referenced course is complete?
  Yes -> Enable Also require Moodle course completion.
         This is based on Moodle Course completion, not directly on the gradebook.

Do you want both requirements?
  Yes -> Enable both rules.
         The student must pass the fetched grade and Moodle must mark the
         referenced course complete.
```

Site administration settings
----------------------------

The plugin provides default settings under the Subcourse activity administration
settings.

* **Progress on course page** controls the default value for showing referenced
  course progress on the parent course page.
* **Grade on course page** controls the default value for showing referenced
  course grade on the parent course page.
* **Display hidden courses** allows hidden courses to be selected when editing a
  Subcourse activity.

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
