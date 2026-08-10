# Dependency triage — Timegrid modernization

**Story:** WO-001  
**Retrieval date:** 2026-08-10  
**Sources:** `composer.json` (authoritative list), [Packagist](https://packagist.org) package metadata  
**Scope:** Inventory + verdicts only. No constraint or code changes (see WO-008 / WO-009).

## Verdict legend

| Verdict | Meaning |
| --- | --- |
| **keep** | Compatible path exists; retain with later retag during hops |
| **retag** | Stay on package, pin a tagged release (leave `dev-*`) |
| **replace** | Swap for a maintained successor with API migration |
| **remove** | Drop from the stack |
| **fork** | Bring in-repo / under org control (domain packages) |

**Owners:** Platform Maintainer (PM), Release Engineer (RE), Security Reviewer (SR)  
**Timebox:** Phase 1 unless noted

---

## Moving-branch constraints (must resolve)

| Package | Current constraint | Latest tagged | PHP 8.3/8.4 | Laravel 13 | Maintenance | Verdict | Owner | Timebox |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| `timegridio/concierge` | `dev-master#90e65c` | *(no Packagist tags)* | No (legacy) | No | Unmaintained / private domain | **fork** | PM | WO-002 · 2 weeks |
| `timegridio/icalreader` | `dev-master` | *(no Packagist tags)* | No | No | Unmaintained | **fork** or **remove** if superseded by `eluceo/ical` only | PM | WO-002 · 1 week |
| `snowfire/beautymail` | `dev-master` | `v1.1.9` (2025-03-06) | Partial (`>=7.0`) | No (Laravel 5-era) | Low activity | **retag** then **replace** (Beautymail → Mailable/Markdown) | RE | Phase 1 · 1 week + Phase 2 replace |
| `webpatser/laravel-countries` | `dev-master` | `v2.2.0` (2026-07-14, `^8.2`) | Yes | Likely with Laravel 8+ line | Active | **retag** to `^2.2` | RE | WO-008 · 3 days |
| `seanstewart/plan-config` | `dev-master` | `1.5.0` (2019-12-12) | No (`>=7.0` only historically) | No | Stale | **remove** / inline config | PM | WO-009 · 1 week |
| `codeclimate/php-test-reporter` | `dev-master` | `v0.4.4` (2017, PHP `^5.3\|\|^7.0`) | No | N/A (dev) | Obsolete | **remove** | RE | WO-009 · 1 day |

---

## `require` inventory

| Package | Current constraint | Latest tagged | PHP 8.3/8.4 | Laravel 13 | Maintenance | Verdict | Owner | Timebox |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| `php` | `>=5.6` | n/a | Target 8.3–8.4 | n/a | Active | **retag** → `^8.3` (WO-015) | RE | Phase 1 |
| `laravel/framework` | `5.3.*` | `v13.24.0` (`^8.3`) | Yes (on L13) | Yes | Active | **keep** path via sequential hops (WO-017…WO-022) | PM | Programme critical path |
| `anhskohbo/no-captcha` | `2.*` | `3.8.0` | Yes (`>=5.5.5`) | Needs L13-compatible major | Active | **retag** / bump major during hops | RE | During L8+ hops |
| `barryvdh/laravel-snappy` | `~0.2` | `v1.0.5` (`^8.1`) | Yes | Check L13 bind | Active | **retag** | RE | Mid hops |
| `bassjobsen/bootstrap-3-typeahead` | `~4.0` | `4.0.2` (2016) | n/a (asset) | n/a | Stale | **remove** with Bootstrap 3 (WO-044/045) | PM | Frontend phase |
| `creativeorange/gravatar` | `~1.0` | `v1.0.26` | Yes | Likely | Active | **keep** / retag | RE | Mid hops |
| `fenos/notifynder` | `^3.0` | `4.3.0` (2017) | Unlikely | No | Stale | **replace** → Laravel Notifications | PM | WO-009 + L hops |
| `graham-campbell/markdown` | `^6.0` | `v16.1.0` (`^8.1`) | Yes | Likely | Active | **retag** across majors | RE | Mid hops |
| `guzzlehttp/guzzle` | `~6.0` | `8.0.2` | Yes | Yes (via Laravel) | Active | **retag** with Laravel hops | RE | With framework hops |
| `intervention/image` | `^2.2` | `4.2.1` (`^8.3`) | Yes | Yes | Active | **retag** to v3/v4 | RE | Mid hops |
| `intervention/imagecache` | `^2.2` | `2.6.0` | Partial | No | **Abandoned** | **remove** | PM | WO-009 · 3 days |
| `ipunkt/laravel-analytics` | `~1.1` | `4.1.0` | Partial | No | **Abandoned** | **remove** or replace with first-party GA snippet | PM | WO-009 · 3 days |
| `laracasts/flash` | `~2.0` | `3.2.6` | Yes | Likely | Active | **retag** | RE | Mid hops |
| `laravel/socialite` | `~2.0` | `v5.29.0` | Yes | Yes | Active | **retag** with hops | RE | Mid hops |
| `laravelcollective/html` | `~5.2` | `v6.4.1` → abandoned → `spatie/laravel-html` | No path on Collective | No | **Abandoned** | **replace** → `spatie/laravel-html` | PM | WO-009 · 1 week |
| `mccool/laravel-auto-presenter` | `^4.0` | `7.7.0` | Partial | No | **Abandoned** | **remove** (use API Resources / presenters in-app) | PM | WO-009 · 1 week |
| `patricktalmadge/bootstrapper` | `~5` | `5.12.0` (2020) | No for L13 | No | Stale | **remove** with Bootstrap migration | PM | Frontend phase |
| `pid/speakingurl` | `^0.11.0` | `0.11.0` (2014) | n/a | n/a | Stale | **replace** → `cocur/slugify` or JS slugify | RE | Mid hops |
| `propaganistas/laravel-phone` | `~2.0` | `6.0.3` (`^8.2`) | Yes | Likely | Active | **retag** | RE | Mid hops |
| `sorich87/bootstrap-tour` | `^0.10.2` | `v0.12.0` (2017) | n/a | n/a | Stale | **replace** / remove with UI rewrite | PM | Frontend phase |
| `stevebauman/location` | `~2.0` | `v7.6.3` (`>=8.1`) | Yes | Likely | Active | **retag** | RE | Mid hops |
| `twitter/typeahead.js` | `^0.11.1` | `v0.11.1` (2015) | n/a | n/a | Stale | **remove** / replace with modern autocomplete | PM | Frontend phase |
| `jenssegers/agent` | `^2.3` | `v2.6.4` (2020) | Borderline | Unlikely | Low | **replace** → `mobiledetect/mobiledetectlib` direct or drop | RE | Mid hops |
| `laracasts/utilities` | `^2.1` | `3.2.5` | Yes | Likely | Active | **retag** | RE | Mid hops |
| `alariva/tidiochat` | `^2.0` | `2.1.1` (2019) | Unlikely | No | Low | **remove** or thin Blade include | PM | WO-009 · 2 days |
| `jenssegers/rollbar` | `^1.5` | `v1.5.1` (2017) | No | No | Stale | **remove** → Sentry (architecture) | SR | WO-009 · 3 days |
| `eluceo/ical` | `^0.11` | `2.17.0` (`~8.4\|\|~8.5`) | **8.4+ preferred** | n/a (lib) | Active | **retag** carefully (major API break 0.x→2.x) | RE | After PHP 8.4 gate / with WO-026 |
| `spatie/laravel-cookie-consent` | `^1.2` | `3.5.0` (`^8.2`) | Yes | Likely | Active | **retag** | RE | Mid hops |
| `torann/geoip` | `1.0.*` | `3.0.10` (`^8.0`…`^8.5`) | Yes | Likely | Active | **retag** | RE | Mid hops |
| `geoip2/geoip2` | `~2.1` | `v3.4.0` (`>=8.1`) | Yes | n/a | Active | **retag** | RE | Mid hops |
| `nesbot/carbon` | `^1.22` | `3.13.2` (`^8.1`) | Yes | Yes (L13 ships Carbon 3) | Active | **keep** via framework hops | RE | With L hops |
| `jackiedo/timezonelist` | `^5.0` | `5.1.3` (2022) | Unclear for L13 | Unlikely | Low | **replace** with static timezone list / Carbon | RE | Mid hops |

*(Moving-branch packages are also listed in the section above; do not double-count in coverage checks.)*

---

## `require-dev` inventory

| Package | Current constraint | Latest tagged | PHP 8.3/8.4 | Laravel 13 | Maintenance | Verdict | Owner | Timebox |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| `barryvdh/laravel-debugbar` | `^2.0` | `v4.4.1` (`^8.2`) | Yes | Likely | Active | **retag** | RE | Mid hops |
| `caouecs/laravel-lang` | `~3.0` | `7.0.3` → moved to Laravel-Lang | n/a | Use `laravel-lang/lang` | **Redirected / abandoned path** | **replace** → `laravel-lang/common` | RE | WO-003 · 3 days |
| `codeclimate/php-test-reporter` | `dev-master` | `v0.4.4` | No | N/A | Obsolete | **remove** | RE | WO-009 · 1 day |
| `fzaninotto/faker` | `~1.0` | `v1.9.2` | No (EOL) | N/A | **Abandoned** | **replace** → `fakerphp/faker` | RE | WO-003 · 2 days |
| `laracasts/generators` | `^1.1` | `2.0.2` | Unclear | No for L13 | Low | **remove** (use built-in generators) | RE | WO-003 |
| `phpunit/phpunit` | `~5.0` | `13.3.0` (`>=8.4.1`) | Yes (modern) | N/A | Active | **retag** via Pest/PHPUnit plan (WO-003) | RE | WO-003 · 2 weeks |
| `phpunit/phpunit-selenium` | `~3.0` | `9.0.1` (2020) | Partial | N/A | Stale | **remove** → Laravel Dusk (WO-006) | RE | WO-006 |
| `potsky/laravel-localization-helpers` | `2.4.*` | `v2.6.6` (2017) | No | No | Stale | **remove** | RE | WO-009 |
| `symfony/css-selector` | `~3.0` | `v8.1.0` | Yes | Via framework | Active | **retag** with Symfony components from Laravel | RE | With hops |
| `symfony/dom-crawler` | `~3.0` | `v8.1.1` | Yes | Via framework | Active | **retag** with hops | RE | With hops |
| `mockery/mockery` | `0.9.*` | `1.6.12` | Yes | N/A | Active | **retag** | RE | WO-003 |
| `tightenco/mailthief` | `~0.3` | `v0.3.14` | No | No | **Abandoned** | **remove** → Mail fake / Mailpit | RE | WO-003 · 2 days |

---

## Frontend / build tooling (not in composer, architecture removal candidates)

| Tool | Status | Verdict | Owner | Timebox |
| --- | --- | --- | --- | --- |
| Gulp 3 + laravel-elixir | EOL | **remove** → Vite (WO-043) | PM | Frontend phase |
| Bower | EOL | **remove** → npm (WO-043) | PM | Frontend phase |
| Bootstrap 3 / AdminLTE 2 / jQuery | EOL | **replace** (WO-044/045) | PM | Frontend phase |

---

## Tooling pins (recommended for later WOs — record only)

| Tool | Suggested pin rationale | Owner |
| --- | --- | --- |
| PHPStan | Level 3 gate in WO-007; pin a single minor (e.g. `^1.12` or project-chosen) once PHP 8.3 CI exists | RE |
| Rector + rector-laravel | Pair versions known-good for the active Laravel hop; avoid floating `dev-main` | RE |
| Pest / PHPUnit | Chosen in WO-003; do not mix PHPUnit 5 fixtures with PHP 8 runners | RE |

---

## Completeness check

- Composer `require` keys (excl. `php`): **36** packages inventoried (including 6 moving-branch).  
- Composer `require-dev` keys: **12** packages inventoried.  
- Every row has a verdict, owner, and timebox.  
- Constraint/code changes deferred to **WO-008** / **WO-009**.
