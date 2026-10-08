# Ekdanta website: approved design reference

This folder is the source of truth for how the new ekdanta.in looks, reads and moves.
Put it in the CMS repository at `docs/ekdanta-design/`.

- `pages/desktop/`: one file per page, drawn on a 1440 × 900 screen.
- `pages/mobile/`: phone versions of the five final pages, drawn on a 390 × 844 screen.
- `assets/`: every image the designs use (file names below).

The live, playable version of the same designs is the design canvas:
https://claude.ai/artifact/AsisqupSd9XTDtFrbwy7H8
On the canvas, the "Final design" tab has the approved desktop pages. "Pages (in review)" has the other desktop pages. "Mobile (in review)" has the phone versions. Press Play on a board and scroll inside it to see the motion. Claude Code cannot open that link. Claude in Chrome can, if the person is signed in to claude.ai in Chrome.

## Page status

| Page | URL on the live site | Desktop file | Mobile file | Status |
|---|---|---|---|---|
| Home | `/` | Final-Home.dc.html | M-Home.dc.html | Final |
| Contact us | `/contact` | Contact.dc.html | M-Contact.dc.html | Final |
| Services | `/services` | Services.dc.html | M-Services.dc.html | Final |
| Thank you (after an enquiry) | `/thank-you` | Thank-You.dc.html | M-Thank-You.dc.html | Final |
| 404 | any missing URL | Not-Found.dc.html | M-Not-Found.dc.html | Final |
| About us | `/about` | About.dc.html | not drawn yet | In review |
| Work | `/work` | Work.dc.html | not drawn yet | In review |
| Case study (template for 6 brands) | `/work/{slug}` | Case-Study.dc.html | not drawn yet | In review |
| Service detail (template for 6 services) | `/services/{slug}` | Service-Detail.dc.html | not drawn yet | In review |
| Blog listing | `/blog` | Blog.dc.html | not drawn yet | In review |
| Blog article (template for every post) | `/blog/{slug}` | Blog-Post.dc.html | not drawn yet | In review |

For pages with no mobile drawing yet, follow the patterns of the five mobile pages: single column, the same scroll lengths, the same motion ideas, and nothing overlapping at 360–430 px wide.

## How to read a `.dc.html` file

Each file is a self-contained, data-driven prototype. A browser will not render it on its own, because it needs the design-canvas runtime. Read it as source:

1. `<helmet>` holds the fonts (Google Fonts: Poppins 500–800 for headings, Hind 400–600 for body text) and a `<style>` block. That block has the CSS keyframes for time-based motion: load-in, loops, morphs, spinners, pulses and marquees.
2. After `</helmet>` comes the markup. The root is a fixed 1440 × 900 (or 390 × 844) box. Inside it is one scroll container (`ref="{{scrollRef}}"`, `onScroll`). Inside that, the page is a stack of `<section id="…">` elements.
3. `{{name}}` holes in attributes and text are values that are recalculated on every scroll frame. `<sc-for list="{{items}}" as="it">` repeats markup for a list. `<sc-if value="{{flag}}">` shows markup conditionally. `onClick="{{fn}}"` and `onChange="{{fn}}"` are interactions.
4. `<script type="text/x-dc">` holds `class Component`. Its `renderVals()` returns every hole value for the current scroll position `y` (`this.state.y`). **This is where all scroll-driven motion is defined**, with comments numbered per section.

### The scroll model used on every page

- Each section is a tall `<section>`. Its height is the scroll length for that section. Inside it is a `position: sticky; top: 0` stage, the size of one screen, that stays pinned while its section scrolls past.
- Each section's top on the page is held in a constant, for example `T_MENU = 1300`. Where no constant exists, the top is the sum of the heights of the sections above it. Progress inside a section is `y - T_SECTION`.
- Values are mapped from progress with small helpers defined at the top of `renderVals()`:
  - `clamp(v, a, b)` and `lerp(a, b, k)`
  - `ease` (in-out quad), `easeOut` (out cubic), `easeBack`
  - `snap(raw, n)` makes a sequence dwell on each of `n` items, moving only for the last 60% of each step
  - `head(s)` is the standard heading reveal: opacity in over 420 px of scroll, starting 650 px before the section top, while rising 40 px (30 px in the mobile files)
  - `move(v)` returns 0 under reduced motion
