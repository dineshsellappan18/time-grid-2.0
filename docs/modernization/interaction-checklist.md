# Per-Screen Interaction Parity Checklist

**Phase Gate:** Frontend migration (Phase 5)
**Status:** ACTIVE — All rows must have a passing Dusk assertion to close the phase.

---

## Legend

| Column | Meaning |
|--------|---------|
| Screen | View file or route |
| Control | Interactive element |
| Expected Behaviour | What happens when user interacts |
| Selector | Stable `data-*` attribute selector |
| Dusk Test | Test class + method reference |
| A11y | Keyboard operability verified |

---

## Manager Screens

### Manager > Agenda (Timeslot)

| # | Screen | Control | Expected Behaviour | Selector | Dusk Test | A11y |
|---|--------|---------|-------------------|----------|-----------|------|
| M-01 | manager/agenda/timeslot | Confirm button | POST appointment action, replaces row HTML | `[data-action=confirm]` | `ManagerAgendaTest::test_confirm_action` | ✓ |
| M-02 | manager/agenda/timeslot | Cancel button | POST appointment action, replaces row HTML | `[data-action=cancel]` | `ManagerAgendaTest::test_cancel_action` | ✓ |
| M-03 | manager/agenda/timeslot | Serve button | POST appointment action, replaces row HTML | `[data-action=serve]` | `ManagerAgendaTest::test_serve_action` | ✓ |
| M-04 | manager/agenda/timeslot | Calendar link | Navigates to calendar view | `[data-nav=calendar]` | `ManagerAgendaTest::test_calendar_link` | ✓ |

### Manager > Agenda (Dateslot)

| # | Screen | Control | Expected Behaviour | Selector | Dusk Test | A11y |
|---|--------|---------|-------------------|----------|-----------|------|
| M-05 | manager/agenda/dateslot | Confirm button | POST appointment action, replaces row HTML | `[data-action=confirm]` | `ManagerAgendaDateslotTest::test_confirm_action` | ✓ |
| M-06 | manager/agenda/dateslot | Cancel button | POST appointment action, replaces row HTML | `[data-action=cancel]` | `ManagerAgendaDateslotTest::test_cancel_action` | ✓ |
| M-07 | manager/agenda/dateslot | Serve button | POST appointment action, replaces row HTML | `[data-action=serve]` | `ManagerAgendaDateslotTest::test_serve_action` | ✓ |
| M-08 | manager/agenda/dateslot | Date filter tabs | Shows/hides rows for selected date | `[data-filter-date]` | `ManagerAgendaDateslotTest::test_date_filter` | ✓ |

### Manager > Calendar

| # | Screen | Control | Expected Behaviour | Selector | Dusk Test | A11y |
|---|--------|---------|-------------------|----------|-----------|------|
| M-09 | manager/calendar | Calendar widget | Renders FullCalendar with events | `#calendar` | `ManagerCalendarTest::test_calendar_renders` | ✓ |
| M-10 | manager/calendar | Prev/Next nav | Changes displayed week/month | `.fc-prev-button, .fc-next-button` | `ManagerCalendarTest::test_navigation` | ✓ |
| M-11 | manager/calendar | View switcher | Switches between month/week/day | `.fc-dayGridMonth-button` | `ManagerCalendarTest::test_view_switch` | ✓ |
| M-12 | manager/calendar | iCal URL copy | Displays iCal feed URL | `[data-ical-url]` | `ManagerCalendarTest::test_ical_url_visible` | ✓ |

### Manager > Services

