MOODLE PLUGIN PRE-TEST CHECK
============================
Plugin: mod_subcourse
Target Moodle: 5.1 primary; `version.php` declares Moodle 5.1 support
Overall: READY FOR MOODLE TESTING WITH WARNINGS

This report covers the current dirty working tree in `/Volumes/BLACKBOX/1.HOME_LAB_REPOS/moodle_subcourse`.
The prior PHPUnit blockers have been fixed. The warning cleanup pass has also reduced the main maintainability and
upgrade-scale warnings. The remaining warnings require human/device verification or representative production data,
not code changes that can be proven from this local checkout alone.

BLOCKERS
None.

WARNINGS
1. [WARNING] Maintainability - `lib.php` and `locallib.php` remain large legacy integration files. Required Moodle callbacks should stay in `lib.php`. The post-grade-update gradepass, hidden-state, and completion sync logic has been moved to `classes/grades/grade_item_sync.php`; future non-callback logic should continue moving to autoloaded classes when touched.
2. [WARNING] Scanner false positives - the deterministic preflight scanner still flags state-changing logic in `locallib.php`, `db/upgrade.php`, backup/restore, and tests. Semantic review found these are Moodle-internal paths using Moodle DML, upgrade, backup/restore, scheduled task, observer, or test APIs rather than unauthenticated browser entry points.
3. [WARNING] Upgrade scale - `db/upgrade.php` still recalculates existing pass-grade completion rows. It now streams candidate course modules and completion rows with recordsets and has regression coverage, but large production sites should still test upgrade runtime on representative data.
4. [WARNING] Manual accessibility and mobile behavior were not interactively verified. The inline progress style was removed, Behat passed, and `moodle-manual-verification-checklist.md` now defines the checks, but screen-reader, keyboard, RTL, target-theme, and real Moodle App checks remain manual.
5. [WARNING] Documentation - `README.md` now states the current Moodle 5.1 support declaration and points Moodle 4.5/5.0 users to `MOODLE_405_STABLE`. The broad README edits should still be reviewed by the maintainer before release.

CHECKS
[PASS] Plugin structure - `version.php` declares `mod_subcourse`; activity-module structure is present with `db/`, `classes/`, backup/restore, language strings, tests, Behat features, mobile support, and privacy provider.
[PASS] Moodle API compatibility - The deprecated `FEATURE_GROUPMEMBERSONLY` support declaration was removed. Local PHP syntax checks load the changed plugin paths successfully.
[PASS] Authentication / authorisation - Browser fetch-now path uses `require_login()`, `require_capability('mod/subcourse:view')`, `require_sesskey()`, and `require_capability('mod/subcourse:fetchgrades')`. External view validates module context. Scheduled tasks and observers run as Moodle system integration paths and delegate user eligibility to `mod/subcourse:begraded`.
[PASS] Input / sesskey / SQL / output - Request input uses Moodle parameter APIs. Custom SQL reviewed in `locallib.php` and `db/upgrade.php` uses placeholders. No direct `$_GET`, `$_POST`, `$_REQUEST`, or `$_COOKIE` handling was found.
[PASS] Database / upgrade / compatibility - `db/install.xml` is well formed. Upgrade pass-grade migration now centralizes repeated writes and streams candidate course-module and completion rows. `tests/upgrade_test.php` covers pass, fail, and manual override preservation.
[PASS] Privacy - Existing privacy provider is present and declares no plugin-stored personal data. The current changes store activity configuration flags and update Moodle grade/completion records; no external data transfer was added.
[WARNING] Performance / clustering - Grade and completion synchronization still iterate users and grade rows. The upgrade path now avoids loading all completion rows for a module into memory, but large-course deployments should still profile scheduled tasks and upgrade runtime.
[PASS] UI / accessibility / language - User-facing text uses language strings. The deprecated live `gotocoursename` string was removed while `lang/en/deprecated.txt` keeps the deprecation record. The Subcourse progress bar no longer uses an inline style.
[PASS] Third-party code / secrets / licensing - No third-party code, credentials, tokens, or external service integrations were found in this plugin tree.
[WARNING] Moodle coding standards - Moodle PHPCS has not been rerun on a Moodle 5.1 Docker checkout in this branch application.
[WARNING] Automated tests - Focused plugin PHPUnit and tagged Behat passed on the Moodle 4.5 branch application before this 5.1 cherry-pick. A Moodle 5.1 runtime check remains required for this branch.
[WARNING] Manual test readiness - Manual install, upgrade, role, completion, backup/restore, mobile, and debugging-enabled checks still need to be performed in a tester-owned Moodle site.
[PASS] Documentation / installation - README exists, documents purpose/installation, and now aligns support wording with the Moodle 5.1 `version.php`. Current README edits should be reviewed before release.