- Reduced motion: when `prefers-reduced-motion: reduce` is set, `_reduce` is true and every value jumps to its final, readable state. A CSS media query also switches off keyframe animations. **The live site must do the same.**
- The design canvas has a "scroll position" control. The `y` values in comments and notes refer to that.

### Section map (heights are scroll lengths in px)

| Page | Sections in order (id and height) |
|---|---|
| Home (desktop) | j-top 1600, j-obstacles 3600, j-work 6000, j-reels 3400, j-brands 2400, j-services 3600, j-about 2250, j-contact 1800 |
| Home (mobile) | m-top 1600, m-obstacles 3600, m-work 6000, m-reels 3400, m-brands 2400, m-services 3600, m-about 2250, m-contact 1800 |
| Contact | c-top 1300, c-channels 3500, c-brief 1600, c-visit 1900, c-faq 3300, c-contact 1800 |
| Services | s-top 1300, s-menu 2400, s-deep 7300 (4 groups × 1600 + lead-in), s-process 2600, s-work 1900, s-price 2200, s-contact 1800 |
| Thank you | t-top 1300, t-next 2600, t-save 1500, t-wait 1700 (1600 on mobile), t-contact 1800 |
| 404 | n-top 1300, n-routes 1700, n-report 1300, n-contact 1800 |
| About | a-top 1300, a-story 3400, a-values 3000, a-team 2600, a-brands 2200, a-contact 1800 |
| Work | w-top 1300, w-cases 4900, w-reels 2400, w-clients 2200, w-contact 1800 |
| Case study | c-top 1300, c-overview 1800, c-gallery 2600, c-result 1800, c-next 1600, c-contact 1800 |
| Service detail | v-top 1300, v-incl 2000, v-steps 2600, v-proof 1700, v-price 1800, v-related 1500, v-contact 1800 |
| Blog | b-top 1300, b-feat 1500, b-posts 1880, b-sub 1600, b-contact 1800 |
| Blog article | p-top 1180, p-body (article, normal flow), p-more, p-contact 1800 |

The last section of every page (`*-contact`) is the same approved closing: "Now it's your turn to become a BIG BRAND!" with the contact card and the footer. Build it once and reuse it everywhere.

### Header (fixed, floating pill, outside the scroll stages)

| Where | What it contains |
|---|---|
| Desktop, home page | Logo; in-page links Why Ekdanta (`#j-obstacles`), Work (`#j-work`), Reels (`#j-reels`), Services (page), About (`#j-about`), Contact (page); button "Become a big brand" → the closing section |
| Desktop, other pages | Logo; Home, Work, Services, About, Contact; one button: "Get a custom quote" → `#s-price` on Services, "Build your brief" → `#c-brief` on Contact, "Become a big brand" → the closing section everywhere else (including the case study and service detail templates) |
| Desktop, everywhere | No WhatsApp button in the header. WhatsApp lives in the closing section. |
| Mobile | A smaller pill with the logo, a green WhatsApp button and a menu button. The two bars of the menu button animate into a cross. |
| Mobile, inside the menu | A dark full-screen overlay. The navigation links are big (32 px, weight 800) with the current page in green. Below them come a "Become a big brand" button and the line "Call 90994 17987 · Sales 63544 33194". The items rise in one after another. In the M-*.dc.html files, the menu is the `<nav class="mm-panel">` element, driven by the holes `mpOp`, `mmOp`, `bar1` and `bar2`. |
| Every page, both sizes | A 3 px orange (`#F2A230`) scroll-progress bar across the very top (hole `progW`). |

Every in-page link targets a section id (`#j-obstacles`, `#c-brief`, `#s-price`, `#*-contact` and so on). The live site needs the same anchors.

### What each page does (from the design canvas notes)

**Home.** Hero: the real Ekdanta elephant holds a torch. Its trunk keeps lifting and lowering it, and the torch beam (a clip-path polygon that follows the torch tip) moves with it while revealing "Aapki branding & marketing ke विघ्नहर्ता". The light opens over 10 seconds, or sooner when the visitor scrolls. Then: obstacles flip into services; the six real case studies; the reels carousel; 44 logos sort onto industry shelves with the 10+ / 10,000+ / 45+ / 2 stats; the four expertise groups as panels that pause fully in view; the founders' story ("कर्ता-धर्ता"); the Big Brand closing. Mobile: the elephant is tilted so the beam shines up over the headline, obstacles sit in two flipping columns, brand logos go onto shelves two per row, the services panels slide sideways with a dwell, there is a full-screen menu, and a green WhatsApp button stays in the header.

