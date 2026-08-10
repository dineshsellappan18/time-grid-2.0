# Concierge usage inventory (WO-002)

**Retrieval date:** 2026-08-10  
**Upstream package:** `timegridio/concierge@dev-master#90e65c`  
**Organisation fork:** `packages/timegrid-concierge` → Composer name `dineshsellappan18/timegrid-concierge` **v1.0.0**

This inventory lists every `Timegridio\Concierge` surface consumed by the Timegrid application. The reservation contract in [reservation-contract.md](./reservation-contract.md) pins the booking-critical methods; this file is the full coupling map.

---

## Entry-point construction

| Consumer | How Concierge is obtained |
| --- | --- |
| `app/Http/Controllers/API/AvailabilityController.php` | Constructor injection `Concierge` |
| `app/Http/Controllers/API/BookingController.php` | Constructor injection `Concierge` |
| `app/Http/Controllers/User/AgendaController.php` | Constructor injection `Concierge` |
| `app/Http/Controllers/Manager/BusinessAgendaController.php` | Constructor injection `Concierge` |
| `app/Http/Controllers/Manager/BusinessVacancyController.php` | Constructor injection `Concierge` |
| `app/Http/Controllers/User/BusinessController.php` | Constructor injection `Concierge` |
| `app/Console/Commands/AutopublishBusinessVacancies.php` | Constructor injection `Concierge` |
| `app/Console/Commands/SendBusinessReport.php` | Constructor injection `Concierge` |

---

## Reservation / vacancy methods (contract surface)

| Method | Call sites |
| --- | --- |
| `business($business)` | Chained before booking/agenda/vacancy calls |
| `takeReservation(array $request)` | `User/AgendaController` |
| `vacancies()->updateBatch($business, $parsed)` | `BusinessVacancyController`, `AutopublishBusinessVacancies` |
| `vacancies()->unpublish()` | `BusinessVacancyController` |
| `getActiveAppointments()` | `BusinessAgendaController`, `SendBusinessReport` |
| `getUnarchivedAppointments()` | `BusinessAgendaController` |
| Calendar / availability via Concierge | `API/AvailabilityController`, `API/BookingController` |

Supporting types on the reservation path:

- `Timegridio\Concierge\Vacancy\VacancyParser` — vacancy publish/autopublish
- `Timegridio\Concierge\Exceptions\DuplicatedAppointmentException` — `AgendaController`

---

## Models (Eloquent domain)

Consumed across controllers, policies, events, listeners, jobs, and `app/TG/*`:

- `Models\Business`, `Models\Appointment`, `Models\Contact`, `Models\Service`
- `Models\Vacancy`, `Models\Humanresource` / `HumanResource`, `Models\ServiceType`
- `Models\Category`, `Models\Domain`

Policies bind Concierge models in `AuthServiceProvider`. Preference keys use the FQCN `Timegridio\Concierge\Models\Business`.

---

## Verdict for inventory

The booking engine is the highest-coupling node: changing Concierge method signatures or model casting for `start_at` / `finish_at` would simultaneously break booking, availability, and vacancy authoring. The fork must preserve the public reservation surface listed above without semantic change during the spike (enforced later by WO-005 contract tests).