| # | Screen | Control | Expected Behaviour | Selector | Dusk Test | A11y |
|---|--------|---------|-------------------|----------|-----------|------|
| M-13 | manager/services/index | Delete link | Submits DELETE form via method link | `a[data-method=DELETE]` | `ManagerServicesTest::test_delete_service` | ✓ |
| M-14 | manager/services/_form | Color picker | Selects hex colour for service | `[data-control=color-picker]` | `ManagerServicesTest::test_color_picker` | ✓ |
| M-15 | manager/services/_form | Service type select | Chooses between dateslot/timeslot | `select[name=type_id]` | `ManagerServicesTest::test_type_select` | ✓ |
| M-16 | manager/services/_availability | Availability toggle | Toggles day availability via switch | `[data-control=availability-switch]` | `ManagerServicesTest::test_availability_toggle` | ✓ |

### Manager > Vacancies

| # | Screen | Control | Expected Behaviour | Selector | Dusk Test | A11y |
|---|--------|---------|-------------------|----------|-----------|------|
| M-17 | manager/vacancies/_form_advanced | Service multi-select | Selects services for vacancy | `select[name="services[]"]` | `ManagerVacanciesTest::test_service_multi_select` | ✓ |
| M-18 | manager/vacancies/_form_advanced | Publish button | Submits vacancy configuration | `[data-action=publish]` | `ManagerVacanciesTest::test_publish` | ✓ |

### Manager > Contacts (Address Book)

| # | Screen | Control | Expected Behaviour | Selector | Dusk Test | A11y |
|---|--------|---------|-------------------|----------|-----------|------|
| M-19 | manager/contacts/index | Search filter | Filters contact list as user types | `[data-search-filter]` | `ManagerContactsTest::test_search_filter` | ✓ |
| M-20 | manager/contacts/index | Filter toggle | Shows/hides column filters | `[data-action=toggle-filter]` | `ManagerContactsTest::test_filter_toggle` | ✓ |
| M-21 | manager/contacts/_form | Phone input | Accepts international phone number | `#mobile-input` | `ManagerContactsTest::test_phone_input` | ✓ |
| M-22 | manager/contacts/_form | Firstname autoslug | Generates slug from name | `input[name=firstname]` | `ManagerContactsTest::test_firstname_input` | ✓ |
| M-23 | manager/contacts/show | Panel toggler | Switches visible info panels | `[data-toggle-panel]` | `ManagerContactsTest::test_panel_toggle` | ✓ |
| M-24 | manager/contacts/show | Delete link | Submits DELETE form via method link | `a[data-method=DELETE]` | `ManagerContactsTest::test_delete_contact` | ✓ |

### Manager > Human Resources

| # | Screen | Control | Expected Behaviour | Selector | Dusk Test | A11y |
|---|--------|---------|-------------------|----------|-----------|------|
| M-25 | manager/humanresources/show | Panel toggler | Switches visible staff panels | `[data-toggle-panel]` | `ManagerHumanResourcesTest::test_panel_toggle` | ✓ |
| M-26 | manager/humanresources/show | Delete link | Submits DELETE form via method link | `a[data-method=DELETE]` | `ManagerHumanResourcesTest::test_delete_staff` | ✓ |

### Manager > Business Settings & Preferences

| # | Screen | Control | Expected Behaviour | Selector | Dusk Test | A11y |
|---|--------|---------|-------------------|----------|-----------|------|
| M-27 | manager/businesses/create | Form wizard | Steps through business creation | `[data-step]` | `ManagerBusinessTest::test_wizard_steps` | ✓ |
| M-28 | manager/businesses/create | Submit button | Creates business | `button[type=submit]` | `ManagerBusinessTest::test_create_submit` | ✓ |
| M-29 | manager/businesses/_form | Phone input | Accepts international phone | `#phone-input` | `ManagerBusinessTest::test_phone_input` | ✓ |
| M-30 | manager/preferences/edit | Delete business link | Submits DELETE form via method link | `a[data-method=DELETE]` | `ManagerPreferencesTest::test_delete_business` | ✓ |

### Manager > Notifications

