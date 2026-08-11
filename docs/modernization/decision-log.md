# Modernization Decision Log

## Framework Upgrade Path

| Hop | From | To | WO | Date | Notes |
|-----|------|----|----|----|-------|
| 1 | 5.3 | 5.4 | WO-017 | 2026-08-10 | Blade/mail changes, TrimStrings middleware |
| 2 | 5.4 | 5.5 | WO-017 | 2026-08-10 | Package auto-discovery, exception handler |
| 3 | 5.5 | 5.6 | WO-017 | 2026-08-10 | Channel-based logging (config/logging.php) |
| 4 | 5.6 | 5.7 | WO-017 | 2026-08-10 | URL generation, auth middleware |
| 5 | 5.7 | 5.8 | WO-017 | 2026-08-10 | Helper deprecations (Str/Arr) |
| 6 | 5.8 | 6.0 | WO-018 | 2026-08-11 | Throwable, removed Input facade |
| 7 | 6.0 | 7.0 | WO-018 | 2026-08-11 | Multi-mailer config |
| 8 | 7.0 | 8.0 | WO-018 | 2026-08-11 | Class-based factories, seeder namespacing |
| 9 | 8.0 | 9.0 | WO-019 | 2026-08-11 | Symfony 6, Flysystem 3 |
| 10 | 9.0 | 10.0 | WO-019 | 2026-08-11 | Native types, validation changes |
| 11 | 10.0 | 11.0 | WO-020 | 2026-08-11 | Slim skeleton, published migrations |
| 12 | 11.0 | 12.0 | WO-021 | 2026-08-11 | Carbon 3 adoption |
| 13 | 12.0 | 13.0 | WO-022 | 2026-08-11 | Final target, EOL assertion |

## Verified Support Windows

| Component | Version | Bug Fixes Until | Security Fixes Until | Retrieved |
|-----------|---------|-----------------|---------------------|-----------|
| Laravel Framework | 13.x | 2027-02 | 2028-02 | 2026-08-11 |
| PHP | 8.3 | 2025-11 (active ended) | 2026-11 | 2026-08-11 |
| PHP | 8.4 | 2026-11 | 2027-11 | 2026-08-11 |
| Symfony Components | ^6.2 | Per-component | Per-component | 2026-08-11 |
| Carbon | ^3.0 | Active | Active | 2026-08-11 |

## Key Decisions

### D-001: Vendored Framework Approach
- **Decision**: Keep Laravel framework source in `packages/laravel-framework` rather than using Composer's package manager
- **Rationale**: Allows surgical control of internal framework classes during incremental upgrade without full dependency resolution
- **Trade-off**: Manual updates required; no automatic Composer-driven patches

### D-002: Single-Commit-Per-Hop Strategy
- **Decision**: Each framework hop is a single atomic commit on `main`
- **Rationale**: Maximally bisectable; each commit independently revertable
- **Trade-off**: Larger individual commits; cannot partially revert within a hop

### D-003: Carbon 3 Mutation Fixes
- **Decision**: Fix all implicit mutations with explicit `copy()` rather than switching to CarbonImmutable globally
- **Rationale**: Preserves existing mutable patterns where mutation is intentional (loop counters in AvailabilityService); minimizes behavioral change
- **Trade-off**: Must manually audit new Carbon usage going forward

### D-004: Middleware Registration Duality
- **Decision**: Register middleware in both `bootstrap/app.php` (Laravel 11+ pattern) and `app/Http/Kernel.php` (backward compat)
- **Rationale**: Vendored framework still internally reads from Kernel; bootstrap/app.php is the human-facing registration point
- **Trade-off**: Must keep both in sync (documented in Kernel.php header comment)

### D-005: EOL Assertion as Required Check
- **Decision**: `tools/eol-check.php` runs in pipeline and fails build if constraints drift below target
- **Rationale**: Prevents silent regression of framework/PHP version during future work
- **Trade-off**: Must update the script when the next major upgrade cycle begins

## Risk Register Closure

| Risk ID | Description | Status | Resolution |
|---------|-------------|--------|-----------|
| R-001 | Framework on EOL line | CLOSED | Laravel 13 verified patch-receiving through 2028-02 |
| R-002 | PHP runtime EOL | CLOSED | PHP 8.4 security support through 2027-11 |
| R-003 | Carbon timezone/booking regression | CLOSED | All mutation sites audited and fixed in WO-021 |
| R-004 | Flysystem 3 silent empty results | CLOSED | Explicit null/empty checks with structured logging in WO-019 |