TOOLS RUN
- `python3 /Users/airjb/.codex/skills/moodle-plugin-cleanup/scripts/cleanup_scan.py /Volumes/BLACKBOX/1.HOME_LAB_REPOS/moodle_subcourse --output /tmp/moodle-subcourse-cleanup-scan-after.md`: PASS; produced conservative heuristic leads.
- `python3 /Users/airjb/.codex/skills/moodle-plugin-preflight/scripts/preflight.py /Volumes/BLACKBOX/1.HOME_LAB_REPOS/moodle_subcourse --target-moodle 5.1 --output /tmp/moodle-subcourse-preflight-after-501.md`: PASS with warnings; no scanner blockers.
- `git diff --check`: PASS.
- `xmllint --noout db/install.xml`: PASS.
- `find . -name '*.php' -not -path './.git/*' -print0 | xargs -0 -n1 php -l`: PASS across plugin PHP files.
- Moodle 5.1 Docker PHPUnit/Behat/PHPCS: NOT RUN in this branch application; no local Moodle 5.1 container was available in the current workspace permissions.

NOT RUN / NOT VERIFIED
- Full Moodle core PHPUnit suite was not run.
- Full Moodle core Behat suite was not run.
- Fresh install and upgrade from older released plugin versions were not manually executed.
- Manual browser accessibility, screen-reader, keyboard, RTL, target-theme, and Moodle mobile app testing were not run. These are listed in `moodle-manual-verification-checklist.md`.
- Moodle 5.1 runtime PHPUnit, Behat, and PHPCS were not run after applying this work to `MOODLE_501_STABLE`.

REQUIRED BEFORE TESTING
None.

SUGGESTED TEST PLAN
1. Fresh-install the plugin on Moodle 5.1 with developer debugging enabled; confirm no install warnings.
2. Upgrade from a pre-2026090200/pre-2026090300 plugin state with existing subcourse activities; confirm new fields default correctly and completion caches are rebuilt.
3. Upgrade a populated site with existing `completionpassgrade` use; confirm migrated records preserve expected pass/fail behavior and manual overrides are not overwritten.
4. Create a main course and referenced course with two students; complete the referenced course for one user and confirm only that user's Subcourse activity completes.
5. Enable reversible referenced-course completion and confirm the activity reverts only for the affected user when referenced-course completion is removed.
6. Leave reversible completion disabled and confirm an already completed parent activity is not reverted.
7. Set a referenced-course grade-to-pass, fetch grades manually, and confirm the Subcourse grade item carries `gradepass`.
8. Test failing, passing, no-grade, and hidden-grade cases for strict Subcourse pass-grade completion.
9. Confirm students cannot fetch grades and teachers without referenced-course grade permissions cannot select unauthorized reference courses.
10. Backup and restore activities created before and after the new fields; confirm restored records keep safe defaults.
11. Open the activity in web and Moodle app contexts; confirm intro, progress, grade, links, and external view tracking behave correctly.
12. Run scheduled `fetch_grades` and `check_completed_refcourses` tasks on multi-user data and inspect runtime/log output.

RESULT
READY FOR MOODLE TESTING WITH WARNINGS
