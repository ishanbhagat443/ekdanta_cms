# Design System Master File

> **LOGIC:** When building a specific page, first check `design-system/ekdanta/pages/[page-name].md`.
> If that file exists, its rules **override** this Master file.
> If not, strictly follow the rules below.

---

**Project:** Ekdanta Branding and Marketing (ekdanta.in), built on Ekdanta CMS
**Generated:** 8 Oct 2026, written by hand from the approved design (`docs/ekdanta-design/DESIGN-README.md` and the `.dc.html` files). This is not generator output: never regenerate it, and never run `--persist --force` over it.
**Category:** Agency marketing site, scroll-driven storytelling
**Design Dials:** Variance 7/10 (fixed, approved compositions) | Motion 9/10 (pinned, scroll-scrubbed choreography) | Density 3/10 (one idea per screen)

---

## Global Rules

### Color Palette

Approved tokens. The CMS exposes them as CSS custom properties (`--tk-*`) through the Ekdanta theme preset; Ekdanta sections use the variables, never raw hex.

| Role | Hex | CSS Variable |
|------|-----|--------------|
| Ink: text, dark sections | `#0D1410` | `--tk-ink` |
| Ekdanta green: primary, buttons, highlights | `#2EB45D` | `--tk-green` |
| Deep green: dark green surfaces | `#0A5A30` | `--tk-green-deep` |
| Green, large text on light (≥ 24 px, or ≥ 18.66 px bold) | `#1E8E47` | `--tk-green-text` |
| Green, small text on light (approved a11y fix) | `#1B8141` | `--tk-green-text-sm` |
| Mint: accents on dark | `#8FE3AE` | `--tk-mint` |
| Mint tints: chips, light text on dark green | `#DFF5E7`, `#CFE8D7` | `--tk-mint-1`, `--tk-mint-2` |
| Orange: secondary accent, progress bar, focus ring | `#F2A230` | `--tk-orange` |
| Paper: light section background | `#F4F6F1` | `--tk-paper` |
| Muted text: body copy | `#56605A` | `--tk-muted` |
| Muted text: captions, large text only | `#7B867F` | `--tk-muted-2` |
| Muted text: small captions (approved a11y fix) | `#6A736D` | `--tk-muted-2-sm` |
| Counter suffix (approved a11y fix for `#9AA39E`) | `#85908A` | `--tk-suffix` |
| Lines | `#E4E8E2`, `#DDE2DA`, `#E6EEE9` | `--tk-line-1`, `--tk-line-2`, `--tk-line-3` |
| Dark surfaces: cards on dark | `#1E2823`, `#222E28`, `#2B3832`, `#0E3A22` | `--tk-dark-1` … `--tk-dark-4` |
| Finale band (approved a11y fix; buttons keep `#2EB45D`) | `#2CAA58` | `--tk-finale` |
| Finale footer line (approved a11y fix) | `#0D351F` | `--tk-finale-foot` |

*Notes: brand-colour fills per case study (navy, maroon, forest, ochre, slate, teal) and per expertise group are content, edited on the collection items, not tokens.*

### Typography

- **Heading Font:** Poppins 500, 600, 700, 800 (headings, buttons, labels, navigation)
- **Body Font:** Hind 400, 500, 600 (body copy)
- **Scripts:** Latin + **Devanagari for both families**. The home hero sets "विघ्नहर्ता" in Poppins 800 at 176 px. Other Devanagari: कर्ता-धर्ता, रिद्धि-सिद्धि, ॥ श्री गणेशाय नमः ॥.
- **Google Fonts:** `https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&family=Hind:wght@400;500;600&display=swap`. The css2 API serves the Devanagari files by `unicode-range` automatically; verify in the browser that they load for both families.
- **Mood:** bold, warm, plain-spoken, Hinglish

| Role | Desktop (1440 × 900) | Mobile (390 × 844) |
|------|----------------------|--------------------|
| Display ("BIG BRAND!", "404") | Poppins 800 · 200 px (up to 380 px) · lh 0.9 · −0.06em | 88 px (up to 170 px) · lh 0.9 · −0.06em |
| Hero H1 | Poppins 800 · 176 px · lh 1.2 (Devanagari); other heroes 120 px · lh 0.95 · −0.055em | 78 px · lh 1.2; others 64 px · lh 0.98 |
| Section heading H2 | Poppins 800 · 54–64 px · lh 1.02–1.04 · −0.045em | 31–32 px · lh 1.04 · −0.045em |
| Sub heading H3 | Poppins 800 · 38–46 px · −0.04em | 24 px · −0.04em |
| Lead line | Poppins 600 · 34–44 px · −0.02em | 20–21 px · −0.02em |
| Card title | Poppins 700–800 · 20–28 px · −0.03em | 18–19 px · −0.03em |
| Body | Hind 400 · 17–19 px · lh 1.5 | Hind 400 · 13.5–16 px · lh 1.4–1.5 |
| Button | Poppins 600 · 15–16 px (header 14 px) | 15 px |
| Navigation | Poppins 500 · 15 px | menu overlay: Poppins 800 · 32 px · −0.04em |
| Eyebrow / label | Poppins 600 · 12–14 px · uppercase · +0.1–0.12em | 11–12 px · +0.1em |

