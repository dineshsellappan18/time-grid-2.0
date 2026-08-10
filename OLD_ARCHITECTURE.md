# Timegrid — Architecture Overview

Self-hosted online appointment and scheduling platform built on **Laravel 5.3** (PHP). The Laravel app owns auth, UI, orchestration, and notifications; the **`timegridio/concierge`** package owns the scheduling domain (vacancies, calendars, booking rules, and core models).

---

## Stack at a glance

| Layer | Technology |
| --- | --- |
| Framework | Laravel 5.3 |
| Language | PHP (≥ 5.6) |
| Booking domain | `timegridio/concierge` |
| UI | Blade + AdminLTE + Bootstrap 3 |
| Auth | Classic login + OAuth (Socialite) |
| Notifications | Events/Listeners → TransMail / Beautymail |
| Calendar export | iCalendar (`eluceo/ical`) |
| Scheduling | Artisan commands + jobs |

---

## System layers

```mermaid
flowchart TB
  subgraph Clients
    B[Browser<br/>Manager · Customer · Guest]
    A[API Client<br/>JSON vacancies / booking]
    C[Artisan / Cron<br/>Reports · iCal · Vacancies]
  end

  subgraph HTTP
    R[Laravel Routes<br/>web.php · api.php]
    M[Middleware<br/>Auth · Role · Lang · CSRF]
    CTRL[Controllers<br/>User · Manager · API · Auth · Root]
  end

  subgraph App
    TG[app/TG Services<br/>Business · Availability · Mail]
    V[Blade Views<br/>AdminLTE · Bootstrap 3]
    E[Events & Listeners<br/>Booking · Confirm · Cancel]
  end

  subgraph Domain
    CON[Concierge Package<br/>Booking · Vacancy · Calendar]
    MOD[Domain Models<br/>App + Concierge Eloquent]
  end

  subgraph Persistence
    DB[(Database)]
    MAIL[Mail / Notify]
  end

  B --> R
  A --> R
  C --> TG
  R --> M --> CTRL
  CTRL --> TG
  CTRL --> V
  CTRL --> MOD
  TG --> CON
  TG --> E
  CON --> MOD
  E --> MAIL
  MOD --> DB
```

**Architectural split**

- **Laravel app** — auth, UI, orchestration (`app/TG`), notifications
- **`timegridio/concierge`** — vacancies, calendars, booking rules, core scheduling models
- **Views** — Blade under `resources/views`

---

## HTTP surfaces

| Prefix / area | Controllers | Purpose |
| --- | --- | --- |
| `/` | Welcome, Guest | Landing & public business pages |
| `/user/*` | `User\*` | Wizard, agenda, directory, preferences |
| `/{business}/manage/*` | `Manager\*` | Dashboard, staff, services, vacancies |
| `/{business}/user/*` | `User\*` | Self-service booking & contacts |
| `/api` & AJAX | `API\*` | Vacancies lookup & booking posts |
| `/root` | `Root\*` | Platform admin |

Route files: `routes/web.php`, `routes/api.php`.

---

## Domain packages

### `app/TG` (application services)

Business registration/setup, availability & iCal sync, search, timezone detection, TransMail, dashboard tokens.

Key areas:

- `BusinessService`, `Business/Dashboard`, `Business/Token`, setup helpers
- `Availability/AvailabilityService`, `Availability/ICalSyncService`
- `SearchEngine`, `DetectTimezone`, `TransMail`
- `Repositories/UserRepository`

### `timegridio/concierge` (booking domain)

High-level booking manager with:

- `BookingManager`
- `VacancyManager` (+ parser / template builder)
- Timeslot / Dateslot calendars
- `TimetableStrategy`
- Addressbook helpers
- Eloquent models: Business, Service, Appointment, Vacancy, Contact, Humanresource, etc.

### Async / scheduled

| Type | Name | Role |
| --- | --- | --- |
| Job | `FetchICalFile` | Pull external calendars |
| Command | `AutopublishBusinessVacancies` | Publish availability |
| Command | `SyncICal` | Sync iCal sources |
| Command | `SendBusinessReport` | Business digests |
| Command | `SendRootReport` | Platform digests |

---

## Domain model

