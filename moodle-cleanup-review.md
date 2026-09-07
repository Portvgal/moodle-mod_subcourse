MOODLE PLUGIN CLEANUP REVIEW
============================
Plugin: mod_subcourse
Target Moodle: 4.5 primary; Moodle 5.0 declared supported
Mode: Review-first plus approved fixes applied

SUMMARY
The prior cleanup blockers have been addressed. No duplicate file contents, committed secrets, raw request superglobals,
or unsafe SQL string interpolation were found. The latest warning cleanup pass reduced `locallib.php` coupling and upgrade
memory risk. The remaining cleanup work is mostly longer-term maintainability around legacy callbacks, manual
accessibility/mobile verification, and large-site performance validation.

COMPLETED CLEANUP / FIXES
1. [DONE] Fixed strict pass-grade completion PHPUnit data provider
   Location: `tests/completion_test.php`
   Evidence: Provider now returns bootstrap-safe labels, and the test maps labels to Moodle constants inside the test method.
   Verification: `vendor/bin/phpunit mod/subcourse/tests/completion_test.php` passes with 12 tests and 21 assertions.

2. [DONE] Renamed external service test to current Moodle convention
   Location: `tests/externallib_test.php`
   Evidence: Former `tests/external.php` was moved to `tests/externallib_test.php` with namespaced `externallib_test` class and PHPUnit process isolation.
   Verification: `vendor/bin/phpunit mod/subcourse/tests/externallib_test.php` passes with 1 test and 1 assertion.

3. [DONE] Removed deprecated module support declaration
   Location: `lib.php`
   Evidence: `FEATURE_GROUPMEMBERSONLY` case was removed from `subcourse_supports()`.
   Verification: PHP syntax, PHPCS, focused PHPUnit, and Behat pass.

4. [DONE] Removed live deprecated language string
   Location: `lang/en/subcourse.php`; `lang/en/deprecated.txt`
   Evidence: `gotocoursename` no longer exists in the live English string file, while `deprecated.txt` keeps the deprecation record.
   Verification: Static search finds only the expected `lang/en/deprecated.txt` reference.

5. [DONE] Removed inline progress style
   Location: `templates/subcourseinfo.mustache`; `styles.css`
   Evidence: Progress output now uses a semantic `<progress>` element and scoped plugin CSS instead of `style="width: ..."` in the template.
   Verification: Static search finds no template inline style; Behat `@mod_subcourse` passes.

6. [DONE] Added upgrade regression coverage
   Location: `tests/upgrade_test.php`
   Evidence: Test covers migration from core `completionpassgrade` to `completionpassgradesubcourse`, including pass, fail, and manual override preservation.
   Verification: `vendor/bin/phpunit mod/subcourse/tests/upgrade_test.php` passes with 1 test and 7 assertions.

7. [DONE] Reduced upgrade memory risk
   Location: `db/upgrade.php`
   Evidence: Migration now uses recordsets for candidate course modules and completion rows, and centralizes repeated pass-grade field writes in `xmldb_subcourse_move_core_passgrade_rule()`.
   Verification: Upgrade regression test and PHP syntax pass.

8. [DONE] Extracted post-grade-update synchronization
   Location: `locallib.php`; `classes/grades/grade_item_sync.php`
   Evidence: `subcourse_grades_update()` now delegates gradepass, hidden grade state, and completion sync follow-up work to an autoloaded service class.
   Verification: PHPCS passes; `locallib_test.php`, `completion_test.php`, and Behat `@mod_subcourse` pass.

9. [DONE] Reviewed support documentation warning
   Location: `README.md`; `version.php`
   Evidence: README now states that the working tree declares Moodle 4.5 and 5.0 support, while Moodle 5.2 is forward-compatibility testing only.
   Verification: Maintainer release review still recommended for wording, but the version-support mismatch warning has been addressed.

10. [DONE] Added manual verification checklist
    Location: `moodle-manual-verification-checklist.md`
    Evidence: Checklist covers accessibility/theme rendering, Moodle App/external-service rendering, upgrade scale, backup/restore, and completion semantics.
    Verification: Checklist is prepared; human execution remains required.

