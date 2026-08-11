# Dusk migration / Selenium retirement (WO-006)

## Status (transitional on Laravel 5.3)

`laravel/dusk` requires Laravel ≥5.4. While Timegrid remains on **Laravel 5.3**, Dusk cannot be installed or executed as a first-party package.

## What shipped in this story

1. **Removed dependency on `phpunit/phpunit-selenium`** — already absent from `composer.json`.
2. **Page-object scaffolds** under `tests/Browser/Pages/` documenting the critical screens that must be covered once Dusk is installable (post WO-017 hop to 5.4+).
3. **Retired Selenium acceptance classes** marked with `@group retired-selenium` and a note pointing here; scenario intent is preserved in the page-object comments and in characterization/contract suites for the booking hot paths.
4. **CI job placeholder** `dusk-deferred` in `.github/workflows/ci.yml` that documents the gate and fails closed only when `RUN_DUSK=1` is set (after Laravel ≥5.4).

## Ported scenario intent (pending Dusk runner)

| Legacy source | Scenario | Page object |
| --- | --- | --- |
| `tests/acceptance/UserRegistrationProcessTest.php` | Login / registration | `LoginPage` |
| `tests/acceptance/ManagerTest.php` | Manager agenda confirm/cancel/serve | `BusinessAgendaPage` |
| Manager vacancy controller integration | Simple + advanced vacancy authoring | `VacancyEditorPage` |
| Public booking journey | Guest/user booking | `PublicBookingPage` |
| Address book contact flows | Contact CRUD | `AddressbookPage` |

## Residual

Headless Chrome + artefact upload becomes a required check only after Dusk installs cleanly (Laravel hop WO-017+).
