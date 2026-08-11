# Parity exceptions (WO-004 characterization)

Locked-in behaviours observed by the characterization suite in `tests/characterization/`. These are **intentionally preserved** during modernization unless a work order explicitly changes them.

---

## Availability

### `endDate` is mutated by disabled-date calculation

`AvailabilityController::getDates()` passes `$endDate` by reference into `getDisabledDates()`, which calls `$end->addDay()`. The JSON `endDate` is therefore **one day later** than `startDate + availability_future_days`.

- Frozen oracle: `startDate=2024-06-16`, `availability_future_days=7` → `endDate=2024-06-24` (not `2024-06-23`).

### `AvailabilityService` slot loop uses inclusive upper bound

`addSlots()` iterates `for ($i = 0; $i <= $maxNumberOfSlots; $i++)` where `$maxNumberOfSlots = round(duration / step)`. This produces one extra candidate start time versus a strict `<` loop; slots that would overrun the vacancy window are still filtered by `hasRoomBetween()`.

### `timeslot_step` preference of `0` falls back to service duration

When `business->pref('timeslot_step')` is `0`, `calculateStep()` returns the service duration as the step (see `config/preferences.php` default).

### Seed appointment blocks overlapping slots

A confirmed appointment at 10:00–10:30 removes `10:00` from 30-minute service slot lists (capacity check via `hasRoomBetween()`). Golden fixtures in `tests/characterization/fixtures/` reflect this.

### Shared `AvailabilityService` instance retains `excludeDates`

The controller injects a single `AvailabilityService`. After `getDates()` loads iCal exclusion files, `excludeDates()` persists on that instance and can affect subsequent `getTimes()` calls in the same request lifecycle / long-lived worker.

### JSON numeric types

`GET api/vacancies/{businessId}/{serviceId}` returns `business` as a **string** in JSON (e.g. `"662"`), while nested `service.id` is numeric. Tests use loose equality.

---

## Booking

### Reservation code derivation

- Hash: `md5(start_at + contact_id + business_id + service_id)` on save.
- Display code: first `appointment_code_length` characters (default **4**) of hash, uppercased.
- Codes are **not stable across databases** (auto-increment IDs differ); characterization asserts format and derivation, not absolute values.

### Booking POST may return HTTP 500 after successful save

`AgendaController::postStore()` can persist the appointment then fail during `NewAppointmentWasBooked` event / notification side effects in the test environment. Characterization asserts **`seeInDatabase`** and code shape rather than redirect status.

### Booking action POST may return HTTP 500 after successful cancel

`BookingController::postAction()` can cancel the appointment (`status=A`) then fail rendering or event dispatch. Characterization asserts **status transition** and **code persistence** rather than JSON `{code, html}` response body.

---

## iCal feed

### Legacy URL shape

Feed URL remains `/{business-slug}/ical/{token}` (route name `business.ical.download`). Token is `md5(slug + '>' + created_at timestamp)`.

### Invalid token returns HTTP 403

Validation uses `Validator::make` with `in:{validToken}`; any mismatch calls `abort(403)`.

### Content-Type

Successful download: `text/calendar; charset=utf-8` with `BEGIN:VCALENDAR` / `BEGIN:VEVENT` body.

---

## Timezone / DST

### `America/Argentina/Buenos_Aires` has no current DST

Argentina abolished DST in 2009. The suite uses this zone for stable UTC−3 behaviour. A **historical** DST transition date (`2008-03-16`) is included to pin slot generation under the pre-2009 policy; see `availability-slots-dst-2008-03-16.json`.

---

## Test oracle

- **Frozen instant:** `2024-06-15 15:00:00 UTC` (`Carbon::setTestNow`).
- **Vacancy date:** `2024-06-17` (Monday within default 7-day window).
- **Services:** 15 / 30 / 60 / 120 minutes at 09:00–17:00 local.

Regenerate golden slot fixtures after intentional behaviour changes:

```bash
TEST_DB_HOST=127.0.0.1 TEST_DB_DATABASE=timegrid TEST_DB_USERNAME=timegrid TEST_DB_PASSWORD=timegrid123 \
  php tests/characterization/bin/generate-golden-fixtures.php
```
