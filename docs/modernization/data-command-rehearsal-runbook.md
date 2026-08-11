# Data Command Rehearsal Runbook

## Purpose

Rehearse `preferences:remap-keys` and `notifications:seed-categories` against an
anonymized production snapshot to prove correctness, idempotence, and time-boundedness
before the production run.

## Prerequisites

- [ ] WO-035 (`db:anonymize`) completed and merged
- [ ] WO-037 (`preferences:remap-keys`, `notifications:seed-categories`) completed and merged
- [ ] Production snapshot available (< 24 hours old)
- [ ] Isolated rehearsal environment provisioned (no outbound mail, no queue processing)
- [ ] Integration test suite green on current main

## Procedure

### Step 1 — Restore Snapshot

```bash
# Restore the production snapshot into the isolated rehearsal database
mysql -h $REHEARSAL_DB_HOST -u $REHEARSAL_DB_USER -p$REHEARSAL_DB_PASS $REHEARSAL_DB_NAME < production_snapshot.sql

# Verify restore
mysql -h $REHEARSAL_DB_HOST -u $REHEARSAL_DB_USER -p$REHEARSAL_DB_PASS $REHEARSAL_DB_NAME \
  -e "SELECT COUNT(*) as users FROM users; SELECT COUNT(*) as contacts FROM contacts; SELECT COUNT(*) as preferences FROM preferences; SELECT COUNT(*) as notification_categories FROM notification_categories;"
```

**Record:** Snapshot date, restore duration, row counts.

### Step 2 — Anonymize

```bash
# CRITICAL: Anonymize BEFORE any inspection
php artisan db:anonymize --force

# Attach the anonymization report to this document
```

**Record:** Anonymization duration, rows changed per table.

**Gate:** No inspection of data may proceed until this step completes successfully.

### Step 3 — Capture Baseline Counts

```bash
# Record pre-command state
mysql -h $REHEARSAL_DB_HOST -u $REHEARSAL_DB_USER -p$REHEARSAL_DB_PASS $REHEARSAL_DB_NAME -e "
  SELECT 'preferences' as tbl, COUNT(*) as total,
    SUM(CASE WHEN \`key\` = 'appointment_annulation_pre_hs' THEN 1 ELSE 0 END) as legacy_annulation,
    SUM(CASE WHEN \`key\` = 'annulation_policy_advice' THEN 1 ELSE 0 END) as legacy_advice,
    SUM(CASE WHEN \`key\` = 'appointment_cancellation_pre_hs' THEN 1 ELSE 0 END) as new_cancellation,
    SUM(CASE WHEN \`key\` = 'cancellation_policy_advice' THEN 1 ELSE 0 END) as new_advice
  FROM preferences;

  SELECT 'notification_categories' as tbl, COUNT(*) as total FROM notification_categories;
  SELECT name, text FROM notification_categories ORDER BY name;
"
```

### Step 4 — Dry Run

```bash
# Dry run both commands
php artisan preferences:remap-keys --dry-run --force
php artisan notifications:seed-categories --dry-run --force
```

**Record:** Projected changes from dry-run output.

### Step 5 — Execute for Real

```bash
# Time the real execution
time php artisan preferences:remap-keys --force
time php artisan notifications:seed-categories --force
```

**Record:** Wall-clock duration, rows changed, any errors.

### Step 6 — Capture After Counts

```bash
# Record post-command state (same query as Step 3)
mysql -h $REHEARSAL_DB_HOST -u $REHEARSAL_DB_USER -p$REHEARSAL_DB_PASS $REHEARSAL_DB_NAME -e "
  SELECT 'preferences' as tbl, COUNT(*) as total,
    SUM(CASE WHEN \`key\` = 'appointment_annulation_pre_hs' THEN 1 ELSE 0 END) as legacy_annulation,
    SUM(CASE WHEN \`key\` = 'annulation_policy_advice' THEN 1 ELSE 0 END) as legacy_advice,
    SUM(CASE WHEN \`key\` = 'appointment_cancellation_pre_hs' THEN 1 ELSE 0 END) as new_cancellation,
    SUM(CASE WHEN \`key\` = 'cancellation_policy_advice' THEN 1 ELSE 0 END) as new_advice
  FROM preferences;

  SELECT 'notification_categories' as tbl, COUNT(*) as total FROM notification_categories;
  SELECT name, text FROM notification_categories ORDER BY name;
"
```

**Assertions:**
- [ ] Legacy key counts are now 0
- [ ] New key counts equal (old legacy + previously existing new)
- [ ] Total preference row count unchanged
- [ ] Notification categories total = 10 (or more if custom categories exist)
- [ ] All 10 required categories present

### Step 7 — Prove Idempotence

```bash
# Second run must be a no-op
php artisan preferences:remap-keys --force
php artisan notifications:seed-categories --force
```

**Assertions:**
- [ ] `preferences:remap-keys` reports 0 rows changed
- [ ] `notifications:seed-categories` reports 0 categories created

### Step 8 — Anomaly Review

Document any anomalies found:

| # | Anomaly | Decision | Action |
|---|---------|----------|--------|
| 1 | Legacy keys not in KEY_MAP | | |
| 2 | Orphaned polymorphic preference rows | | |
| 3 | Duplicate notification categories (case differences) | | |
| 4 | Volume exceeding maintenance window | | |

**Gate:** All anomalies must have a documented decision before sign-off.

## Timing Summary

| Step | Duration | Notes |
|------|----------|-------|
| Restore | | |
| Anonymize | | |
| preferences:remap-keys | | |
| notifications:seed-categories | | |
| **Total** | | |

## Production Run Plan

- **Maintenance window:** [TBD based on rehearsal timing + 2x buffer]
- **Rollback procedure:** Restore from pre-run snapshot
- **Estimated rollback duration:** [TBD]
- **Notification to users:** [TBD — session reset expected]

## Sign-off

| Role | Name | Date | Signature |
|------|------|------|-----------|
| Maintainer | | | |
| Release Engineer | | | |
| Security/Compliance | | | |

---

**Status:** TEMPLATE — Awaiting rehearsal execution with production snapshot.
