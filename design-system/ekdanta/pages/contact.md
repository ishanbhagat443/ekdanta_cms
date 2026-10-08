# Page Override: Contact (mobile)

> Overrides `design-system/ekdanta/MASTER.md` for the **mobile** composition (≤ 767 px, and 768–991 px scaled). Desktop follows MASTER. Exact per-section numbers (positions, distances, ranges) are transcribed from `docs/ekdanta-design/pages/mobile/M-Contact.dc.html` into each section's motion defaults JSON; this file records the rules that differ.

**Artboard:** 390 × 844. **Gutter:** 24 px. **Header:** mobile pill (MASTER → Header) with the green WhatsApp button and the menu button; the full-screen menu is the `mm-panel` overlay. **Heading reveal:** rises 30 px, not 40. **Type:** the mobile column of the MASTER type scale.

## Section rules

| Section | Mobile rule |
|---|---|
| Contact Hero (Phone Dial) `c-top` | Same dialling loop (keyframes `kp*`); scrolling tilts the phone away. |
| Contact Channels `c-channels` | The green band slides down the list and holds on each channel (`snap`). Tap a channel to jump to it. |
| Brief Builder `c-brief` | Text, card, road, milestones, elephant and chips assemble as you scroll; the four steps and the WhatsApp hand-off are the same as desktop. |
| Visit Us (Map) `c-visit` | The map draws itself, the route runs and the pin drops. |
| FAQ Deck `c-faq` | **The answered card is thrown sideways**, never upwards, so it never covers the heading. Counter, progress bar, dots and arrows as desktop. |
| Finale | As Home. |
