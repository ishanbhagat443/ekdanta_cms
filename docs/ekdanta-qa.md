# Ekdanta QA log

The ui-ux-pro-max pre-delivery checklist (see `design-system/ekdanta/MASTER.md` → Pre-Delivery Checklist), run on every Ekdanta section and every new admin screen, desktop and mobile. One row per item checked. Open items are at the top.

## Open items

| # | Where | Finding | Proposed fix | Status |
|---|---|---|---|---|
| 1 | Every page with looping motion (trunk and beam, orbit, marquees, phone dial, blinking "e") | WCAG 2.2.2 Pause, Stop, Hide (Level A): motion that starts by itself, lasts over 5 s and runs beside other content needs a way to pause it. The design has none. | "Pause animations" text button in the Finale footer line and the mobile menu; switches to the reduced-motion state; remembered per visitor | Approved 8 Oct 2026, built in Phase 3/4 |

## Decisions already made

| Date | Item | Measured | Decision |
|---|---|---|---|
| 8 Oct 2026 | Focus ring `#F2A230` | 2.1:1 on white, 1.3:1 on green | Approved: keep the orange outline, add an ink halo (element → 3 px ink → 3 px orange → 2 px ink) |
| 8 Oct 2026 | Small muted captions `#7B867F` on paper | 3.5:1 | Approved: `#6A736D` where text is small (4.5:1) |
| 8 Oct 2026 | Small green eyebrows `#1E8E47` | 3.9:1 | Approved: `#1B8141` under 18 px (4.53:1); large green headings unchanged |
| 8 Oct 2026 | "/6" suffix `#9AA39E` (64 px) | 2.4:1 | Approved: `#85908A` (3.04:1) |
| 8 Oct 2026 | White "BIG BRAND!" on `#2EB45D` (200 px) | 2.7:1 | Approved: finale band `#2CAA58` (3.0:1), footer line `#0D351F` (4.53:1) |
| 8 Oct 2026 | Unlit founder-story words | 1.5:1 | Accepted: a transient scroll state that ends fully lit; shown lit under reduced motion |
| 8 Oct 2026 | Mobile menu arrows | 2.9:1 | Accepted: decorative, `aria-hidden` |

## Checklist results

Recorded per phase from Phase 1 on.

| Phase | Area | Result | Status |
|---|---|---|---|
| Phase 0 | Setup (docs, design system, dependency) | No UI changed; nothing to check | n/a |
| Phase 1 | Site header (all three navigation styles) | Menu buttons were 40 × 40 and lost their focus outline (`outline: none`, only a faint tint): now 44 × 44 with a 2 px ring on keyboard focus. Full-screen menu is a modal `<dialog>`: focus trapped, Esc closes, focus returns to the button; `aria-label` on both icon buttons, `aria-expanded` and `aria-controls` on the toggle. Current item gets `aria-current="page"`. Dropdown active item: ink on light tint (was accent text on Bootstrap blue). Rising items and the bars-to-cross skip animation under reduced motion and the site-wide switch | Pass |
| Phase 1 | Header Builder → Navigation style | Three labelled selects with plain-language options and a heading; Header JS shown only to Developer Mode | Pass |
| Phase 1 | Theme Customizer → Typography | Font family is a labelled text field with a suggestion list (`<datalist>`) and helper text; stored values are never replaced on save; invalid names get a field error | Pass |
| Phase 1 | Theme Customizer → Animations | Helper text under each setting; list matches the section editor | Pass |
| Phase 1 | Forms → edit | "Also save submissions as enquiries" switch with helper text replaces the fixed source list | Pass |
| Phase 1 | Error pages (403, 404) | Message renders as text; no raw Blade | Pass |