| # | Screen | Control | Expected Behaviour | Selector | Dusk Test | A11y |
|---|--------|---------|-------------------|----------|-----------|------|
| M-31 | manager/notifications | Notification list | Displays notification items | `[data-notification-list]` | `ManagerNotificationsTest::test_list_renders` | ✓ |

---

## User Screens

### User > Appointments (Agenda)

| # | Screen | Control | Expected Behaviour | Selector | Dusk Test | A11y |
|---|--------|---------|-------------------|----------|-----------|------|
| U-01 | user/appointments/index | Cancel button | POST cancel action, replaces panel | `[data-action=cancel]` | `UserAppointmentsTest::test_cancel_action` | ✓ |
| U-02 | user/appointments/index | Confirm button | POST confirm action, replaces panel | `[data-action=confirm]` | `UserAppointmentsTest::test_confirm_action` | ✓ |

### User > Booking (Timeslot)

| # | Screen | Control | Expected Behaviour | Selector | Dusk Test | A11y |
|---|--------|---------|-------------------|----------|-----------|------|
| U-03 | user/booking/timeslot/book | Wizard steps | Navigates booking steps | `[data-step]` | `UserBookingTimeslotTest::test_wizard_steps` | ✓ |
| U-04 | user/booking/timeslot/_service-picker | Service buttons | Selects service, loads times via fetch | `[data-service-id]` | `UserBookingTimeslotTest::test_service_selection` | ✓ |
| U-05 | user/booking/timeslot/_time-picker | Time slot button | Selects time, advances step | `[data-time-slot]` | `UserBookingTimeslotTest::test_time_selection` | ✓ |
| U-06 | user/booking/timeslot/_date-picker | Date input | Selects booking date | `input[name=date]` | `UserBookingTimeslotTest::test_date_selection` | ✓ |

### User > Booking (Dateslot)

| # | Screen | Control | Expected Behaviour | Selector | Dusk Test | A11y |
|---|--------|---------|-------------------|----------|-----------|------|
| U-07 | user/booking/dateslot/_timetable | Service button | Selects service, shows prereqs | `[data-service-id]` | `UserBookingDateslotTest::test_service_selection` | ✓ |
| U-08 | user/booking/dateslot/_timetable | Date button | Filters timetable by date | `[data-filter-date]` | `UserBookingDateslotTest::test_date_filter` | ✓ |
| U-09 | user/booking/dateslot/show | Cancel button | POST cancel action | `[data-action=cancel]` | `UserBookingDateslotTest::test_cancel` | ✓ |

### User > Contacts

| # | Screen | Control | Expected Behaviour | Selector | Dusk Test | A11y |
|---|--------|---------|-------------------|----------|-----------|------|
| U-10 | user/contacts/_form | Phone input | Accepts international phone | `#mobile-input` | `UserContactsTest::test_phone_input` | ✓ |
| U-11 | user/contacts/_form | Date picker (birthdate) | Selects date via native input | `#birthdate` | `UserContactsTest::test_birthdate_picker` | ✓ |
| U-12 | user/contacts/show | Cancel appointment | POST cancel action, replaces panel | `[data-action=cancel]` | `UserContactsTest::test_cancel_from_contact` | ✓ |

### User > Businesses

| # | Screen | Control | Expected Behaviour | Selector | Dusk Test | A11y |
|---|--------|---------|-------------------|----------|-----------|------|
| U-13 | user/businesses/show | Subscribe button | Subscribes to business | `[data-action=subscribe]` | `UserBusinessesTest::test_subscribe` | ✓ |
| U-14 | user/businesses/show | Confirm button | POST confirm action | `[data-action=confirm]` | `UserBusinessesTest::test_confirm_action` | ✓ |
| U-15 | user/businesses/show | Book button | Navigates to booking flow | `[data-nav=book]` | `UserBusinessesTest::test_book_link` | ✓ |

---

## Guest Screens

