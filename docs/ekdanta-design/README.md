# Ekdanta website: final design reference (18 Oct 2026)

This folder is the source of truth for how ekdanta.in looks, reads and moves on every screen size. It replaces the older `docs/ekdanta-design/` folder (from 8 Oct): copy this one over it.

The live, playable version of the same boards is the design canvas: https://claude.ai/artifact/AsisqupSd9XTDtFrbwy7H8. Claude Code can't open that link. Claude in Chrome can, if the person is signed in to claude.ai in Chrome.

## Five screen sizes, 13 pages each

| Folder | Frame | Use it for screens that are |
|---|---|---|
| `pages/desktop-1440x900/` | 1440 × 900 | desktop monitors, 1280 wide and up |
| `pages/laptop-1536x735/` | 1536 × 735 | wide, short laptop windows |
| `pages/tablet-landscape-1180x820/` | 1180 × 820 | tablets held sideways and small laptops, 1024 to 1279 wide |
| `pages/tablet-portrait-820x1180/` | 820 × 1180 | tablets held upright, 768 to 1023 wide (and portrait tablets up to 1366 tall) |
| `pages/mobile-390x844/` | 390 × 844 | phones, up to 767 wide |

| Page | Address | File in every folder |
|---|---|---|
| Home | `/` | `Home.dc.html` |
| Services | `/services` | `Services.dc.html` |
| Work | `/work` | `Work.dc.html` |
| About | `/about` | `About.dc.html` |
| Blog | `/blog` | `Blog.dc.html` |
| Contact | `/contact` | `Contact.dc.html` |
| Case study (template) | `/work/{slug}` | `Case-Study.dc.html` |
| Service detail (template) | `/services/{slug}` | `Service-Detail.dc.html` |
| Blog article (template) | `/blog/{slug}` | `Blog-Post.dc.html` |
| Thank you | `/thank-you` | `Thank-You.dc.html` |
| 404 | any missing address | `Not-Found.dc.html` |
| Privacy Policy | `/privacy-policy` | `Privacy.dc.html` |
| Terms of Service | `/terms-of-service` | `Terms.dc.html` |

Links inside a board point to other boards with the canvas names: `Privacy.dc.html` (desktop), `LP-Privacy.dc.html` (laptop), `TL-…` (tablet landscape), `TP-…` (tablet portrait), `M-…` (mobile). Read them as the page addresses above.

## How the five sizes relate

- **Desktop** is the master composition for big screens.
- **Laptop and tablet landscape** are the desktop boards drawn by the screen-fit rule, not new layouts:
  - u = the smaller of (screen width ÷ 1440) and (screen height ÷ 900).
  - Each part is drawn u times its design size.
  - Backgrounds and full-frame layers run to the screen's edges.
  - Left groups keep their distance from the left edge, right groups from the right edge, and centred groups stay centred.
  - A section with a part wider than about 950 design px stays centred as one piece.
  - Parts that sit on the frame's bottom edge (the elephant) sit on the screen's bottom edge.
  - The Home hero beam's tip follows the torch.

  The live site does this today with the "Ekdanta screen fit, v2" snippet in Theme Customizer. The laptop and tablet-landscape boards show the result to match.
- **Tablet portrait:**
  - Home, Services, Contact and Thank you use the phone layout, drawn 1.398 times larger and centred, with the backgrounds running edge to edge.
  - The other nine pages have their own tablet layouts: the phone layout widened to a 724 px column with 48 px side margins and larger type (eyebrow 14, section heading 54, lead 20, body 19, against 12 / 32 / 15.5 / 15.5 on phones), and 2-column grids where the phone has one column.
- **Mobile** is the phone composition. On small phones (360 × 640), text keeps a readable minimum size, so rows must wrap rather than run off the screen.

## Header and footer on every board

**Header, the same on every page.**
- **Menu:** Home, Services, Work, About, Blog, Contact. The current page is marked with a light green pill and `aria-current="page"`. Detail pages mark their parent; Privacy, Terms, Thank you and 404 mark nothing.
- **Button:** "Become a big brand", which goes to the page's closing section (`#contact`).
- **Desktop, laptop and tablet landscape:** a floating white pill with the logo, the six links and the green button.
- **Tablet portrait:** the logo, a dark "Become a big brand" button, a green WhatsApp button and the menu button.
- **Phone:** the logo, a green WhatsApp button and the menu button.
- **Full-screen menu (phone and tablet portrait):** the six links, then "Become a big brand", then "Call 90994 17987 · Sales 63544 33194".
- **Every board:** a 3 px orange scroll-progress bar and a "Skip to main content" link.

**Footer, inside the closing section.**
- **Desktop, laptop and tablet landscape:** two rows above a thin line.
  - Row 1: the six page links on the left. On the right: Privacy Policy, Terms of Service and a "Cookie settings" button.
  - Row 2: the legal line with the GSTIN on the left, and "Aapki branding & marketing ke विघ्नहर्ता" on the right.
- **Phone and tablet portrait:** three lines.
  1. Privacy Policy · Terms of Service · Cookie settings
  2. The legal line
  3. "Surat and Ahmedabad · Aapki branding & marketing ke विघ्नहर्ता"

**Cookie bar** (the `ck-card` element), shown until the visitor chooses:
- Desktop, laptop and tablets: a slim white pill along the bottom.
- Phone: a white card at the bottom.
- "Cookie settings" in the footer brings it back.

## How to read a `.dc.html` file

Each file is a data-driven prototype that needs the design-canvas runtime, so read it as source rather than opening it in a browser:

1. `<helmet>` holds the fonts (Poppins for headings, Hind for body text) and a `<style>` block with the CSS keyframes for time-based motion.
2. After `</helmet>` comes the markup. The root is a fixed frame (for example 1440 × 900) holding one scroll container, with the page as a stack of `<section id="…">` elements. A pinned section is a tall section holding a `position: sticky` stage one screen tall.
3. `{{name}}` holes are values recalculated on every scroll frame. `<sc-for>` repeats markup for a list. `<sc-if>` shows markup conditionally.
4. `<script type="text/x-dc">` holds `class Component`. Its `renderVals()` returns every hole for the scroll position `y`, so all scroll-driven motion is defined there. It honours reduced motion (`_reduce`): every value jumps to its final, readable state.

Images point to `../../assets/…`.

## Assets

| File | Used for |
|---|---|
| `logo-colour.png`, `logo-white.png` | logo on light and dark backgrounds |
| `e-mark.png` | the Ekdanta mark (decorative next to text) |
| `elephant-head.png`, `elephant-trunk-torch.png` | Home hero; the trunk layer moves |
| `case-*.jpg` (6) | the six case-study Instagram grids |
| `reel-1.jpg` … `reel-5.jpg` | reel posters |
| `client-logos-strip-a.jpg`, `client-logos-strip-b.jpg` | client logo sheets |
| `founder-vaishali-hirpara.jpg`, `founder-dishant-bhagat.jpg` | founder photos (About story arch cards, Home founders) |
| `ekdanta-og-1200x630.jpg` | default social share image (the Home hero) |