- **Text floors on the live site:** body copy never below 13 px, captions never below 12 px (`clamp()` against the `--u` scale). Text drawn inside illustrations (phone screens, tiles, mock UIs, 8.5–11 px) is artwork and `aria-hidden`; the same information must exist as real text or not be needed.

### Spacing Variables

The design is drawn as absolute compositions on fixed artboards. On the live site every composition length is written in **design px × `--u`**: `--u = min(100vw / 1440, 100svh / 900)` on desktop (≥ 992 px), `min(100vw / 390, 100svh / 844)` on mobile (≤ 767 px). 768–991 px uses the mobile composition, centred, scale capped at 1.35.

| Token | Value | Usage |
|-------|-------|-------|
| `--tk-gutter` | `96u` desktop / `24u` mobile | Left and right edge of every composition |
| `--tk-gap-xs` | `8u` | Chip gaps, icon-to-label |
| `--tk-gap-sm` | `12u` | Button groups, list rows |
| `--tk-gap-md` | `18u` – `24u` | Card stacks, grid gaps |
| `--tk-gap-lg` | `40u` | Stat columns |

### Shadow Depths

| Level | Value | Usage |
|-------|-------|-------|
| `--tk-shadow-sm` | `0 8px 20px -8px rgba(13,20,16,0.4)` | Walker mark, small floating chips |
| `--tk-shadow-header` | `0 10px 30px -18px rgba(13,20,16,0.5)` | Floating header pill |
| `--tk-shadow-card` | `0 30px 60px -30px rgba(13,20,16,0.7)` | Cards on light (the README's "soft long shadow") |
| `--tk-shadow-lift` | `0 40px 80px -30px rgba(0,0,0,0.7)` | Large photo cards on dark |
| `--tk-shadow-finale` | `0 40px 80px -40px rgba(13,20,16,0.5)` | Finale contact card |

### Radii

| Token | Value | Usage |
|-------|-------|-------|
| `--tk-radius-pill` | `999px` | Every button, chip, header pill |
| `--tk-radius-card` | `22px` (16–24) | Cards |
| `--tk-radius-photo` | `26px` | Work and reel photo cards |
| `--tk-radius-panel` | `28px` | Finale contact card |
| `--tk-radius-tile` | `10px` | Client logo tiles |

---

## Component Specs

### Buttons

```css
/* Primary: green pill, ink text (6.9:1) */
.ek-btn--primary { background: var(--tk-green); color: var(--tk-ink); border-radius: 999px;
  font: 600 15px/1 'Poppins'; height: 48px; padding: 0 18px; } /* header: 42px tall, 14px text */
/* Dark: ink pill, white text */
.ek-btn--dark { background: var(--tk-ink); color: #fff; } /* hero: 56px tall, 16px text */
/* Outline: transparent, 1.5px ink edge */
.ek-btn--outline { box-shadow: inset 0 0 0 1.5px currentColor; background: transparent; }
/* Press feedback, everywhere */
.press { transition: transform 100ms ease-out; } .press:active { transform: scale(0.97); }
```

Heights: 42 px (header), 48 px (finale card), 52 px (mobile hero), 56 px (desktop hero and menu CTA). Every button and link target is at least 44 × 44 px; where a design element is smaller (the 40 px reel play badge), extend the hit area with padding, not the drawing.

### Focus ring (approved accessibility fix)

```css
:focus-visible { outline: 3px solid var(--tk-orange); outline-offset: 3px;
  box-shadow: 0 0 0 8px var(--tk-ink); } /* ink fills the 3 px gap and shows 2 px beyond the orange */
html { scroll-padding-top: calc(var(--tk-header-h) + 16px); } /* focus is never hidden behind the pill */
```

The orange outline stays as designed. The ink halo under it is what keeps focus visible on green (6.9:1) and white (18.7:1). CSS can't leave a transparent gap between two rings without knowing the background, so the ink also fills the 3 px offset gap: element → 3 px ink → 3 px orange → 2 px ink.

### Cards

- Light card: paper or `#DDE2DA` fill, radius 22 px, padding 26 px, no border.
- Photo card: radius 26 px, `object-fit: cover`, `--tk-shadow-lift`.
- Flip card: two faces, `backface-visibility: hidden`, perspective 1200 px (900 px mobile).

### Inputs (Brief Builder)

- Chips: pill, 1.5 px inset ring `#CFD8D1`; selected = ink fill, white text, check icon, `aria-pressed`.
- Text fields: visible label above, never placeholder-only; errors under the field, in text.

### Header

| Where | Contents |
|-------|----------|
| Desktop | Fixed floating pill: top 16 px, inset 32 px, height 60 px, white 95 %, `--tk-shadow-header`. Logo (32 px tall), inline links (Poppins 500 15 px; current page = 22 % green pill + 600), one CTA button. No WhatsApp button. |
| Mobile | Pill: top 10 px, inset 12 px, height 56 px, logo 27 px; green WhatsApp button 44 × 44 (`aria-label`); ink menu button 44 × 44 whose two bars turn into a cross. |
| Mobile menu | Ink full-screen overlay, links Poppins 800 32 px (current page green), then "Become a big brand" (56 px) and "Call 90994 17987 · Sales 63544 33194"; items rise in one after another (`transition-delay` 60 ms + 45 ms steps). |
| Every page | 3 px orange scroll-progress bar across the very top. |

### Modals

None in the design. Do not introduce modals for content the design shows in place.

---

## Style Guidelines

**Style:** flat colour blocks, big tight Poppins headlines, real photography, pill controls, long soft shadows. Hinglish and Devanagari copy is part of the voice; never translate or rewrite it.

**Key Effects:** sticky full-screen stages; scroll-scrubbed flips, fans, wipes and carousels; count-ups; word-by-word highlights; CSS loops (trunk and beam, orbit, phone dial, blinking "e").

### Page Pattern

**Pattern Name:** Pinned scroll story

- **Conversion Strategy:** every page ends in the same Big Brand Finale (contact card + footer); one primary CTA per screen.
- **CTA Placement:** hero CTA pair (dark pill + WhatsApp outline), header CTA, finale card.
- **Section Order:** see the section map in DESIGN-README; each section is a tall block (its height = scroll length) with a `position: sticky; top: 0` stage one screen tall.

---

## Motion

- **Model:** progress inside a section = scroll y − section top, in design px. Values come from `clamp((p − start) / length)`, eased, mapped with `lerp`. Scroll lengths scale with screen height (`stage height ÷ 900`, mobile `÷ 844`) so pacing is identical on every screen.
- **Easings:** `ease` = in-out quad, `easeOut` = out cubic, `easeBack` = out back (c1 1.70158).
- **Heading reveal (`head`):** opacity in over 420 px of scroll, starting 650 px before the section top, rising 40 px (30 px on mobile).
- **Dwell (`snap(raw, n)`):** each of n items holds, then moves only in the last 60 % of its step (reels use 65 %).
- **Time-based motion:** CSS `@keyframes` (load-ins and loops), paused while their section is off-screen.
- **Properties:** transform, opacity, clip-path and CSS variables. Width bars → `scaleX`; colour changes → cross-faded layers where they look identical; layout properties only on small elements.

### Reduced motion

`prefers-reduced-motion: reduce`, the site-wide "Reduce motion" setting and `?reduce-motion=1` all give the same result: every value shows its final readable state, step sequences switch without tweening, loops stop, anchor scrolling is instant. With JavaScript off, stages fall back to normal flow and all text is readable.

### Pause control (approved 8 Oct 2026)

WCAG 2.2.2 (Level A) needs a way to pause motion that starts automatically, lasts more than 5 s and runs beside other content (trunk and beam, orbit, marquees, phone dial, blinking "e"). A small text button **"Pause animations"** sits in the Big Brand Finale footer line and inside the mobile menu. One tap switches the page to the reduced-motion state; the button then reads "Play animations". The choice is remembered per visitor.

---

## Anti-Patterns (Do NOT Use)

- ❌ New colours, fonts or copy not in the design (placeholders in `[…]` stay as editable defaults)
- ❌ Emoji as icons; use the design's inline SVGs
- ❌ Anything that only works on hover
- ❌ Scroll-jacking: scrolling is always native; stages are `position: sticky`
- ❌ Reading layout inside the animation frame loop
- ❌ Animating width, height, top or left on large elements
- ❌ Raw hex in Ekdanta components; use `--tk-*`
- ❌ Invisible focus, or focus landing on parts that are still hidden

---

## Pre-Delivery Checklist

Run on every Ekdanta section (desktop and mobile) and every new admin screen; record results in `docs/ekdanta-qa.md`.

- [ ] Text contrast ≥ 4.5:1 (≥ 3:1 for text ≥ 24 px, or ≥ 18.66 px bold), using the approved shades above
- [ ] Visible focus ring (orange + ink) on every interactive element; nothing focused is hidden under the header
- [ ] Touch targets ≥ 44 × 44 px
- [ ] `aria-label` on icon-only buttons (WhatsApp, menu, arrows, dots)
- [ ] Alt text on content images; decorative art `alt=""` / `aria-hidden`
- [ ] No emoji used as icons
- [ ] Nothing works only on hover
- [ ] Reduced motion shows the final readable state; JavaScript off leaves all text readable
- [ ] No horizontal scroll from 360 px to 2560 px; no overlap at 1280 × 720 and 360 × 640
- [ ] CLS < 0.1; images have dimensions; below-the-fold images lazy-load