**Contact.**
1. Hero "Chalo, baat karte hain.": a tilted phone dials 90994 17987 key by key, the call connects with ringing pulses, and a WhatsApp-style bubble says "Namaste! How can we help your brand?".
2. Six ways to reach us: a green band slides down the list and holds on each channel. Click a channel to jump to it.
3. Build your brief: four steps with a road, an elephant walking along it, milestones, and confetti at the end. It opens WhatsApp pre-filled, and "Sent it? See what happens next" opens the thank-you page.
4. Visit us: the map draws itself, the route runs to Bhatar Char Rasta and the pin drops.
5. FAQ: a deck of question cards. Each scroll flicks the top card away, with a big counter and a progress bar; dots and arrows jump between cards.
6. Closing.

**Services.**
1. Hero "Ek partner. Poora kaam.": the four expertise groups and their services orbit the Ekdanta mark, and "We handle your …" keeps rolling through words.
2. Four expertise: a deck rises, fans out, deals into a row and flips face up. Tap a card to jump to it.
3. Deep dive: one pinned stage per expertise with a circle wipe between them. Each of the 24 services lights up as its animated tile builds, and a bottom bar jumps between the four groups.
4. How we work: a path draws itself with the Ekdanta mark riding it, and five steps pop in.
5. Proof: six case studies ride across and tilt as they pass the centre. On mobile the carousel stops on each card.
6. Pricing: Basic / Standard / Premium get stamped "NO FIXED PACKAGES" and tumble away, then an example plan rises.
7. Closing.

**Thank you.**
1. A dark "Sending your enquiry" button with a spinner morphs into a green circle and a tick draws itself. Signal rings spread, chips "Enquiry received" and "Our team is notified" pop in, "Dhanyavaad!" rises, and a live "Received today at …" pill shows the visitor's own time.
2. "Ab aage kya hoga?": an enquiry tag travels through four steps.
3. Save our number: a ringing phone switches from an unknown number to Ekdanta. The three numbers are tap-to-call, and a button downloads a contact card (vCard).
4. "Tab tak, thoda dekh lo": three cards deal in (work, Instagram, blog).
5. Closing.

On the live site, the thank-you page also fires the Meta Pixel "Lead" event and the Google Ads conversion.

**404.**
1. Two 4s drop in and the Ekdanta "e" rolls in as the 0, looking around and blinking. Loose pages drift and a dotted trail leads to a "?" pin. "Arre! Yeh page gayab hai."
2. Six cards (Home, Services, Work, About us, Blog, Contact us) and a search box that finds pages, services and blog articles.
3. "Batao, hum theek kar denge": a broken chain joins up and turns green, and the address bar changes from "Not found" to "Working". The button opens WhatsApp to report the link.
4. Closing.

The server must return a real 404 status.

**About (in review).**
1. Hero: "Ekdanta" as a dictionary entry, and a red ledger (chopda) that writes the four wins.
2. Story: the founders' cards, then five beats including a 100-dot grid.
3. "Hamare chaar vaade": a four-sided block turns to each promise.
4. Team roll call of seven roles.
5. Count-ups and three logo rows moving in opposite directions.
6. Closing.

**Work (in review).**
1. A tilted, drifting wall of real work.
2. The six case studies with a scroll-tracking index, a parallax window and each brand's colour.
3. Five reels rise into an arc and take turns in the spotlight.
4. All 44 logos with an industry filter that shuffles.
5. Closing.

**Case study (in review, one template, six brands).**
1. Hero in the brand's colour with an Instagram-style profile card.
2. Brief / approach / what we made, plus count-up facts.
3. A guided zoom across the grid.
4. Three result cards and a quote.
5. The next case study.
6. Closing.

**Service detail (in review, one template, six main services).**
1. Hero with a live scene per service: feed, ad tap, logo draw, site build, viewfinder, or CRM board.
2. What's included (cards dealt from a stack).
3. How it works (track).
4. Related work.
5. Pricing factors and an FAQ.
6. Works well with.
7. Closing.

