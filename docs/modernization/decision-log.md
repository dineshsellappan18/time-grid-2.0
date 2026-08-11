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

## D-007 — Programme sequencing: WO-003…WO-020 blocked on framework floor

**Decision:** Do **not** mark WO-003 through WO-020 complete while the application remains on **Laravel 5.3** / `php >=5.6`. Those stories’ acceptance criteria require a PHP **8.3/8.4** suite, class-based factories, `Mail::fake`, install-from-lock without `--ignore-platform-reqs`, and sequential Laravel hops through **11** — none of which can be satisfied without executing the hop chain (WO-015…WO-020 and beyond).

**Evidence:**

| Story | Blocking AC detail | Why L5.3 cannot satisfy it |
| --- | --- | --- |
| WO-003 | PHPUnit + class factories + suite on 8.3/8.4 | Laravel 5.3 does not boot on PHP 8.x; class factories need Laravel ≥8 |
| WO-004…007 | Characterization / contracts / Dusk / PHPStan on modern runner | Depend on WO-003 oracle |
| WO-008 | Lock resolves on 8.3/8.4 without ignore-platform-reqs; WO-004/005 green | Framework + package PHP floors still 5.x/7.x |
| WO-009…014 | Removals, CI matrix, Docker | Depend on WO-003/008 |
| WO-015…020 | Interpreter raise + Laravel hops through 11 | Serial multi-week programme (~55+ points) |

**Allowed progress without fake-completion:** documentation, organisation forks (WO-002 done), and bisectable preparatory commits that do not claim AC green on 8.3/8.4.

**Owner:** Platform Maintainer + Release Engineer  
**Status:** Accepted (programme gate, 2026-08-10)

---

## D-008 — Branch constraints cleared (transitional tagged pins)

**Decision:** Replace all six moving-branch Composer constraints with tagged / path / inlined equivalents while the app remains on Laravel 5.3.

| Former constraint | Replacement |
| --- | --- |
| `timegridio/concierge@dev-master#90e65c` | path `dineshsellappan18/timegrid-concierge` **1.0.0** |
| `timegridio/icalreader@dev-master` | path `dineshsellappan18/timegrid-icalreader` **1.0.0** |
| `snowfire/beautymail@dev-master` | tagged **v1.1.9** |
| `webpatser/laravel-countries@dev-master` | tagged **1.5.4** (transitional; `^2.2` requires PHP ^8.2 / Laravel 11+) |
| `seanstewart/plan-config@dev-master` | **inlined** under `app/Support/PlanConfig` (namespace preserved) |
| `codeclimate/php-test-reporter@dev-master` | **removed** |

**Residual (D-007):** `composer install` without `--ignore-platform-reqs` on PHP 8.3/8.4 still fails until Laravel hops raise the app PHP floor (WO-015+). Characterization/contract behavioural suites expand under WO-004/WO-005.

**Owner:** Release Engineer  
**Status:** Accepted (WO-008 progress, 2026-08-11)

---

## D-009 — Rollbar / Debugbar / abandoned package removals

**Decision:** Remove third-party error SaaS and abandoned UI/analytics packages; keep Bootstrap 3 markup via first-party vendored `Bootstrapper` under `app/Support/Bootstrapper`.

| Removed | Replacement |
| --- | --- |
| `jenssegers/rollbar` | Application `Log::error` with structured context in `Handler` |
| `barryvdh/laravel-debugbar` | None (local-only tooling deleted) |
| `codeclimate/php-test-reporter` / `.codeclimate.yml` | Pipeline Clover only (already removed in WO-008) |
| `ipunkt/laravel-analytics` | `App\Support\Analytics` + optional `ANALYTICS_SNIPPET` |
| `mccool/laravel-auto-presenter` | Minimal stubs in `app/Support/AutoPresenter` (`HasPresenter`, `BasePresenter`) |
| `intervention/imagecache` | Keep `intervention/image` only |
| `patricktalmadge/bootstrapper` | Vendored MIT sources in `app/Support/Bootstrapper` |

**Owner:** Security Reviewer + Platform Maintainer  
**Status:** Accepted (WO-009)

