# Timegrid — Product Overview

Timegrid is an online appointment and scheduling platform built for service businesses that need a cleaner way to manage bookings, clients, staff, and availability — without phone tag or messy spreadsheets.

## What it solves

Most service businesses lose time coordinating appointments manually. Timegrid gives your business a self-service booking experience for customers and a complete back-office for your team to manage day-to-day operations.

## Who it’s for

Ideal for clinics, consultants, salons, coaches, and any appointment-based business that wants customers to book online while the team keeps full control of calendar, services, and client records.

## What you can do today

- **Business Dashboard** — One place to oversee appointments, status, and daily operations
- **Addressbook** — Store and manage all customer/contact records
- **Agenda & Calendar** — View and manage appointments in a clear schedule view
- **Staff Management** — Assign people who deliver the services
- **Services Setup** — Define what your business offers and how it is booked
- **Availability** — Control when bookings can be taken
- **Customer Self-Booking** — Let clients choose a time that works for them
- **Notifications** — Keep customers and owners updated on appointment activity
- **Multi-language Support** — Ready for broader audience reach

## Value for your business

1. **Optimize** — Customers book when it suits them, reducing back-and-forth
2. **Grow** — Keep contacts organized and convert them into recurring customers
3. **Professionalize** — Deliver a polished digital booking experience
4. **Save time** — Free your team from repetitive scheduling work

## How it works in practice

1. Create your business profile
2. Add services, staff, and availability
3. Share booking access with customers
4. Manage appointments from the dashboard

## Stack at a glance

| Layer           | Technology                                |
| --------------- | ----------------------------------------- |
| Framework       | Laravel 5.3                               |
| Language        | PHP (≥ 5.6)                               |
| Booking domain  | `timegridio/concierge`                    |
| UI              | Blade + AdminLTE + Bootstrap 3            |
| Auth            | Classic login + OAuth (Socialite)         |
| Notifications   | Events/Listeners → TransMail / Beautymail |
| Calendar export | iCalendar (`eluceo/ical`)                 |
| Scheduling      | Artisan commands + jobs                   |

### Role views

| Role | Flow |  
| **Customer** | Wizard → business home → timeslot/dateslot picker → store appointment → optional soft-appointment email validation |
| **Manager** | Dashboard, addressbook, staff, services, vacancy publish, agenda, FullCalendar, notifications |
| **System** | Event → listener email fan-out; iCal export token; scheduled vacancy autopublish and reports |
