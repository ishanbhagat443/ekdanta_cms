# Page Override: Home (mobile)

> Overrides `design-system/ekdanta/MASTER.md` for the **mobile** composition (≤ 767 px, and 768–991 px scaled). Desktop follows MASTER. Exact per-section numbers (positions, distances, ranges) are transcribed from `docs/ekdanta-design/pages/mobile/M-Home.dc.html` into each section's motion defaults JSON; this file records the rules that differ.

**Artboard:** 390 × 844. **Gutter:** 24 px. **Header:** mobile pill (MASTER → Header) with the green WhatsApp button and the menu button; the full-screen menu is the `mm-panel` overlay. **Heading reveal:** rises 30 px, not 40. **Type:** the mobile column of the MASTER type scale.

## Section rules (same scroll lengths as desktop)

| Section | Mobile rule |
|---|---|
| Home Hero (Torch) `j-top` | The elephant group is tilted: `translate(161.8px, 494.5px) rotate(22deg) scale(0.62)`, so the beam shines **up** over the headline. The beam polygon is the mobile one (`--mx`/`--my`, keyframes `ekBeamM`). Headline 21 px + 78 px Devanagari; chips wrap. CTA pair 52 px tall: "Become a big brand" + "WhatsApp" (short label). |
| Obstacles (Flip Cards) `j-obstacles` | Two flipping columns of 165 × 162 px cards (perspective 900 px); counter sits under the heading; walker track runs 24 → 322 px. Under reduced motion each card is either face up or face down (no half-flips). |
| Work Showcase `j-work` | Scatter positions and the carousel spacing are the mobile ones (`d × 420`, rise 30, case text rise 50). Reduced motion: no movement, cases cross-fade by index. |
| Reels Carousel `j-reels` | Card spacing 205 px (desktop 300), rise 480, ring 3 px; background columns: 3 instead of 5. |
| Brand Shelves `j-brands` | Shelves two logos per row; tile scale 0.548; the four shelves sit at the positions in `CATS`; under reduced motion logos go straight to their shelf. |
| Services Panels `j-services` | Panels slide sideways **with a dwell**: each panel rests fully in view before the next (`snap`, 40 % hold); outline word drift 140 px (desktop 260). |
| Founders Story `j-about` | Same word-by-word highlight and count-ups as desktop, single column. |
| Big Brand Finale `contact` | "BIG BRAND!" 88 px; the footer's two lines stack. Card layout: see the M-file. |
