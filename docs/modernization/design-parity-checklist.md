# Visual / Design parity checklist

**Design source used:** Forge `UI_DESIGN_SPEC` (no Figma file or UX reference images are linked in Forge or the repo).

**Status date:** 2026-08-11

## Blocker

| Item | Status |
|------|--------|
| Figma file URL | **Missing** — asked in Forge clarification |
| UX reference images | **None uploaded** |
| UI design stage approval | Forge notes design stage was **not approved**; hierarchy is indicative |

Until Figma is provided, parity is judged against the Forge ManagerLayout / PublicLayout component trees only.

## Shell CSS consistency (live app)

| Screen | HTTP | Shell | Vite CSS | Forge shell pieces |
|--------|------|-------|----------|--------------------|
| Home `/` | 200 | welcome | yes | full-bleed brand hero + feature strip |
| Login / Register | 200 | **auth** | yes | centered auth card, BS5 forms (no AdminLTE/iCheck) |
| Dashboard | 200 | manager | yes | Sidebar + TopBar + Kpi + TzChip |
| Agenda | 200 | manager | yes | Sidebar + TopBar + TzChip |
| Calendar | 200 | manager | yes | Sidebar + TopBar + TzChip |
| Address book | 200 | manager | yes | Sidebar + TopBar + TzChip |
| Services | 200 | manager | yes | Sidebar + TopBar + TzChip |
| Staff | 200 | manager | yes | Sidebar + TopBar + TzChip |
| Vacancy create | 200 | manager | yes | Sidebar + TopBar + TzChip |
| Preferences | 200 | manager | yes | Sidebar + TopBar + TzChip |
| Notifications | 200 | manager | yes | Sidebar + TopBar + TzChip |
| Guest business | 200 | public | yes | public topbar |
| User dashboard / agenda / directory | 200 | public | yes | public topbar |

## Forge component parity (content, not just chrome)

| Forge screen | Match | Gaps |
|--------------|-------|------|
| ManagerLayout chrome | Partial | Missing `ThemeToggle`; Calendar sharing nav item absent (WO-047) |
| Dashboard KpiRow | Partial | Legacy appointment/contact counters, not today/week/occupancy/query-cost cards |
| Agenda & Calendar | Partial | Agenda is plain table (no filter tabs / AppointmentRow actions). Calendar uses FullCalendar island |
| Address book | Partial | Legacy contacts table; no FilterChips, PII badges, detail drawer, GDPR bar |
| Availability editor | Partial | Legacy vacancy UI; no ModeSwitch / PublishBar as specified |
| Preferences | Partial | Legacy prefs form; not tabbed General/Hours/Notifications/Security |
| Public booking | Partial | Not mobile-first 480px stepper flow |
| Calendar sharing UI | Missing | WO-047 backlog |
| Platform health console | Missing | WO-048 backlog |

## Sign-off

Awaiting Figma URL / UX frames before pixel-level CSS comparison can be completed.
