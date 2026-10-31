# Ekdanta site: admin guide

How to build and edit the Ekdanta website in this CMS. Below this guide, the same page lists every page in build order and every Ekdanta section with its fields, motion parts and default motion, read from the CMS itself, so it is always current.

## Before you start

The setup command puts the shared content in place: `php artisan ekdanta:seed`. It never overwrites what you have changed. It adds:

- **Media Library → Ekdanta folder:** the design's images. Each has a stable key such as `ekdanta/logo-colour`, so sections find them on any server.
- **Collections:** case studies, service groups, services, client logos, reels, FAQs and team roles.
- **Design → Theme Customizer:** the Ekdanta theme (colours, fonts, focus ring), also kept under Backups as "Ekdanta".
- **Settings → Business:** phone numbers, email, offices and the legal entity (legal name, trade name, constitution, GST number, registered address).
- **Settings → SEO Defaults → OG image:** the share image (the Home hero), if none is set.
- **Menus:** "Ekdanta main menu" (Home, Services, Work, About, Blog, Contact), used on every page: in the header, and as the Big Brand Finale's footer links.
- **Header Builder:** the floating pill header, with the "Become a big brand" button.
- **Design → Block Library:** the **Big Brand Finale** saved block (the green closing section of every page).
- **Engagement → Forms:** the **Ekdanta Brief** form, used by the Brief Builder: name and phone required, and a consent box on the last step.
- **Blog:** the design's articles as drafts titled "SAMPLE – …". Replace or delete them.

It also fills **Privacy Policy** and **Terms of Service** (from `docs/ekdanta-content/legal`), but only while those pages have no sections: the four legal sections, then the Big Brand Finale, left as drafts. Every `[Add …]` in their text is yours to fill.

It does not create the other pages. Build them as below.

## Build a page

1. **Pages** → *Add a page*: type the title and web address (for example `contact`), choose *Blank page*, then *Create page*.
2. In the builder, click **Add a section**. The Ekdanta sections sit under the *Ekdanta* heading; type "Ekdanta" in the filter box to see only them. Add the page's sections in the order listed under **Pages, in order** below.
3. Last, click **Add a saved block** and choose **Big Brand Finale**. It stays linked: edit it once in the Block Library and every page changes. It replaces the site footer on its own.
4. Open **Page settings** (right column):
   - *Use this page as*: Home page, 404 page or Thank-you page, for those three pages.
   - *Template for*: Case study, Service or Blog article, for the three template pages.
   - *Header menu*: leave it on the site default ("Ekdanta main menu") on every page, Home and the blog included.
   - *Show page title banner*: off.
5. Set *Status* to Published and save. Use the eye icon (Preview) to check it at desktop and phone sizes; the preview bar has 1440 × 900, 1280 × 720, 390 × 844 and 360 × 640, and a *Reduce motion* switch.

A role or template takes effect on the live site once its page is published. Template pages have no address of their own: they draw every case study at `/work/…`, every service with its own page at `/services/…` and every article at `/blog/…`. In the builder preview, pick which item to show with the item picker.

## Edit a section's words, pictures and links

Open the section in the builder (click its name). The **Desktop** tab holds every field. The **Mobile** and **Tablet** tabs change only the fields that may differ on those screens, and show what a field uses when you leave it empty (in grey):

- An empty **Mobile** field uses the Desktop value.
- An empty **Tablet** field uses the Mobile value, then the Desktop value. Fill it only when tablets need their own words.

Which layout a screen gets:

- **Desktop:** screens 992 px wide and up held sideways, and desktop monitors.
- **Tablet:** screens 768 to 991 px wide, and every tablet held upright (up to 1366 px tall, so a 1024 × 1366 iPad Pro too). Home, Services, Contact and Thank you show the phone layout, larger and centred. The other pages, and the Big Brand Finale, widen it to the screen.
- **Phone:** screens up to 767 px wide.

**The screen fit is built into the section engine.** On a screen that isn't shaped like 1440 × 900 (a 1536 × 735 laptop, a 1180 × 820 tablet held sideways), each part of a section keeps its place against the screen's left edge, right edge or centre, and full-screen layers (backgrounds, the Home beam, the reel wall, the services panels) reach the edges. No Theme Customizer code is needed: don't add the "Ekdanta screen fit, v2" snippet back. Which edge a part keeps to is set for each section type in code, not in the section editor. If a part sits in the wrong place, note the page, the screen size, the section and where the part should sit, for Claude Code.