```mermaid
erDiagram
  USER ||--o{ BUSINESS : owns
  USER ||--o{ CONTACT : may_link
  BUSINESS ||--o{ SERVICE : offers
  BUSINESS ||--o{ HUMANRESOURCE : employs
  BUSINESS ||--o{ VACANCY : publishes
  BUSINESS ||--o{ CONTACT : addressbook
  BUSINESS ||--o{ APPOINTMENT : receives
  SERVICE ||--o{ VACANCY : slotted_as
  SERVICE ||--o{ APPOINTMENT : booked_as
  HUMANRESOURCE ||--o{ VACANCY : covers
  VACANCY ||--o| APPOINTMENT : reserved_by
  CONTACT ||--o{ APPOINTMENT : books
  CATEGORY ||--o{ BUSINESS : classifies
  DOMAIN ||--o{ BUSINESS : lists
```

| Entity | Owned by | Relates to | Role in product |
| --- | --- | --- | --- |
| User / Role / Permission | `app/Models` | Business (owner), Contact | Auth & RBAC |
| Business | Concierge | Services, Staff, Vacancies, Contacts | Tenant / shop |
| Service (+ ServiceType) | Concierge | Business, Vacancies, Appointments | Bookable offering |
| Humanresource (Staff) | Concierge | Business, Vacancies, iCal link | Who delivers service |
| Vacancy | Concierge | Business, Service, Staff, Appointment | Open availability slot |
| Appointment | Concierge | Vacancy, Contact, Service | Confirmed / soft booking |
| Contact | Concierge | Business, User (optional link) | Addressbook customer |
| Preference | Both | User / Business | Locale, strategy, settings |
| Domain / Category | Concierge | Business | Marketplace listing / strategy |

---

## Booking request path

```mermaid
sequenceDiagram
  actor Customer
  participant API as API / AgendaController
  participant Concierge as Concierge::booking()
  participant DB as Database
  participant Events as Events & Listeners
  participant Mail as TransMail / Beautymail
  actor Manager

  Customer->>API: Pick business & service
  Customer->>API: GET vacancies
  API->>Concierge: Query availability
  Concierge->>DB: Read vacancies
  Customer->>API: POST booking
  API->>Concierge: Reserve vacancy
  Concierge->>DB: Create appointment
  Concierge->>Events: NewAppointmentWasBooked
  Events->>Mail: Send notifications
  Mail-->>Customer: Booking confirmation
  Mail-->>Manager: New booking notice
  Manager->>DB: View agenda / calendar
```

1. Customer picks a business
2. Client loads vacancies (API)
3. POST booking via `User\AgendaController` or `API\BookingController`
4. `Concierge::booking()` reserves a Vacancy
5. Fires `NewAppointmentWasBooked` (or soft-book event)
6. Listeners send email notifications
7. Manager agenda & calendar refresh from Appointment models

### Role views

| Role | Flow |
| --- | --- |
| **Customer** | Wizard → business home → timeslot/dateslot picker → store appointment → optional soft-appointment email validation |
| **Manager** | Dashboard, addressbook, staff, services, vacancy publish, agenda, FullCalendar, notifications |
| **System** | Event → listener email fan-out; iCal export token; scheduled vacancy autopublish and reports |

---

## Event → notification map

| Event | Listener(s) | Outcome |
| --- | --- | --- |
| `NewUserWasRegistered` | `AutoConfigureUserPreferences`, `SendMailUserWelcome` | Prefs + welcome email |
| `NewAppointmentWasBooked` | `SendBookingNotification` | Owner/customer booking mail |
| `NewSoftAppointmentWasBooked` | `SendSoftAppointmentValidationRequest` | Validation request |
| `AppointmentWasConfirmed` | `SendAppointmentConfirmationNotification` | Confirm notice |
| `AppointmentWasCanceled` | `SendAppointmentCancellationNotification` | Cancel notice |
| `NewContactWasRegistered` | `LinkContactToExistingUser` | Attach contact to user |

Defined in `app/Providers/EventServiceProvider.php`.

---

## Key directories

```text
timegrid/
├── app/
│   ├── Console/Commands/     # Scheduled tasks
│   ├── Events/ · Listeners/  # Notification pipeline
│   ├── Http/Controllers/     # User · Manager · API · Auth · Root
│   ├── Jobs/                 # FetchICalFile
│   ├── Models/               # User, Role, Permission
│   ├── Policies/             # Business, Contact authorization
│   └── TG/                   # Application service layer
├── routes/                   # web.php, api.php
├── resources/views/          # Blade UI (manager, user, guest, emails)
├── database/migrations/      # Schema
└── vendor/timegridio/concierge/  # Booking domain package
```

---

## Product context

See [PRODUCT_OVERVIEW.md](./PRODUCT_OVERVIEW.md) for the business/product summary.

Local demo (when running): `http://127.0.0.1:8000`