**Blog (in review).**
1. Notebook-paper hero "Seekho. Phir badho." with a shuffling card stack and a topic ticker.
2. Featured article opens like a curtain.
3. Article grid with topic chips and search.
4. Weekly WhatsApp tips with a paper plane flying to a phone.
5. Closing.

**Blog article (in review).**
1. The headline types itself out with a cursor, over a brochure-versus-reel cover.
2. The article body: every block rises in, beside a sticky reading rail with "On this page" tracking, a minutes-left ring and share buttons. It has custom in-article blocks: a buyer-journey strip, "brochure says / reel shows" rows, a wide figure, a pull quote, reel ideas and a checklist.
3. Tags and an author box.
4. Keep reading.
5. Closing.

## Design tokens

| Token | Value | Used for |
|---|---|---|
| Ink | `#0D1410` | text, dark sections |
| Ekdanta green | `#2EB45D` | primary brand colour, buttons, highlights |
| Deep green | `#0A5A30` | dark green surfaces |
| Green (text on light) | `#1E8E47` | eyebrows and accents on light backgrounds |
| Mint | `#8FE3AE` | accents on dark backgrounds |
| Mint tint | `#DFF5E7`, `#CFE8D7` | chips and light text on dark green |
| Orange | `#F2A230` | secondary accent, focus ring (`outline: 3px solid #F2A230`) |
| Paper | `#F4F6F1` | light section background |
| Muted text | `#56605A`, `#7B867F` | body copy, captions |
| Lines | `#E4E8E2`, `#DDE2DA`, `#E6EEE9` | borders and placeholders |
| Dark surfaces | `#1E2823`, `#222E28`, `#2B3832`, `#0E3A22` | cards on dark backgrounds |

- Fonts: Poppins (500, 600, 700, 800; headings, buttons, labels; tight letter-spacing such as `-0.045em` on big headings) and Hind (400, 500, 600; body text). Both support Devanagari, which the designs use. Load the Devanagari subset for **both** fonts, because the home hero sets "विघ्नहर्ता" in Poppins 800 at 176 px. Other Devanagari in the designs: कर्ता-धर्ता, रिद्धि-सिद्धि, ॥ श्री गणेशाय नमः ॥.
- Shape: pill buttons (`border-radius: 999px`), cards with 16–24 px radius, soft long shadows such as `0 30px 60px -30px rgba(13,20,16,0.7)`.
- Press feedback: `.press:active { transform: scale(0.97) }`.

## Assets

| File | What it is |
|---|---|
| logo-colour.png | Ekdanta logo, green and grey, for light backgrounds (1594 × 415) |
| logo-white.png | Ekdanta logo, all white, for dark backgrounds |
| e-mark.png | The Ekdanta "e" mark on its own (used as the walking/rolling mark, the 404 "0", the orbit core) |
| elephant-head.png | Home hero elephant head (layered with the trunk) |
| elephant-trunk-torch.png | Home hero trunk holding the torch (rotates around its base) |
| case-trilok-infratech.jpg, case-yantra-design.jpg, case-heaven-solar.jpg, case-srk-dairy.jpg, case-her-and-he.jpg, case-valleyrides.jpg | Instagram grid of each case study |
| reel-1.jpg … reel-5.jpg | Reel cover frames |
| client-logos-strip-a.jpg, client-logos-strip-b.jpg | The 44 client logos as two horizontal sprite strips, each logo in a fixed-width cell. On the live site, upload each logo separately with its industry so they can be filtered and sorted. |

Still to supply (placeholders in the designs): a higher-resolution elephant, founder photos, one result number per case study, office hours, the Ahmedabad address, the reply time, and the answers marked `[Confirm …]`. Text in square brackets, such as `[Add office hours]`, is a placeholder: keep it as an editable field.

## Business details used in the designs

- Calls and WhatsApp: 90994 17987 (WhatsApp `https://wa.me/919099417987`), 90994 17949, Sales 63544 33194
- Email: info@ekdanta.in · Website: ekdanta.in · Instagram: instagram.com/ekdanta.in
- Office: B-402–403, Bhatar Trade Center, near Bhatar Char Rasta, Uma Bhawan Society, Bhatar, Surat, Gujarat 395017
- Directions: https://share.google/EAOxrObclZeY4QkQR
- Footer: "© 2026 Ekdanta Branding and Marketing. Surat and Ahmedabad." and "Aapki branding & marketing ke विघ्नहर्ता"