| # | Screen | Control | Expected Behaviour | Selector | Dusk Test | A11y |
|---|--------|---------|-------------------|----------|-----------|------|
| G-01 | guest/businesses/show | Cancel button | POST cancel action, replaces panel | `[data-action=cancel]` | `GuestBusinessTest::test_cancel_action` | ✓ |
| G-02 | guest/businesses/show | Confirm button | POST confirm action, replaces panel | `[data-action=confirm]` | `GuestBusinessTest::test_confirm_action` | ✓ |
| G-03 | guest/businesses/show | Book button | Navigates to booking flow | `[data-nav=book]` | `GuestBusinessTest::test_book_link` | ✓ |

---

## Root / Auth Screens

| # | Screen | Control | Expected Behaviour | Selector | Dusk Test | A11y |
|---|--------|---------|-------------------|----------|-----------|------|
| R-01 | auth/login | Email input | Accepts email for login | `input[name=email]` | `AuthTest::test_login_email_input` | ✓ |
| R-02 | auth/login | Password input | Accepts password | `input[name=password]` | `AuthTest::test_login_password_input` | ✓ |
| R-03 | auth/login | Submit button | Submits login form | `button[type=submit]` | `AuthTest::test_login_submit` | ✓ |
| R-04 | auth/register | Name input | Accepts user name | `input[name=name]` | `AuthTest::test_register_name_input` | ✓ |
| R-05 | auth/register | Email input | Accepts email for registration | `input[name=email]` | `AuthTest::test_register_email_input` | ✓ |
| R-06 | auth/register | Password input | Accepts password | `input[name=password]` | `AuthTest::test_register_password_input` | ✓ |
| R-07 | auth/register | Submit button | Submits registration form | `button[type=submit]` | `AuthTest::test_register_submit` | ✓ |
| R-08 | welcome | Language switcher | Changes application locale | `[data-nav=lang-switch]` | `WelcomeTest::test_language_switcher` | ✓ |
| R-09 | welcome | Get Started button | Navigates to registration | `[data-nav=get-started]` | `WelcomeTest::test_get_started` | ✓ |

---

## Shared / Layout Controls

| # | Screen | Control | Expected Behaviour | Selector | Dusk Test | A11y |
|---|--------|---------|-------------------|----------|-----------|------|
| S-01 | layouts/* | Language dropdown | Opens dropdown, switches locale | `#navLang` | `SharedLayoutTest::test_language_dropdown` | ✓ |
| S-02 | layouts/* | User account menu | Opens dropdown with profile links | `[data-nav=user-menu]` | `SharedLayoutTest::test_user_menu` | ✓ |
| S-03 | layouts/* | Notifications menu | Shows upcoming appointments | `[data-nav=notifications]` | `SharedLayoutTest::test_notifications_menu` | ✓ |
| S-04 | layouts/* | Sidebar toggle (mobile) | Toggles sidebar on small screens | `[data-bs-toggle=offcanvas]` | `SharedLayoutTest::test_sidebar_toggle` | ✓ |
| S-05 | layouts/* | Copy to clipboard (iCal URL) | Copies feed URL to clipboard | `[data-clipboard-target]` | `SharedLayoutTest::test_clipboard_copy` | ✓ |

---

## Phase Gate Summary

| Metric | Value |
|--------|-------|
| Total Rows | 55 |
| Manager | 31 |
| User | 15 |
| Guest | 3 |
| Root/Auth | 9 |
| Shared | 5 |
| Rows with Dusk Test | 55 |
| Rows with A11y Check | 55 |
| **Gate Status** | ALL MAPPED |

---

## Gate Enforcement

The phase gate is enforced by `tests/Browser/InteractionParity/RunInteractionGateTest.php` which:
1. Parses this checklist for all rows
2. Verifies each referenced Dusk test class and method exists
3. Fails if any row is unmapped or the referenced test does not exist
4. Publishes results to `storage/app/interaction-gate-result.json`

To run: `php artisan dusk --filter=InteractionParity`
