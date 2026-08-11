# iCal Guard Cutover — Evidence Pack

## Overview

This document records the evidence required to transition the iCal token guard from
**shadow mode** to **enforced mode** in production.

**Cutover criteria (all must pass):**

1. Zero `ical.token.divergence` entries over the final 7 consecutive days
2. Feed availability for valid subscriptions >= 99% across the shadow window
3. This evidence pack published and signed off
4. Rollback rehearsed and execution time < 2 minutes (MTTR target)

---

## Shadow Window Summary

| Metric | Value | Status |
|--------|-------|--------|
| Shadow start date | _YYYY-MM-DD_ | — |
| Shadow end date | _YYYY-MM-DD_ | — |
| Total shadow days | _14+_ | — |
| Total feed requests | _N_ | — |
| Successful feed requests | _N_ | — |
| Feed availability | _XX.XXX%_ | PASS / FAIL |
| Total divergences | _N_ | — |
| Divergences in final 7 days | _0_ | PASS / FAIL |

---

## Daily Divergence Table

Generate with: `php artisan ical:divergence-report --days=14`

| Date | Divergence Count | Notes |
|------|-----------------|-------|
| _YYYY-MM-DD_ | 0 | — |

---

## Denial Breakdown

| Reason | Count | Assessment |
|--------|-------|-----------|
| malformed_token | _N_ | Expected (scanners, bots) |
| invalid_token | _N_ | Expected (rotated/old tokens) |
| unknown_business | _N_ | Expected (404 probes) |
| divergence | 0 | Required: zero in final 7 days |

---

## Feed Availability

**Formula:** `successful_2xx / total_valid_subscription_requests × 100`

- Excludes malformed (400) and unknown-business (404) from the denominator
- Only counts requests with a backfilled or service-issued token as "valid subscription"

---

## Rollback Rehearsal

| Step | Action | Expected Time |
|------|--------|--------------|
| 1 | Change `ICAL_GUARD_MODE=shadow` in environment config | < 30s |
| 2 | Config cache clear or process restart | < 60s |
| 3 | Verify shadow mode active (check next feed request log) | < 30s |
| **Total MTTR** | | **< 2 minutes** |

**Rehearsal record:**

| Date | Operator | Actual MTTR | Pass/Fail |
|------|----------|-------------|-----------|
| _YYYY-MM-DD_ | _name_ | _Xm Ys_ | — |

---

## Sign-Off

| Role | Name | Date | Decision |
|------|------|------|----------|
| Security Reviewer | — | — | APPROVE / REJECT |
| Engineering Lead | — | — | APPROVE / REJECT |
| Operations | — | — | APPROVE / REJECT |

---

## Post-Cutover Observation

After flipping to enforced mode, observe for 48 hours:

| Metric | Baseline (shadow) | Post-cutover | Delta | Status |
|--------|-------------------|--------------|-------|--------|
| Feed success rate | _XX%_ | _XX%_ | — | — |
| 400 count/day | _N_ | _N_ | — | — |
| 403 count/day | _N_ | _N_ | — | — |
| 404 count/day | _N_ | _N_ | — | — |
| Unexpected denials | 0 | _N_ | — | PASS / FAIL |

**Rollback trigger:** Feed availability drops below 99% → immediately revert to shadow.

---

## Cutover Command

```bash
# Flip to enforced mode (configuration only, no redeploy)
export ICAL_GUARD_MODE=enforced

# Verify (check application logs for next feed request)
php artisan ical:divergence-report --days=1

# Rollback if needed
export ICAL_GUARD_MODE=shadow
```
