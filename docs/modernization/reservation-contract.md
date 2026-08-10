# Reservation surface contract (WO-002)

**Status:** Accepted for pinning by WO-005 contract tests  
**Fork tag:** `dineshsellappan18/timegrid-concierge` **1.0.0**  
**Source of truth for signatures:** `packages/timegrid-concierge/src/`

Timegrid controllers and console commands depend on the following public surface. Semantics must remain stable across the Concierge fork; WO-008 may switch Composer to the tagged fork, and WO-005 must assert these signatures and behaviours.

---

## 1. Workspace binding

```php
public function business($business): Concierge // fluent; sets workspace business + timezone
```

**Call pattern:** `$this->concierge->business($business)->…`

---

## 2. `takeReservation`

```php
public function takeReservation(array $request)
```

**Required keys on `$request`:**

| Key | Meaning |
| --- | --- |
| `issuer` | Booking issuer (user id / model as used today) |
| `service` | `Service` model with `id`, `duration` |
| `contact` | `Contact` model with `id` |
| `comments` | Optional string |
| `date` | Booking date string |
| `time` | Booking time string |
| `timezone` | Timezone for local→UTC conversion |

**Behaviour contract:**

1. Resolve vacancies via Calendar for service, duration, date, and time.
2. Return `false` when no vacancy matches.
3. Prefer first vacancy when multiple matches (current unexpected multi-match behaviour preserved).
4. Build appointment in UTC from date/time/timezone; associate vacancy; save.
5. Throw `DuplicatedAppointmentException` when `duplicates()` is true (appointment still available via `appointment()`).
6. Return the saved `Appointment` on success.

**Primary consumer:** `app/Http/Controllers/User/AgendaController.php`

---

## 3. Vacancies — `updateBatch` / `unpublish`

```php
// VacancyManager
public function updateBatch(Business $business, $parsedStatements): bool
public function unpublish() // deletes published vacancies for the workspace business
```

**Access:** `$this->concierge->vacancies()->updateBatch(...)` after `business($business)` when required by VacancyManager construction.

**`updateBatch` behaviour:**

- Groups `$parsedStatements` by `date`, then by `service` slug.
- Skips unknown service slugs (no fatal).
- Replaces vacancies for that date/service and processes capacity/time statements.
- Returns whether any change occurred (`bool`).

**Consumers:** `BusinessVacancyController`, `AutopublishBusinessVacancies`

**Parser:** `Vacancy\VacancyParser` produces `$parsedStatements` from manager/autopublish input.

---

## 4. Agenda queries

```php
public function getActiveAppointments()      // bookings()->active(), eager contact/business/service, orderBy start_at
public function getUnarchivedAppointments()  // bookings()->unarchived(), same eager loads + order
public function getUnservedAppointments()    // bookings()->unserved() — inventory completeness; pin if used
```

**Consumers:** `BusinessAgendaController`, `SendBusinessReport` (`getActiveAppointments`)

---

## 5. Availability adjacency

Availability is not a single named Concierge method on all paths; API controllers use Concierge + Concierge models (`Business`, `Service`, `Vacancy`) and app-layer `AvailabilityService`. Contract tests (WO-005) must cover:

- Vacancy query scopes used for bookable slots (`future`, `forService`, `forDate`, etc.)
- That `takeReservation` vacancy selection remains consistent with published vacancies from `updateBatch`

---

## 6. Models in the reservation path

Pinned types (namespace `Timegridio\Concierge\Models`):

- `Business`, `Appointment`, `Contact`, `Service`, `Vacancy`, `Humanresource`

Casting of `start_at` / `finish_at` must not change without an explicit hop story (edge case called out in WO-002).

---

## 7. Out of scope for this contract document

- Switching the app `composer.json` constraint (WO-008)
- Writing PHPUnit contract tests (WO-005)
- Changing reservation semantics during the floor-raise spike
