# AGPL / copyleft review — Concierge hosted fork (WO-002)

**Review date:** 2026-08-10  
**Reviewer:** Platform Maintainer (dinesh.sellappan) with Security Reviewer consult on supply-chain provenance  
**Subject:** Organisation fork of the booking engine used by Timegrid, for self-hosted and hosted deployments

---

## License facts

| Artefact | License observed | Evidence |
| --- | --- | --- |
| Application `timegrid` (this repository) | **AGPL-3.0** (project licence) | Root / package metadata historically AGPL for the product |
| Upstream `timegridio/concierge` package | **MIT** | `packages/timegrid-concierge/LICENSE.md` (copied from upstream); `composer.json` `"license": "MIT"` |
| Organisation fork `dineshsellappan18/timegrid-concierge` **1.0.0** | Remains **MIT** for the library sources | Fork does not relicense upstream MIT code |

**Important distinction:** Concierge as a library is MIT. The **Timegrid application** that embeds it is under AGPL. Hosting Timegrid (SaaS or managed hosting) triggers AGPL obligations on the **application**, not a relicense of Concierge itself.

---

## Review outcome

**Outcome: APPROVED to adopt the organisation fork**, subject to the obligations below.

Rationale:

1. Forking MIT Concierge into `packages/timegrid-concierge` (semver **1.0.0**) does not create a copyleft conflict with Concierge’s own licence.
2. Deploying the Timegrid **application** (AGPL) as a network service requires AGPL compliance for that application’s Corresponding Source.
3. Keeping Concierge as a clearly MIT-licensed subtree (or future VCS tag) preserves provenance and simplifies third-party reuse of the booking engine alone.

---

## Obligations for self-hosted deployments

1. Provide **Corresponding Source** of the AGPL-covered Timegrid application to recipients who receive binaries or run modified versions.
2. Preserve AGPL notices, copyright, and licence texts in distributions.
3. Document how operators obtain source (e.g. this GitHub repository / tagged release matching the deployed build).
4. Do not remove MIT notices from the Concierge fork subtree.

---

## Obligations for hosted (SaaS / multi-tenant) deployments

1. AGPL **network use** / Corresponding Source offer: operators of a modified AGPL Timegrid service must ensure users interacting remotely can obtain Corresponding Source of the hosted application version (standard AGPL §13 considerations — counsel if commercial SaaS expands).
2. Do **not** treat Concierge MIT code as if it were AGPL; keep licence headers accurate.
3. Supply-chain: pin the fork by **semver tag** (no `dev-master`); record digest in the decision log (done for **1.0.0**).
4. Before production SaaS launch, legal sign-off on the AGPL source-offer mechanism (named follow-up; not blocking the Phase 1 fork decision).

---

## Named reviewer

| Role | Name | Sign-off |
| --- | --- | --- |
| Reviewer / Platform Maintainer | dinesh.sellappan | Approved 2026-08-10 |
| Security (provenance) | Security Reviewer (programme role) | Fork tag + digest required — satisfied by D-006 |

---

## Residual risks

- Relicensing mistakes if someone copies Concierge into AGPL-only trees without MIT attribution.
- Future extraction of Concierge to a public GitHub package must keep MIT and changelog of floor-raise commits.
- If counsel later requires AGPL for a combined work interpretation, revisit distribution packaging — **does not reverse the fork-vs-reimplement technical decision**.
