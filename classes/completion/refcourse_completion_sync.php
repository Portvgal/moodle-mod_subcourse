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

declare(strict_types=1);

namespace mod_subcourse\completion;

use completion_completion;
use completion_info;
use context_module;
use stdClass;

/**
 * Synchronises subcourse completion from referenced course completion.
 *
 * @package     mod_subcourse
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class refcourse_completion_sync {
    /**
     * Synchronise one user for one subcourse instance.
     *
     * @param stdClass $subcourse Subcourse record.
     * @param int $userid User id.
     * @return bool True if completion state was evaluated and update_state() was called.
     */
    public static function sync_user(stdClass $subcourse, int $userid): bool {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/lib/completionlib.php');
        require_once($CFG->dirroot . '/completion/completion_completion.php');

        $hasrefcourserule = !empty($subcourse->completioncourse);
        $haspassgraderule = !empty($subcourse->completionpassgradesubcourse);

        if (!$hasrefcourserule && !$haspassgraderule) {
            return false;
        }

        if (!completion_info::is_enabled_for_site()) {
            return false;
        }

        $course = $DB->get_record('course', ['id' => $subcourse->course], '*', IGNORE_MISSING);
        if (!$course) {
            return false;
        }

        $cm = get_coursemodule_from_instance('subcourse', $subcourse->id, $course->id, false, IGNORE_MISSING);
        if (!$cm) {
            return false;
        }

        $modulecontext = context_module::instance($cm->id);
        if (!has_capability('mod/subcourse:begraded', $modulecontext, $userid)) {
            return false;
        }

        $cminfo = get_fast_modinfo($course, $userid)->get_cm($cm->id);
        $completion = new completion_info($course);
        if (!$completion->is_enabled($cminfo)) {
            return false;
        }

        if (!$hasrefcourserule) {
            $completion->update_state($cminfo, COMPLETION_UNKNOWN, $userid);
            return true;
        }

        if (!empty($subcourse->refcourse)) {
            $coursecompletion = new completion_completion(['userid' => $userid, 'course' => $subcourse->refcourse]);
            if ($coursecompletion->is_complete()) {
                $completion->update_state($cminfo, COMPLETION_COMPLETE, $userid);
                return true;
            }

            if (!empty($subcourse->completioncoursereversible) || $haspassgraderule) {
                $completion->update_state($cminfo, COMPLETION_UNKNOWN, $userid);
                return true;
            }
        }

        return false;
    }

    /**
     * Synchronise multiple users for one subcourse instance.
     *
     * @param stdClass $subcourse Subcourse record.
     * @param array $userids User ids.
     * @return int Number of users whose completion state was evaluated and update_state() was called.
     */
    public static function sync_users(stdClass $subcourse, array $userids): int {
        $count = 0;

        foreach (array_unique(array_map('intval', $userids)) as $userid) {
            if ($userid > 0 && self::sync_user($subcourse, $userid)) {
                $count++;
            }
        }

        return $count;
    }
}
