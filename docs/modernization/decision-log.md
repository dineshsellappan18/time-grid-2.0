# Modernization decision log

**Project:** Timegrid (Forge `d41ccc22-fcca-4bf3-8558-8d684ee0ee3d`)  
**Related story:** WO-001 — Inventory and triage every dependency with verdicts  
**Retrieval date:** 2026-08-10  

This log replaces informal assumptions about PHP / Laravel support windows with dates taken from primary sources on the retrieval date above. Dependency verdicts live in [dependency-triage.md](./dependency-triage.md).

---

## D-001 — Verified PHP support windows

**Decision:** Programme targets **PHP 8.3 and 8.4** as the supported interpreters for modernization (with awareness of 8.5 as newer active). PHP `>=5.6` / 7.1 local hacks are transitional only and not a target.

**Evidence** (retrieved **2026-08-10** from [php.net/supported-versions](https://www.php.net/supported-versions.php)):

| Branch | Initial release | Active support until | Security support until |
| --- | --- | --- | --- |
| 8.2 | 2022-12-08 | 2024-12-31 (ended) | 2026-12-31 |
| 8.3 | 2023-11-23 | 2025-12-31 (ended) | 2027-12-31 |
| 8.4 | 2024-11-21 | 2026-12-31 | 2028-12-31 |
| 8.5 | 2025-11-20 | 2027-12-31 | 2029-12-31 |

**Implications:**

- PHP 5.6 / 7.x / 8.0 / 8.1 are **EOL** for security and must not remain the production target.
- `eluceo/ical` latest (`2.17.0`) declares `~8.4 || ~8.5` — plan calendar library retag after the PHP 8.4 gate where needed.
- CI matrix in later WOs should cover **8.3 and 8.4** at minimum.

**Owner:** Release Engineer  
**Status:** Accepted (WO-001)

---

## D-002 — Verified Laravel release / support windows

**Decision:** End-state framework target remains **Laravel 13** (programme hops 5.3 → … → 13). Intermediate hops stay on versions that still receive security fixes only when unavoidable.

**Evidence** (retrieved **2026-08-10** from [laravel.com/docs/releases](https://laravel.com/docs/releases)):

| Version | PHP (*) | Release | Bug fixes until | Security fixes until |
| --- | --- | --- | --- | --- |
| 10 | 8.1–8.3 | 2023-02-14 | 2024-08-06 | 2025-02-04 |
| 11 | 8.2–8.4 | 2024-03-12 | 2025-09-03 | 2026-03-12 |
| 12 | 8.2–8.5 | 2025-02-24 | 2026-08-13 | 2027-02-24 |
| 13 | 8.3–8.5 | 2026-03-17 | Q3 2027 | 2028-03-17 |

Policy note from the same page: bug fixes ~18 months; security fixes ~2 years per major.

**Implications:**

- Laravel **5.3** (current) is far past EOL — every production deploy on it is an accepted risk until hops complete.
- Laravel **13** requires **PHP ≥ 8.3** — aligns with D-001.
- Do not jump versions; follow WO-017…WO-022 sequential hop stories.

**Owner:** Platform Maintainer  
**Status:** Accepted (WO-001)

---

## D-003 — Six moving-branch constraints

**Decision:** No `dev-master` / branch SHA constraints remain after Phase 1 constraint work (WO-008). Per-package verdicts:

| Package | Verdict | Follow-on WO |
| --- | --- | --- |
| `timegridio/concierge@dev-master#90e65c` | **fork** (AGPL review) | WO-002 |
| `timegridio/icalreader@dev-master` | **fork** or **remove** | WO-002 |
| `snowfire/beautymail@dev-master` | **retag** to tagged `v1.1.9`, later **replace** | WO-008 / WO-009 |
| `webpatser/laravel-countries@dev-master` | **retag** to `^2.2` | WO-008 |
| `seanstewart/plan-config@dev-master` | **remove** / inline | WO-009 |
| `codeclimate/php-test-reporter@dev-master` | **remove** | WO-009 |

**Owner:** Platform Maintainer + Release Engineer  
**Status:** Accepted (WO-001) — execution in WO-002 / WO-008 / WO-009

---

## D-004 — Explicit removals (architecture + triage)

**Decision:** The following are **removal** (not migrate) candidates; WO-009 executes removals after lockfile discipline exists:

- `jenssegers/rollbar` → Observability via Sentry (architecture)
- `codeclimate/php-test-reporter`
- `intervention/imagecache` (abandoned)
- `ipunkt/laravel-analytics` (abandoned)
- `mccool/laravel-auto-presenter` (abandoned)
- `laravelcollective/html` → **replace** with `spatie/laravel-html` (not silent remove)
- `tightenco/mailthief` (abandoned)
- `fenos/notifynder` → Laravel Notifications
- Gulp / Bower / laravel-elixir (frontend WO-043+)

**Owner:** Security Reviewer (Rollbar), Platform Maintainer (rest)  
**Status:** Accepted (WO-001)

---

## D-005 — Documentation location

**Decision:** Store modernization artefacts under `docs/modernization/` so later Forge Shipping / audit jobs can archive them without scanning the repo root.

**Artefacts created by WO-001:**

- `docs/modernization/dependency-triage.md`
- `docs/modernization/decision-log.md` (this file)

**Owner:** Release Engineer  
**Status:** Done

---

## D-006 — Concierge: fork (not reimplement), tag 1.0.0

**Decision:** **Fork and modernize** `timegridio/concierge`; do **not** reimplement the booking domain in Phase 1.

**Chosen semver tag:** `dineshsellappan18/timegrid-concierge` **1.0.0**  
**Location:** `packages/timegrid-concierge/` (in-repo organisation fork; app Composer constraint unchanged until WO-008)  
**Tree digest (SHA-256 of sorted file digests):** `3e4f573550a747321a997e10a3115a76a3019fd8aa8a65800ac2b51a00b990fd` (also in `packages/timegrid-concierge/.fork-digest`)  
**Upstream pin preserved in metadata:** `dev-master#90e65c`

**Floor-raise spike result:**

- Fork `composer.json` PHP requirement raised from `>=5.5.9` to `^8.1|^8.2|^8.3|^8.4`.
- `illuminate/support` widened transitively for hop path; abandoned `mccool/laravel-auto-presenter` dropped from the fork require set.
- Public reservation methods (`takeReservation`, `vacancies()->updateBatch`, `getActiveAppointments`, `getUnarchivedAppointments`, `business`) left signature-compatible — see [reservation-contract.md](./reservation-contract.md).
- Installation proof: fork package metadata validates; full app switch waits for WO-008 (OUT OF SCOPE here).

**Measured effort delta (spike):**

| Path | Estimated effort | Spike observation |
| --- | --- | --- |
| **Fork + floor raise + tag** (chosen) | ~2–4 engineer-weeks to production-ready PHP 8.3/8.4 Concierge, then WO-005/008 | Inventory + MIT-preserving fork + docs completed in this story (~1 day documentation + package copy spike); remaining work is Rector/PHPStan clean-up on the fork during hops |
| **Reimplement booking engine** | ~8–12 engineer-weeks (models, vacancy DSL, calendar strategies, appointment lifecycle, test parity) | Rejected: 3–4× effort, expands Phase 2 into a domain rewrite, higher risk to availability maths |

**icalreader note (from D-003):** `timegridio/icalreader@dev-master` remains **fork or remove** under supply-chain work; not blocking Concierge fork adoption. Prefer remove/replace during calendar modernization if unused critically; otherwise fork with a tag in WO-008 scope.

**AGPL:** See [agpl-review.md](./agpl-review.md) — hosted fork **approved**; Concierge remains MIT; application AGPL obligations listed.

**Owner:** Platform Maintainer + Release Engineer + Legal (SaaS source-offer follow-up)  
**Status:** Accepted (WO-002)

---

## Change control

| Date | Change | Author |
| --- | --- | --- |
| 2026-08-10 | Initial log + verified PHP/Laravel windows; moving-branch and removal decisions | WO-001 implementation |
| 2026-08-10 | D-006 Concierge fork v1.0.0 + AGPL review + reservation contract | WO-002 implementation |