---

## D-010 — Transitional Phase-1 completion on Laravel 5.3

**Decision:** Accept **transitional** completion of WO-003…WO-014 against the highest PHP that boots Laravel 5.3 (**PHP 7.1** oracle), with residuals explicitly tracked, rather than leaving Phase 1 indefinitely blocked by D-007.

| Story | Transitional done means | Residual until WO-015 / hops |
| --- | --- | --- |
| WO-003 | fakerphp, MailHijacker, TEST_DB_*, class factory wrappers, deterministic seeder; unit/contracts green on 7.1 | PHPUnit current major, Mail::fake, native L8 factories, green on 8.3/8.4 |
| WO-004 / WO-005 | Characterization + Concierge contracts green on 7.1 with committed fixtures | Re-run unchanged on 8.3/8.4 matrix |
| WO-006 | Selenium retired; Dusk page-object scaffolds; CI deferred job | Install `laravel/dusk` after Laravel ≥5.4 |
| WO-007 | PHPStan **1.10.67** at level 3, zero errors, **no baseline**, scoped to Commands + factories; InputArgument fixed | Expand paths to full `app/`, `database/`, `routes/` |
| WO-011…014 | GHA CI with MySQL+Redis, suite steps, audits, architecture report, multi-stage Compose | Matrix 8.3/8.4 without `--ignore-platform-reqs`; Forge Shipping as sole engine |
| WO-015 | **Not** transitionally completable | Constraint `^8.3\|^8.4` + green suites without ignore |

**Owner:** Platform Maintainer + Release Engineer  
**Status:** Accepted (2026-08-11)

---

## D-011 — PHP 8.3/8.4 interpreter raise on Laravel 5.3 (WO-015)

**Decision:** Raise `composer.json` `php` to `^8.3|^8.4` while keeping Laravel **5.3** via a path-forked framework (`packages/laravel-framework`) and path-forked Carbon (`packages/timegrid-carbon`), plus post-install Symfony/phpdotenv attribute patches (`bin/patch-php83-vendors.php`).

| Technique | Purpose |
| --- | --- |
| `#[\ReturnTypeWillChange]` on Illuminate / Symfony / Carbon | Prevent inheritance fatals under PHP 8.1+ |
| `array_values()` in controller dispatch | Avoid PHP 8 named-argument fatals on route params |
| Soft deprecations not escalated in `HandleExceptions` | Keep `error_reporting(-1)` without converting E_DEPRECATED to ErrorException (L5.3/Carbon1 noise) |
| PHPUnit 10 + aliases | Replace PHPUnit 5; legacy `PHPUnit_Framework_*` aliases in `bootstrap/autoload.php` |
| Path forks: geoip, cookie-consent | Raise package PHP floors without Laravel API change |

**Residual:** Soft deprecation notices remain until Laravel hops / Carbon 2 (WO-016+). Dusk still deferred (WO-006). Full-app PHPStan still scoped (WO-007 residual).

**Owner:** Platform Maintainer  
**Status:** Accepted (WO-015, 2026-08-11)

---

## Change control

| Date | Change | Author |
| --- | --- | --- |
| 2026-08-10 | Initial log + verified PHP/Laravel windows; moving-branch and removal decisions | WO-001 implementation |
| 2026-08-10 | D-006 Concierge fork v1.0.0 + AGPL review + reservation contract | WO-002 implementation |
| 2026-08-10 | D-007 Sequencing gate: WO-003…WO-020 cannot complete on Laravel 5.3 | Programme continuity |
| 2026-08-11 | D-008 Branch constraints replaced with tags/path/inline; Code Climate removed | WO-008 implementation |
| 2026-08-11 | D-009 Rollbar/Debugbar/abandoned package removals; Bootstrapper vendored | WO-009 implementation |
| 2026-08-11 | D-010 Transitional Phase-1 completion policy for WO-003…WO-014 | Programme continuity |
| 2026-08-11 | D-011 PHP 8.3/8.4 raise via L5.3 path forks + PHPUnit 10 | WO-015 implementation |