- Text in square brackets, such as `[Add office hours]`, is a placeholder for you to replace.
- Pictures come from the Media Library.
- A link field takes:
  - a page (`/contact`), or a section on the same page (`#c-brief`);
  - a web address (`https://…`), `tel:…` or `mailto:…`;
  - `wa:your message`: WhatsApp to the business number, with the message filled in;
  - from Settings → Business: `phone:1` (call its first number), `email:` and `office:1` (directions to its first office).
- In text, `{phone:1}`, `{phone:2}`, `{whatsapp}`, `{email}`, `{reply_time}`, `{hours:1}` (the first office's hours), `{gstin}`, `{registered_address}` and `{legal_line}` show those details from Settings → Business. `{legal_line}` reads "a brand of Vedanta Advisor (proprietorship). GSTIN 24GHNPD1228K1ZC.", built from the trade name, constitution and GST number. In the Big Brand Finale's footer line, `{year}` is this year.
- Anchor: each section's last field sets its `#name` for links.

## Privacy, consent and cookies

- **The brief's consent box.** In *Forms → Ekdanta Brief*, the last field is a *Consent* box: its label is the sentence the visitor agrees to, with a note, a link (the Privacy Policy) and shorter wording for phones. It starts unticked and the brief can't be sent without it; the server checks too. A ticked box is saved with the time and the exact words shown, in the submission, the enquiry and the CSV export. Any form can have one: add a field of type *Consent*.
- **The cookie bar** (Settings → Cookies) shows until a visitor chooses; their choice is kept for 180 days, and the footer's *Cookie settings* button brings the bar back. Until a visitor taps *Allow all*, no Google Analytics, Tag Manager (SEO Defaults), Meta Pixel or Google Ads conversion tag (Page settings → Conversion tags) loads. Google's tags then start with Consent Mode v2 at "denied", updated to "granted". To make your own script wait for the same choice, add it as `<script type="text/plain" data-tracking>…</script>` (Developer Mode, page code). Switching the bar off loads the tags for everyone.
- **Skip to main content** is the first stop for keyboard users on every page; it appears at the top left when focused.
- **The site footer** is in the Big Brand Finale: the page links come from a menu (*Footer: page links*), then *Privacy Policy*, *Terms of Service*, *Cookie settings* and *Pause animations*, the legal line (`{legal_line}`) and the tagline. Phones and tablets held upright show three lines; set the phone wording in the block's Mobile tab.

## Settings that feed the sections

Change these once; every section that shows them follows.

| Setting | Shown in |
|---|---|
| Settings → Business → Phones | Big Brand Finale (contact card), Contact Hero (phone dial), Contact Channels, Brief Builder call line, Save Our Number (and its contact file, `/contact.vcf`), the header's phone menu call line, WhatsApp links (the number marked WhatsApp) |
| Settings → Business → Email | Big Brand Finale, Contact Channels (`{email}`, `email:`) |
| Settings → Business → Reply time | Any text with `{reply_time}`, e.g. Next Steps' "[Add reply time]" chip |
| Settings → Business → Offices | Big Brand Finale (address), Contact Channels and Visit Us (`office:1` directions), Visit Us office hours (`{hours:1}`) |
| Settings → Business → Legal name, Trade name, Constitution, GST number, Registered address | The Big Brand Finale's footer line (`{legal_line}`), and any text with `{gstin}` or `{registered_address}` |
| Settings → Cookies | The cookie bar's words and its Privacy link, and whether it asks at all |
| Menus → Ekdanta main menu | The header on every page, and the Big Brand Finale's footer page links (its *Footer: page links* field) |
| Collections → Case studies | Work Showcase, Proof Carousel, Work Hero, Case Studies Scroller, Related Work, the case study template (Case Hero, Overview, Gallery, Results, Next Case) |
| Collections → Service groups and Services | Services Panels, Expertise Cards, Service Deep Dive, the service template (Service Hero, What's Included, How It Works, Service Pricing + FAQ, Works Well With) |
| Collections → Client logos | Brand Shelves, Who We Work With, Client Logo Wall |
| Collections → Reels | Reels Carousel, Reels Arc, Work Hero |
| Collections → FAQs | FAQ Deck (group "contact"), Service Pricing + FAQ (group = the service's address) |
| Collections → Team roles | Team Roll Call |
| Blog → Posts and Categories | Featured Article, Article Grid (topic chips are the categories), the article template, Keep Reading (the article's related posts first) |
| Engagement → Forms → Ekdanta Brief | Brief Builder (pick another form in the section if you like) |

In a section that lists collection items, an empty list field shows every published item in the collection's order. To show only some, type their addresses separated by commas, for example `yantra-design, heaven-solar`.

## Change an animation

Open the section, then its **Motion** tab.

- **Device tabs** (Desktop, Tablet, Mobile): motion is set per device. A device with no changes uses the design's motion.
- **Scroll stage**: whether the section pins a full-screen stage while you scroll, and for how long (in design px).
- **Tracks**: each track moves one part of the section. The part list shows the section's parts (they are listed per section below).
  - *When*: tied to scrolling, when it scrolls into view, when the page opens, loops, on hover or keyboard focus, on click, or when its step is active.
  - *Preset*: pick another movement from the Motion Library to swap it, or choose *Custom keyframes* and write your own (JSON).
  - *Timing, scroll range and more*: duration, delay, stagger, ease, direction, and for scroll tracks the scroll range in px from the section's top.
  - *Reduced motion*: what a visitor who asked for less motion sees (the final state, the part as drawn, the part hidden, or a switch at its point in the scroll).
  - Turn a track off with its switch; *Reset this track* puts the design's version back.
- **Preview scroll position**: drag it to see the section at any point of its scroll in the preview on the right.
- **Save motion** stores your changes. **Reset whole section to design default** removes them all.

### Switch motion off

- One section on one device: Motion tab → that device → *No motion on …*.
- The whole site: Design → Theme Customizer → Animations → *Enable Animations* off. Every visitor then sees the still version.
- Visitors can pause animations themselves with the *Pause animations* button in the Big Brand Finale and the phone menu, and visitors whose device asks for reduced motion always get the still version.

### The Motion Library

Design → **Motion Library** holds every preset: the design's movements (named after their sections, for example "Ekdanta: pBub") and the general entrances. Edit, duplicate, preview, export or import them there. A change to a preset changes every track that uses it.

## Blog articles

Blog → Posts. Write the article in the editor; headings 2 (H2) become the article's contents list. The editor's **Insert block** button adds the design's article blocks: buyer journey strip, compare rows, wide figure, pull quote, reel ideas and checklist. Edit their words in place. Add Tags and Related posts in the post's side panel; they show under the article.

In the Article Hero section, *Green words* lists the words that turn green with the orange swoosh when an article's title contains one. *Articles that show the drawing* lists the articles that use the brochure-and-reel drawing instead of their cover image.

## Forms and the thank-you page

The Brief Builder walks visitors through the Ekdanta Brief form, saves it as an enquiry (Engagement → Enquiries), then opens WhatsApp with their answers. Each form field's *WhatsApp sentence* builds that message. The form then offers the thank-you page.

On the thank-you page, Page settings → **Conversion tags** takes the Meta Pixel ID (it sends "Lead") and the Google Ads conversion ID and label. They fire on the live page only.

## Move pages and blocks between sites

- **Pages**: the export icons on the Pages list download a page as a file. *Export with media* also carries the pictures it uses and the collection items. Import it under *Import a page*: it arrives as a draft. Pictures and collection items already on the site are kept as they are.
- **Saved blocks**: Design → Block Library, the same way.
- **Motion presets**: Design → Motion Library → Export / Import.
- **Theme**: Design → Theme Customizer → Export / Import.

## Quick checks before publishing

- Preview at 1440 × 900 and 390 × 844, then with *Reduce motion* on. Then a tablet held upright (820 × 1180) and a small phone (360 × 640): text a phone shows at its minimum size must wrap, not run off the screen.
- Press Tab through the page: every link and button shows the orange ring and scrolls into view.
- Privacy Policy and Terms of Service are published, and no `[Add …]` placeholder is left in them.
- Replace every `[ … ]` placeholder.
- The home, 404 and thank-you pages have their role, and the three template pages their *Template for*.
