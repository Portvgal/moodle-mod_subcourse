# Moodle Subcourse Code Review Backlog

This backlog reflects the current working tree review for `mod_subcourse` on 2026-09-03 after the automated-test
blockers and immediate cleanup warnings were addressed. This `MOODLE_501_STABLE` branch application still needs Moodle
5.1 PHPUnit, Behat, and PHPCS verification in a Moodle 5.1 checkout.

## Must
None before Moodle testing.

## Should
1. Run the manual Moodle testing scenarios in `moodle-preflight-report.md`, especially fresh install, upgrade from older plugin versions, backup/restore, role boundaries, and developer-debugging checks.
2. Rehearse the pass-grade completion upgrade on representative production-scale data if the site has large courses or many existing Subcourse completion records.
3. Review the broad local `README.md` edits for release wording and maintainer voice. The Moodle 5.1 support statement now matches `version.php`.
4. Decide whether a later refactor should extract grade-fetch/update orchestration from `locallib.php` into an autoloaded service while preserving Moodle callback wrappers. The immediate post-grade-update sync logic has already been extracted to `classes/grades/grade_item_sync.php`.

## Could
1. Execute `moodle-manual-verification-checklist.md` and record the completed browser accessibility, Moodle App, backup/restore, and upgrade-scale outcomes.
2. Add more browser-level accessibility checks for the semantic progress element, including keyboard, screen-reader, RTL, and theme contrast checks.
3. Add a manual or automated Moodle App smoke test for `templates/mobile_view_latest.mustache`.
4. Add performance logging or admin-visible diagnostics around scheduled grade fetch and completion sync runtimes.
5. Tighten stale comments and docblocks in legacy activity-report callback stubs.
6. Add a release note explaining the migration from Moodle core `completionpassgrade` to the plugin's strict `completionpassgradesubcourse` rule.

## Beneficial Features
1. Add an admin diagnostics page or CLI task that reports subcourse instances with missing/deleted referenced courses, local-scale grade incompatibility, stale fetch times, and completion sync anomalies.
2. Add a manual re-sync/repair tool for selected subcourse instances, with dry-run output and user counts before writing grades/completions.
3. Add scheduled-task batching controls for grade fetch and completion sync on large sites.
4. Improve teacher-facing fetch feedback with clearer per-user/per-grade failure counts and local-scale warnings.
5. Add richer completion status visibility on the activity page, distinguishing referenced-course completion, fetched grade, passing grade, hidden grade, and not-yet-fetched states.
