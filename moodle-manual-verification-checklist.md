# Moodle Subcourse Manual Verification Checklist

This checklist tracks the warnings that cannot be fully closed by static checks,
PHPUnit, or Behat alone. Execute it in a Moodle 5.1 test site with developer
debugging enabled.

## Accessibility and theme rendering

- [ ] Open a Subcourse activity as a student with referenced-course progress.
      Confirm the native progress element is visible, labelled by the adjacent
      progress text, and does not rely on inline styles.
- [ ] Test the same activity in Boost and the target production theme.
      Confirm the progress bar, current grade, and action link do not overlap on
      desktop or mobile widths.
- [ ] Navigate the view page by keyboard only. Confirm focus order reaches the
      referenced-course action link and no hidden control traps focus.
- [ ] Test with browser zoom at 200 percent. Confirm progress, grade text, and
      the referenced-course link remain readable.

## Moodle App and external-service rendering

- [ ] Open the Subcourse activity in the Moodle App or mobile service preview.
      Confirm `mobile_view_latest.mustache` renders the expected activity view.
- [ ] Confirm the external view call records the view event and returns no
      warnings for an enrolled student with access.
- [ ] Confirm a student without access cannot fetch restricted Subcourse data
      through the external service.

## Upgrade and operational scale

- [ ] Upgrade a site with existing Subcourse activities that used Moodle core
      pass-grade completion. Confirm the new Subcourse pass-grade rule is set and
      the core course-module pass-grade fields are cleared.
- [ ] Rehearse the upgrade on a copy with representative completion volume.
      Confirm the streaming recordset migration completes within the local
      maintenance window.
- [ ] Run the scheduled grade fetch task on a representative course set. Confirm
      runtime and memory use are acceptable for the expected site size.

## Backup and restore

- [ ] Restore a current backup containing Subcourse pass-grade completion.
      Confirm the completion settings and grade item are restored correctly.
- [ ] Restore an older backup that predates pass-grade completion. Confirm the
      restored activity defaults remain compatible and developer debugging is
      clean.

## Completion semantics

- [ ] As a student with a passing fetched grade, confirm pass-grade completion can
      complete the parent Subcourse activity even when referenced-course
      completion is not complete.
- [ ] As a student with a failing fetched grade, confirm the parent Subcourse
      activity remains incomplete.
- [ ] Confirm manual completion overrides are preserved by grade sync, scheduled
      sync, and upgrade migration.