NEEDS CONFIRMATION
1. [LOW] `templates/mobile_view_latest.mustache` remains a scanner false-positive orphan
   Location: `templates/mobile_view_latest.mustache`; `classes/output/mobile.php`
   Evidence: `mobile::main_view()` renders `mod_subcourse/mobile_view_latest`; `tests/output_mobile_test.php` now asserts strings from that rendered template.
   Recommendation: Do not delete.
   Verification: `vendor/bin/phpunit mod/subcourse/tests/output_mobile_test.php` passes.

2. [MEDIUM] Upgrade runtime on large production data remains unverified
   Location: `db/upgrade.php`
   Evidence: Upgrade is covered functionally and uses streaming recordsets, but production sites may have many grade/completion rows.
   Recommendation: Run an upgrade rehearsal on representative production-scale data before release.
   Verification: Manual/performance upgrade test.

DUPLICATE / OVER-COMPLEX LOGIC
1. [MEDIUM] `lib.php` remains callback-heavy
   Location: `lib.php`
   Evidence: Static scan reports 547 lines. It mixes required Moodle callbacks, cm info construction, completion rule descriptions, grade-item callbacks, and legacy activity-report stubs.
   Recommendation: Keep required callbacks in `lib.php`; move non-callback helper behavior into namespaced classes when touched for functional work.

2. [LOW] `locallib.php` still contains broad grade-fetching responsibilities
   Location: `locallib.php`
   Evidence: Post-grade-update synchronization has been extracted, but fetching reference grades and grade-update orchestration remain in the global compatibility library.
   Recommendation: Extract grade-fetch/update orchestration into an autoloaded service in a later refactor, preserving global wrapper functions for Moodle callback compatibility.

DO NOT DELETE
- `version.php`, `settings.php`, `view.php`, `index.php`, and Moodle activity callbacks in `lib.php`.
- `db/access.php`, `db/events.php`, `db/tasks.php`, `db/services.php`, `db/mobile.php`, `db/install.xml`, and `db/upgrade.php`.
- `classes/privacy/provider.php`, event classes, scheduled task classes, external service classes, backup/restore classes, and generator/test files.
- `templates/mobile_view_latest.mustache`, because it is rendered by mobile output.
- Help strings and capability strings that are looked up dynamically by Moodle forms, permissions UI, AMOS, and help-button conventions.

SUGGESTED IMPLEMENTATION ORDER
1. Perform manual Moodle test scenarios from `moodle-preflight-report.md`.
2. Rehearse upgrade on production-scale data if this plugin is deployed to large courses.
3. Schedule a separate maintainability refactor for `lib.php`/`locallib.php` after the current completion work is accepted.
4. Perform maintainer release review of the expanded README wording.

TESTS / CHECKS REQUIRED
- `git diff --check`: PASS.
- `xmllint --noout db/install.xml`: PASS.
- PHP syntax across plugin PHP files: PASS.
- Moodle PHPCS for `mod/subcourse`: PASS.
- Focused PHPUnit files: PASS individually.
- Tagged Behat `@mod_subcourse`: PASS.
- Manual install, upgrade, backup/restore, role, mobile, and accessibility checks remain required for tester sign-off.

TOOLS RUN
- Cleanup scanner: PASS with conservative false-positive leads.
- Preflight scanner: PASS with warnings; no blockers.
- Static checks: PASS.
- PHPUnit: PASS for completion, external, upgrade, locallib, and mobile tests.
- Behat: PASS after Moodle cache purge, 15 scenarios with 13 passed and 2 skipped.

NOT RUN / NOT VERIFIED
- Full Moodle PHPUnit/Behat suites.
- Fresh install/upgrade/manual browser/mobile/accessibility checks. Manual items are now tracked in `moodle-manual-verification-checklist.md`.
- Moodle 5.0 runtime check, because no local Moodle 5.0 container was available.
