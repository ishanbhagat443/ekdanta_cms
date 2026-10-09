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
| Phase 2 | Settings → Business (phone numbers, offices) | Repeating rows with Add / Remove; "Add" moves focus to the new row; Remove has an `aria-label`; row errors are shown as text under the list. Found: every repeater field (here and in all builder repeaters) was labelled only by its placeholder, so a filled row lost its labels (WCAG 3.3.2), and selects had no name at all. Fixed in the shared partial: visible caption over each control, the same text as its `aria-label` | Pass after fix |
| Phase 2 | Page settings (role, header style, menu, button, footer, page code) | Every control has a `<label for>`; page code shown only with Developer Mode | Pass |
| Phase 2 | Builder preview sizes + Reduce motion | Size buttons are a labelled group with `aria-pressed`; the switch has a label; the iframe has a `title`; reload button got an `aria-label` | Pass |
| Phase 2 | Header Builder (style, progress bar, WhatsApp button, full-screen menu extras) | All fields labelled. Floating pill: `scroll-padding-top: 96px`, so anchors and focus are never hidden under it. WhatsApp button is 44 × 44 with an `aria-label` (Ekdanta: ink on `#2EB45D`, 6.9:1). Progress bar is `aria-hidden` and drawn with `scaleX`. Found: the full-screen menu's new button still animated under reduced motion, and the call line was dimmed to 0.8 opacity. Fixed: no animation under reduced motion or the site switch, full-opacity text | Pass after fix |
| Phase 2 | Brand → Design colours | Each colour picker sits inside a label naming its token; 44 × 44; invalid values are refused with a field error; tokens with transparency stay in Theme Customizer | Pass |
| Phase 2 | Ekdanta theme preset | Colours, fonts and radii are copied from MASTER.md, including the approved accessibility shades (`#1B8141` links, `#2CAA58` finale band). Body text is ink `#0D1410`, the colour used in the design files | Pass |
| Phase 3 | Motion engine on the public site | In headless Chrome (1280 × 720 and 390 × 844): no console errors, every scene boots, and with `prefers-reduced-motion` forced every section shows its final state at once. By feature/JS tests and the CSS rules (browser runs come in Phase 6 with Playwright): `?reduce-motion=1` and the site switch give the same result, with no pinning and no loops. Without JS nothing is hidden or pinned. If the script never arrives, the content shows after 2.5 s. Loops pause off screen. Keyboard focus moving onto a part still hidden in a pinned stage scrolls the scene to where the part is shown. Steps set `is-active` and `aria-current="step"`. Split text keeps an accessible copy; count-ups keep the real number for screen readers | Pass |
| Phase 3 | Visitor "Pause animations" button (full-screen menu; Finale in Phase 4) | Text button, at least 44 px tall, `aria-pressed`. The label stays the same, and the pressed state is also shown with a ✓, not only by colour. The choice is remembered on the device and applied before first paint. Hidden when the site has already switched motion off | Pass |
| Phase 3 | Section → Motion tab | Every control has a `<label for>`. Switches are labelled, the "edited" badge has a text title, and steps and scene settings use native `<details>`. Reset and Remove ask first. The preview slider and Play drive the live preview. JSON fields report a named error instead of saving | Pass |
| Phase 3 | Motion Library (list, form, preview) | Icon-only actions have an `aria-label`. The import field is labelled, errors show as text with `role="alert"`, and the preview iframe has a `title`. Built-in presets can't be deleted (Duplicate is offered instead) | Pass |
| Phase 3 | Older pages | Old entrance effects keep their fixed 32/40 px distances: "px" strings are screen px, plain numbers are design px. Found in Chrome: they first scaled with the screen (28.6 px at 1280 × 720). Fixed. The Hero's entrance renders from the same keyframes as before | Pass after fix |

### Known limits (ponytail ledger)

- Fonts load one weight per typography role. The Ekdanta preset spreads its roles to load Poppins 500/600/700/800 and Hind 400/500/600. If an editor changes the Tables role (Poppins 700) or the Testimonials role (Hind 600), that weight stops loading and the browser fakes it. Fix when it matters: an "extra weights" field per family.
- Motion "groups" (sub-timelines with progress 0–1) are not built: track ranges are scroll px only. Add them if a Phase 4 transcription needs them.
- CSS-keyframe presets can't play backwards, so a hover track using one just pauses. Hover and click presets should be Anime.js keyframes (the built-in ones are).
- The design files' 146 CSS `@keyframes` are converted into Motion Library presets in Phase 4, section by section (named after their sections), not in bulk now.
- Admin builder buttons use AdminLTE's `btn-xs` (about 22 px tall), as the rest of the builder does. They stay within WCAG 2.5.8 through the spacing exception.
