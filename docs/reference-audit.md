# NG Home Cleaners — Reference Site Audit

**Reference URL:** https://fancy-cell-e783.orange-meadow-af80.workers.dev/  
**Audit date:** August 2026  
**Source files:** `docs/reference/*.dc.html` (saved HTML exports)  
**Purpose:** Document the reference site as-is before the Laravel rebuild. Use this to preserve useful content, brand identity, and conversion patterns whilst fixing bugs, inconsistencies, and weak implementation.

---

## 1. Executive summary

The reference site is a five-page marketing site for a Nottingham cleaning business, built on a pen.dev / DC (Design Component) stack with an embedded JavaScript quote estimator on the contact page. It presents a local, trustworthy brand with a quote-first conversion model, strong WhatsApp presence, and residential/commercial service coverage across NG1–NG16.

**Strengths to preserve**

- Clear local positioning (Nottingham, NG postcodes, named districts)
- Quote-first funnel with live guide pricing
- Plain, direct British English copy
- Trust signals (DBS, insurance, Google reviews, 48-hour re-clean guarantee)
- Detailed service inclusions/exclusions
- Photography-led gallery of real jobs
- Mobile sticky CTA bar (home page)

**Critical problems to fix in rebuild**

- Unresolved template expressions visible on live pages (`{{ step.title }}`, `{{ item }}`, etc.)
- Content contradictions (oven inclusion on deep clean vs homepage add-on note)
- Duplicate FAQ entries (payment asked twice)
- Quote form defaults flats to 2 floors (should be 1)
- Exact 30-minute time slots offered (misleading without real scheduling)
- Add-on display prices use `lo` only whilst calculation uses `lo–hi` range
- No individual service landing pages (SEO gap)
- No privacy, terms, or cookie pages
- Form submits to Google Forms via hidden iframe (not a proper lead pipeline)
- Shared nav/footer components not server-rendered (imported via `dc-import`)

---

## 2. Site map and public pages

| Page | File | URL path | Primary purpose |
|------|------|----------|-----------------|
| Home | `index.dc.html` | `/index.dc` | Hero, services overview, social proof, FAQ, conversion |
| Services | `services.dc.html` | `/services.dc` | Inclusions, exclusions, pricing philosophy |
| About | `about.dc.html` | `/about.dc` | Team story, vetting, policies, area list |
| Get a quote / Contact | `contact.dc.html` | `/contact.dc` | 7-step quote wizard + live estimate sidebar |
| Areas | `areas.dc.html` | `/areas.dc` | District-by-district coverage (NG1–NG16) |

**Pages not present on reference**

- Individual service landing pages (`/services/regular-clean`, etc.) — all service links point to `/services.dc`
- Privacy policy, terms of service, cookie policy
- Blog / guides (none on reference)
- Customer portal or login
- `/admin` or CRM (external Google Form only)

---

## 3. Brand and design system

### 3.1 Colours

| Token / usage | Value | Notes |
|---------------|-------|-------|
| Primary teal (brand) | `#0E3B36` | Thumbnail background, ink sections |
| Cream paper | `#F6F1E7` | Thumbnail accent, form inputs (`var(--paper)`) |
| Teal scale | `var(--teal-300)` … `var(--teal-700)` | Links, badges, estimate panel |
| Surfaces | `var(--surface-page)`, `var(--surface-sunken)`, `var(--surface-card)`, `var(--surface-ink)` | Alternating sections |
| Text | `var(--text-primary)`, `var(--text-secondary)`, `var(--text-muted)`, `var(--text-inverse)` | |

Design system bundle: `_ds/ng-home-cleaners-design-system-28bf6795-6216-4e83-84f5-3554eccce596/`

### 3.2 Typography

- Display: `var(--font-display)` — headings, step numbers, price headline
- Body: `var(--font-body)`
- Scale: `--type-h2`, `--type-h3`, `--type-body`, `--type-body-lg`, `--type-caption`
- Eyebrow labels: uppercase, `--tracking-eyebrow`, teal accent

### 3.3 Components (design system)

Imported from `NGHomeCleanersDesignSystem_28bf67`:

- `Button` (variants: default, outline, inverse; sizes: sm, lg; icons left/right)
- `Badge` (tones: brass, accent; icons: star, shield-check, badge-check)
- `TestimonialCard`
- `Card`
- `Icon` (Lucide-style names: house, sparkles, key, building-2, check, x, phone, etc.)

### 3.4 Layout patterns

- Max content width: `1200px`
- Section padding: `clamp(48px–56px, 7–8vw, 80–96px)` vertical; `clamp(20px, 5vw, 48px)` horizontal
- Grids: `repeat(auto-fit, minmax(240–320px, 1fr))`
- Cards: `border-radius: var(--radius-card)`, subtle border and shadow
- Ink (dark teal) bands for final CTAs on every page

---

## 4. Global navigation (`SiteNav`)

The navigation is imported via `<dc-import name="SiteNav">` on every page. **The saved HTML files do not include the nav markup.** From page cross-links, CTAs, and the master build specification, the intended structure is:

### 4.1 Desktop (inferred)

| Element | Destination / action |
|---------|---------------------|
| Logo | Home |
| Home | `/index.dc` |
| Services | `/services.dc` |
| Areas | `/areas.dc` |
| About | `/about.dc` |
| Contact | `/contact.dc` |
| Phone | `tel:07503651476` — **07503 651476** |
| WhatsApp | `https://wa.me/message/SXLIHLSZYTFHD1` or `https://wa.me/447503651476` |
| Primary CTA | “Book my first clean” / “Get a free estimate” → `/contact.dc` |

Nav height hint: `72px`.

### 4.2 Mobile

- Home page includes a **fixed bottom bar** at `max-width: 720px`:
  - “Book my clean” → `/contact.dc` (primary, full width)
  - “WhatsApp” → `https://wa.me/447503651476`
- Spacer `76px` above bar to prevent content overlap
- Safe area inset support: `env(safe-area-inset-bottom)`

### 4.3 Navigation gaps

- No visible active-state documentation in saved files
- Two different WhatsApp URL formats used (`wa.me/message/...` vs `wa.me/447503651476`)
- Services dropdown absent — flat nav only

---

## 5. Global footer (`SiteFooter`)

Imported via `<dc-import name="SiteFooter">` on every page. **Footer markup not present in saved files.** Height hint: `380px`.

Expected content (from site-wide patterns):

- Business name and logo variants
- Contact: **07503 651476**, **hello@nghomecleaners.co.uk**
- Hours: **Every day, 8am–7pm**
- Links to main pages (Home, Services, Areas, About, Contact)
- Coverage: Nottingham NG1–NG16
- **No privacy, terms, or cookies links on reference**

---

## 6. Contact and business details

| Channel | Value |
|---------|-------|
| Phone (display) | 07503 651476 |
| Phone (tel link) | `tel:07503651476` |
| Phone (international schema) | +447503651476 |
| Email | hello@nghomecleaners.co.uk |
| Website (schema) | https://nghomecleaners.co.uk |
| WhatsApp (message link) | https://wa.me/message/SXLIHLSZYTFHD1 |
| WhatsApp (number link) | https://wa.me/447503651476 |
| Opening hours | Mo–Su 08:00–19:0 (schema) / “every day 8am–7pm” (copy) |
| Service area | NG1–NG16, Nottingham and surrounding areas |
| Insurance claim | £1m public liability |

---

## 7. Page-by-page audit

### 7.1 Home (`index.dc.html`)

**SEO**

| Field | Content |
|-------|---------|
| Title | House cleaning in Nottingham · NG Home Cleaners |
| Meta description | Vetted, DBS-checked cleaners across Nottingham… fixed price quoted before we start |
| OG type | website |
| Twitter card | summary_large_image |
| Structured data | `LocalBusiness` JSON-LD (name, phone, email, areaServed, openingHours) |
| Canonical | Not set in saved file |
| H1 | There are better uses for a Saturday morning. |

**Sections (top to bottom)**

1. **Hero**
   - Eyebrow: “Cleaning · Nottingham and surrounding areas”
   - H1 + body copy (vetted, DBS-checked, written standard)
   - CTAs: “Book my first clean” → contact; “See what's included” → services
   - WhatsApp link in reassurance line
   - Trust badges: Five-star Google reviews; £1m public liability; DBS-checked cleaners
   - Hero image: `assets/photo-team.jpg`

2. **What we clean** (4 service cards)
   - Regular clean, Deep clean, End of tenancy, Office & commercial
   - Each links to `services.dc.html` (“What's included”)
   - See [Section 8](#8-service-cards) for card copy

3. **How it works** (4 steps)
   - 01 Tell us about your home
   - 02 We put it in writing
   - 03 Meet your cleaner
   - 04 Pay whichever way suits you

4. **Our recent work** (gallery)
   - 8 images in magazine-style grid (3 columns desktop, 1 column mobile)
   - Link: “Areas we cover” → areas
   - Caption: real jobs, customer permission, no stock

5. **What customers say** (3 testimonials)
   - Faris Ateigo — Deep clean
   - Marada Kochi — End of tenancy
   - Abeer Elzaidabi — Move-out
   - Link to Google search for all reviews

6. **The part you only notice when it goes wrong** (3 trust cards)
   - Cover when yours is away
   - Someone to ring when it goes wrong
   - The bill if something breaks

7. **FAQ** (“The things people ask first”) — 9 `<details>` items — see [Section 12](#12-faqs)

8. **Final CTA** (ink background)
   - “Book a clean you will not have to chase”
   - Phone + WhatsApp

**Mobile-only:** sticky bottom CTA bar (Book / WhatsApp).

---

### 7.2 Services (`services.dc.html`)

**SEO**

| Field | Content |
|-------|---------|
| Title | Cleaning services in Nottingham — regular, deep, end of tenancy & commercial · NG Home Cleaners |
| Meta description | House cleaning Nottingham… NG1–NG16… fixed quote |
| Structured data | `Service` + `OfferCatalog` (4 services listed) |
| H1 | Pay for the clean your home needs. |

**Sections**

1. **Hero** — eyebrow “Services · Nottingham NG1–NG16”, intro copy
2. **What's included** — two-column: left explainer + eco card + `photo-room-tidy.jpg`; right 4 service detail cards
3. **What is not included** — 8 exclusion items with notes
4. **How we price it** — 4 bullet points (fixed price, no hourly meter, travel included, quote within one working day)
5. **Final CTA** — “Send a postcode, get a price you can hold us to”

**Service inclusion lists** (from JavaScript `renderVals`)

| Service | Items |
|---------|-------|
| Regular | Dusting throughout; Hoovering all floors and carpets; Mopping hard floors; Bathrooms and toilets cleaned |
| Deep | Kitchen and oven degreased; Skirting boards cleaned; Inside cabinets and cupboards; Limescale, tiles and shower screens |
| End of tenancy | Inside all wardrobes and drawers; Wall marks, doors and light fittings; Fridge, freezer and appliances emptied and cleaned; Cleaned to inventory standard |
| Commercial | Desks, touch points, glass and reception; Kitchens, washrooms and consumables; Floors, bins and waste; Evenings/weekends around opening hours |

**Not included list**

| Task | Note |
|------|------|
| Inside oven/fridge/freezer on regular | Included on deep and EOT |
| Inside cabinets on regular | Included on deep and EOT |
| External windows | Internal glass paid extra from £20 |
| Heavy mould treatment | Surface mould yes; structural needs specialist |
| Rubbish and furniture removal | Bag to bin included; van loads quoted separately |
| Carpet/upholstery shampooing | Vacuum included; wet extraction separate |
| Lofts, garages, outbuildings | Only if mentioned on quote form |
| Ladder work above head height | Insurance/safety exclusion |

**Live site bug:** inclusion lists render as `{{ item }}` placeholders instead of actual checklist text.

---

### 7.3 About (`about.dc.html`)

**SEO**

| Field | Content |
|-------|---------|
| Title | About us — vetted cleaners in Nottingham · NG Home Cleaners |
| Structured data | `Organization` |
| H1 | A small Nottingham team, not a national franchise. |

**Sections**

1. **Hero** — family-run, NG1–NG16, named districts; `photo-team.jpg`
2. **Local, and staying that way** — vetted cleaners, fixed pricing, upfront extras if worse than described
3. **Vetting** (4 cards) — **BUG: live site shows `{{ step.title }}` and `{{ step.body }}`**
   - Identity and right to work
   - DBS check and references
   - A trial clean with us
   - Insured on every visit (£1m)
4. **What we promise** (4 bullets) — **BUG: live shows `{{ item }}`**
5. **Booking, cancellation and valuables** (3 policy cards)
6. **Coverage** — 12 area labels — **BUG: live shows `{{ area }}`**
7. **Final CTA** — “Check your postcode, get your price”

---

### 7.4 Contact / Quote form (`contact.dc.html`)

**SEO**

| Field | Content |
|-------|---------|
| Title | Get a free cleaning estimate in Nottingham · NG Home Cleaners |
| Structured data | `ContactPage` + `LocalBusiness` |
| H1 | Tell us about your home. |

**Layout**

- Two-column: form (left) + sticky estimate sidebar (right, `top: 96px`)
- Form POSTs to Google Forms (`1FAIpQLSe1-azAyaNfnggKiOdOjkmv7t1u61PFQ0Bk2xD__jF3oJyDsg`) via hidden iframe `nghc-crm`
- Success state replaces form (“That is with us.”)

**Sidebar**

- Live estimate panel (`aria-live="polite"`)
- Contact links: phone, WhatsApp walkthrough, email, hours

See [Section 9](#9-quote-estimator-wizard) for full wizard documentation.

---

### 7.5 Areas (`areas.dc.html`)

**SEO**

| Field | Content |
|-------|---------|
| Title | Areas we cover — cleaners across Nottingham NG1–NG16 · NG Home Cleaners |
| Structured data | `LocalBusiness` with named `areaServed` places |
| H1 | Nottingham only, and we intend to keep it that way. |

**Sections**

1. **Hero** — NG1–NG16 only, staying local for reliability
2. **District by district** — 12 cards (see [Section 11](#11-service-areas))
3. **Just outside the line?** — NG17+ policy; 4 fact bullets
4. **Final CTA** — “Check your postcode”

**Missing postcodes on reference:** NG4, NG10, NG13, NG15 (gaps in NG sequence).

---

## 8. Service cards

Homepage “What we clean” cards (all link to `/services.dc`):

| Service | Icon | Summary copy | Notable detail |
|---------|------|--------------|----------------|
| Regular clean | house | Weekly/fortnightly; dusting, hoovering, mopping, bathrooms | — |
| Deep clean | sparkles | Kitchen and **hob** degreased; skirting, cabinets, limescale | **“Oven interior and internal windows are add-ons.”** |
| End of tenancy | key | Deposit-focused; inventory standard | — |
| Office & commercial | building-2 | Evenings/weekends; priced per visit after walk-round | — |

**Content inconsistency:** Services page and quote estimator describe **oven degreasing as included in deep clean**. Homepage card explicitly lists oven as an **add-on**. See [Section 15](#15-content-inconsistencies).

---

## 9. Quote estimator wizard

Seven steps on a single long form (not a true stepped wizard with validation gates).

### Step 1 — Which clean do you need?

| Field | Type | Options |
|-------|------|---------|
| Service | Radio (required) | Regular clean; Deep clean; End of tenancy; Office or commercial |
| Frequency | Select (Regular only) | Weekly; Fortnightly; Monthly — default Fortnightly |

Helper text: regular cleans have two-hour minimum; usually start with one deep clean.

### Step 2 — What are we cleaning?

| Field | Type | Options / default |
|-------|------|-------------------|
| Property type | Radio | Flat (default checked via `isFlat`); House |
| Bedrooms | Select (required) | Studio (0, flat only); 1–5 bed or more |
| Property status | Select | Furnished (default); Part-furnished; Empty |
| Business name | Text (commercial only) | Optional placeholder “Whitmore & Co” |

**Logic:** Selecting studio (0 beds) forces Flat. Selecting House when beds=0 resets to 1 bed.

### Step 3 — Rooms and floors

| Field | Control | Default | Range |
|-------|---------|---------|-------|
| Bathrooms | Stepper | 1 | 1–5 |
| Separate toilets (WC) | Stepper | 0 | 0–3 |
| Kitchens | Stepper | 1 | 1–3 |
| Living/dining (reception) | Stepper | 1 | 1–4 |
| Floors | Stepper | **2** | 1–4 |

**Extra rooms (checkboxes):** Conservatory; Office; Utility room; Loft room

**Bug:** Default `floors: 2` applies to flats and houses alike. Rebuild spec requires **1 floor default for flats** (and bungalows), with explicit split-level/maisonette option.

**Pricing note:** Extra floor modifier applies to floors **above 2** (`Math.max(floors - 2, 0)`), so default 2 floors means no floor surcharge until 3+.

### Step 4 — Condition of the property

Checkboxes (each adds 7% uplift, max 28%):

- Heavy limescale
- Mould or damp patches
- Pets in the home
- Heavy kitchen grease
- Cluttered surfaces
- Not cleaned in months

No free-text condition field on reference (rebuild adds one).

### Step 5 — Anything extra? (optional)

See `docs/reference-pricing.md` for add-on list and display/calculation conflict.

Excluded services note: no carpet/upholstery, outside windows, gutters, jet washing.

### Step 6 — Where and when

| Field | Type | Notes |
|-------|------|-------|
| Postcode | Text (required) | Pattern: `[Nn][Gg] ?([1-9]|1[0-6]).*` — NG1–NG16 |
| Parking | Select | Free outside (default); Permit; Paid/metered; Unsure |
| Preferred date | Date | Min = today + 3 days |
| Preferred start time | Select | No preference + **exact 30-min slots 8:00am–7:00pm** |
| Access | Select | I will be home (default); Someone will let you in; Agent/landlord; To be arranged |

**Bug:** Exact time slots (e.g. 10:30am) imply bookable availability. Rebuild spec requires **Morning (from 9am) / Afternoon (from 1pm) / Flexible** only.

### Step 7 — Where do we send the price?

| Field | Type | Required |
|-------|------|----------|
| Your name | Text | Yes |
| Phone | Tel | Yes |
| Email | Email | Yes |
| Anything else | Textarea | No |

Submit: “Send my quote request” → Google Form hidden fields.

**Privacy note on form:** “We use your details to quote this job and nothing else. No marketing list, no sharing.”

### Estimate sidebar copy

**Included in every price:** DBS-checked cleaner · £1m insurance · products and equipment · free re-clean if we miss anything. Pay in full by card before the clean, or half to secure the slot and the balance on the day.

**Estimate states**

| State | Headline |
|-------|----------|
| No service | “Pick a service to start” |
| Commercial | “Priced per visit” |
| No bedrooms | “Add the bedrooms” |
| Regular | “From £X a visit” (single price with frequency discount) |
| Deep / EOT | “£X–£Y” range |

Range narrows as customer provides more signals (condition, postcode, date, parking/access, notes).

Full pricing logic: `docs/reference-pricing.md`.

---

## 10. CTAs (calls to action)

### Primary CTA copy variants

| Copy | Typical destination |
|------|---------------------|
| Book my first clean | `/contact.dc` |
| Book my clean | `/contact.dc` (mobile bar) |
| See what's included | `/services.dc` |
| Send my quote request | Form submit |
| Read them all on Google | Google search URL |
| Areas we cover | `/areas.dc` |
| Call 07503 651476 | `tel:` |
| WhatsApp us / Send your walkthrough | WhatsApp links |

### CTA placement

- Hero on every major page
- Ink-band section at page bottom (all pages)
- Mobile sticky bar (home only)
- FAQ and inline text links to contact form
- Estimate sidebar contact links

---

## 11. Service areas

### 11.1 Areas page — 12 district cards

| Code | Name | Summary note |
|------|------|--------------|
| NG1 | City centre | Apartments, city-centre lets, parking arranged |
| NG2 | West Bridgford | Family homes, busy for fortnightly |
| NG3 | Mapperley and St Ann's | Victorian terraces, deep clean before regular |
| NG5 | Sherwood and Arnold | Larger houses, weekday mornings |
| NG6 | Bulwell | Landlord turnarounds |
| NG7 | Lenton and Radford | Student lets, EOT to inventory standard |
| NG8 | Wollaton and Bilborough | Detached/semi, pre-hosting deep cleans |
| NG9 | Beeston and Stapleford | Weekly/fortnight upkeep |
| NG11 | Clifton and Ruddington | Suburban/village, travel included |
| NG12 | Radcliffe and Keyworth | Village properties |
| NG14 | Burton Joyce and Lowdham | Edge villages, larger detached |
| NG16 | Eastwood and Kimberley | North-west, standing weekly slots |

**Not listed:** NG4, NG10, NG13, NG15 (but postcode validator accepts NG1–NG16).

### 11.2 About page — compact area list

NG1 City centre · NG2 West Bridgford · NG3 Mapperley · NG5 Sherwood/Arnold · NG6 Bulwell · NG7 Lenton/Radford · NG8 Wollaton/Bilborough · NG9 Beeston/Stapleford · NG11 Clifton/Ruddington · NG12 Radcliffe/Keyworth · NG14 Burton Joyce · NG16 Eastwood/Kimberley

### 11.3 Coverage facts (areas page)

- Travel inside NG1–NG16 included
- Weekday and Saturday slots
- Same standard everywhere
- Send postcode to confirm district

---

## 12. FAQs

### Homepage FAQ (9 items)

| Question | Topic |
|----------|-------|
| How does it work out on cost? | Estimate form → range → fixed price in writing |
| How and when do I pay? | Full card upfront OR half deposit + balance on day |
| Why are you more than the cleaner down the road? | Cover, insurance, DBS, accountability |
| Do I need to be home during the clean? | No; meet cleaner first; DBS-checked |
| When could you start? | Quote form date; work to fixed dates/Saturdays |
| What if there is more work than expected? | Stop and agree before continuing |
| **How do I pay?** | **DUPLICATE — 50% deposit, balance on day; regular customers skip deposit** |
| Do you bring products and equipment? | Yes; eco on request free |
| What if the clean is not up to standard? | 48-hour re-clean at their cost |

**Bug:** “How and when do I pay?” and “How do I pay?” overlap substantially. Consolidate in rebuild.

---

## 13. Testimonials

| Name | Service tag | Quote (abridged) |
|------|-------------|------------------|
| Faris Ateigo | Deep clean · Verified Google review | Very good service… deep clean… didn't miss a single spot… professional |
| Marada Kochi | End of tenancy · Verified Google review | End-of-tenancy… exceeded expectations |
| Abeer Elzaidabi | Move-out · Verified Google review | Student accommodation… super friendly, quick, clean |

Component: `TestimonialCard` with quote, name, meta line.

**SEO/trust note:** Reviews presented as “Verified Google review” but no schema.org `Review` markup on page. Rebuild should only show verified reviews from CRM settings.

---

## 14. Images and assets

### 14.1 Logo and brand

| Asset | Usage |
|-------|-------|
| `assets/logo-lockup-ink.png` | Nav (light background) |
| `assets/logo-lockup-white.png` | Nav/footer on dark |
| `assets/logo-monogram-ink.png` | Favicon alternative |
| `assets/logo-square-ink.png` | Mapped in resource fixer |
| `assets/favicon-ng.png` | Favicon + apple-touch-icon |

### 14.2 Photography

| Asset | Used on | Alt text summary |
|-------|---------|------------------|
| `photo-team.jpg` | Home, About hero | Four team members in navy polos cleaning hallway |
| `photo-room-tidy.jpg` | Services | Sitting room after clean |
| `photo-living-room-clean.jpg` | Gallery | Apartment living room |
| `photo-hallway.jpg` | Gallery | Hallway, mopped floor |
| `work-01.jpg` … `work-10-sm.jpg` | Home gallery | Various rooms post-clean |

Gallery uses resource ID mapping (`window.__resources`) for CDN/blob URLs in production.

### 14.3 Gallery layout (home)

- **Desktop:** 3-column grid, alternating 2+1 spans (magazine layout)
- **Mobile:** single column, taller rows (`clamp(200px, 56vw, 340px)`)
- All images: `loading="lazy"`, `object-fit: cover`, `border-radius: var(--radius-media)`

---

## 15. Content inconsistencies

| Topic | Location A | Location B | Issue |
|-------|------------|------------|-------|
| Oven on deep clean | Home card: “Oven interior… are **add-ons**” | Services `deepIncluded`: “Kitchen and **oven** degreased”; Contact estimate detail same | Homepage contradicts services + estimator |
| Hob vs oven | Home: “Kitchen and **hob** degreased” | Services: “Kitchen and **oven** degreased” | Wording mismatch |
| Payment FAQ | “How and when do I pay?” | “How do I pay?” | Duplicate/overlapping FAQ |
| WhatsApp URL | `wa.me/message/SXLIHLSZYTFHD1` | `wa.me/447503651476` | Two link formats sitewide |
| Deep clean live vs saved | Live homepage fetch: oven included, no add-on note | Saved `index.dc.html`: hob + add-ons note | Possible reference drift |
| Not included vs deep | Says oven included on deep/EOT | Home says oven add-on even for deep | Customer confusion |
| Part-furnished | Form option exists | Pricing logic only applies Empty (−8%) or Furnished (+5%) for non-regular | Part-furnished has **no price modifier** |

---

## 16. Bugs and UI/UX problems

### 16.1 Critical bugs

| Bug | Evidence | Rebuild action |
|-----|----------|----------------|
| Unresolved template expressions | Live about: `{{ step.title }}`, `{{ step.body }}`, `{{ area }}`, `{{ item }}`; live services/contact similar | Server-render all content; no client template leaks |
| Default floors = 2 for flats | `contact.dc.html` state `floors: 2` | Default 1; maisonette option for 2+ |
| Exact time slots | `slots()` generates 8:00am–7:00pm every 30 min | Morning/Afternoon/Flexible only |
| Extra price display vs calculation | UI shows `£` + `lo`; calc uses `lo–hi` | Single source of truth |
| Google Form submission | Hidden iframe, no server lead record | Laravel lead pipeline |
| Template `{{ 20 }}` in icon sizes | Multiple pages | Render numeric attributes server-side |

### 16.2 UX issues

| Issue | Detail |
|-------|--------|
| Single-page form, not wizard | All 7 steps visible; no step validation gating |
| No “Review” step | User submits without summary confirmation page |
| Commercial flow | Bedroom/room fields still shown; estimate says “walk-round” but form collects residential-style data |
| Monthly frequency | Offered in UI; pricing only discounts Weekly (5%) and Fortnightly (2.5%) |
| Preferred date min +3 days | Hard-coded; no explanation of lead time in UI beyond caption |
| Sticky sidebar | Estimate panel may be below fold on mobile; no collapsible mobile summary |
| No back-navigation | Form is one scroll; no step persistence UX |
| Part-furnished | No pricing differentiation explained |
| Condition checkboxes | No way to describe unlisted conditions (rebuild adds free text) |
| Service switching | Step 1 helper text does not change per service (rebuild requirement) |

### 16.3 Accessibility notes

**Good:** FAQ uses native `<details>`; steppers have `aria-label`; estimate has `aria-live="polite"`; form fields have labels; focus styles on inputs.

**Gaps:** No skip link; mobile sticky bar may obscure content; checkbox/radio custom styling relies on native inputs (acceptable); no documented reduced-motion handling.

---

## 17. SEO issues

| Issue | Severity | Notes |
|-------|----------|-------|
| No individual service URLs | High | All services on one page; missed long-tail keywords |
| No legal pages | Medium | Privacy/terms expected for forms and cookies |
| No canonical URLs | Medium | Not in saved `<helmet>` |
| No sitemap/robots | Medium | Not present on reference |
| JS-dependent content | High | Lists/testimonials/areas render via DC logic; live site shows raw `{{ }}` to users and potentially crawlers |
| No BreadcrumbList | Low | — |
| OG image not set | Medium | Twitter card set but no `og:image` in saved files |
| Duplicate payment FAQ | Low | Thin content repetition |
| Schema URL vs deploy URL | Low | Schema says nghomecleaners.co.uk; reference on workers.dev |
| Missing NG postcodes in content | Low | NG4, NG10, NG13, NG15 not named despite coverage claim |
| H1 uniqueness | OK | Each page has one H1 |
| Meta descriptions | OK | Unique per page, reasonable length |

---

## 18. Responsive behaviour

| Breakpoint / behaviour | Implementation |
|------------------------|----------------|
| Mobile detection | `matchMedia('(max-width: 720px)')` on home only |
| Typography | `clamp()` on headings and section padding |
| Grids | `auto-fit` + `minmax()` throughout |
| Gallery | 3-col → 1-col; row height adjusts |
| Hero | Single column stack on narrow viewports |
| Contact form | `minmax(320px, 1fr)` → single column; sticky sidebar loses stickiness when stacked |
| Mobile CTA bar | Home only, fixed bottom, z-index 60 |
| Touch targets | Buttons min 44px height; pill labels min-height 44px |

---

## 19. Trust elements (sitewide)

| Claim | Where shown |
|-------|-------------|
| DBS-checked cleaners | Hero badges, FAQ, about vetting, estimate sidebar |
| £1m public liability | Badges, about, trust section, estimate sidebar |
| Five-star Google reviews | Hero badge (not a numeric rating) |
| 48-hour re-clean guarantee | Hero, FAQ, final CTA, promises |
| Fixed price in writing | How it works, services, about, form intro |
| Vetted replacement cover | “When it goes wrong” section |
| Insurance on breakages | Trust section, about valuables policy |
| No contract/subscription | How it works, FAQ |
| Products included | Services card, FAQ |
| Real photography | Gallery disclaimer |

**Rebuild requirement:** Make all trust claims configurable and only visible when verified/enabled.

---

## 20. Technical implementation notes

- **Platform:** pen.dev / DC components (`<x-dc>`, `<dc-import>`, `<x-import>`, `<sc-for>`, `<sc-if>`)
- **Design system ID:** `28bf6795-6216-4e83-84f5-3554eccce596`
- **CRM integration:** Google Forms POST with mapped entry IDs
- **Pricing:** Client-side JavaScript only (`contact.dc.html` script block)
- **No backend:** No database, auth, or admin on reference site

---

## 21. Rebuild mapping (reference → production)

| Reference | Production route (per build spec) |
|-----------|-----------------------------------|
| index.dc | `/` |
| services.dc | `/services` + `/services/{slug}` |
| areas.dc | `/areas` + `/areas/{slug}` |
| about.dc | `/about` |
| contact.dc | `/get-a-quote` (and `/contact` for general contact) |
| — | `/privacy`, `/terms`, `/cookies` (new) |

---

## 22. Audit checklist for implementers

- [ ] Fix all template expression leaks
- [ ] Resolve oven/hob deep-clean inconsistency in CMS content
- [ ] Merge duplicate payment FAQs
- [ ] Default flat floors to 1
- [ ] Replace exact time slots with arrival windows
- [ ] Unify WhatsApp link format
- [ ] Single pricing source for add-ons (display = calculation)
- [ ] Server-render all checklist/testimonial/area content
- [ ] Add service and area landing pages
- [ ] Add legal pages
- [ ] Implement proper lead capture (not Google Forms iframe)
- [ ] Add canonical, OG image, sitemap, breadcrumbs
- [ ] Handle Part-furnished in pricing or remove option
- [ ] Add Monthly frequency pricing rule or remove option

---

## 23. Known issues (rebuild must fix)

This section consolidates every confirmed defect from live inspection (August 2026) and saved HTML. These are **not** to be reproduced in the Laravel rebuild.

### 23.1 Quote estimator — functional bugs

| Issue | Evidence | Rebuild requirement |
|-------|----------|---------------------|
| **Service description not changing in estimator** | Step 1 shows static copy (“Regular cleans have a two-hour minimum…”) regardless of selected service. Sidebar `estDetail` updates per service, but Step 1 helper text does not. | Per-service description from CRM `Service` record updates immediately on clean-type change. |
| **No one-off option for regular cleaning** | Frequency select offers Weekly, Fortnightly, Monthly only. No “One-off” for customers wanting a single regular-style visit. | Add One-off alongside Weekly, Fortnightly, Monthly where Regular Cleaning is selected. |
| **Flat floor handling wrong** | `state.floors` defaults to **2** for all property types. Label says “Including any loft conversion” with no split-level option. | Default **1 floor** for flats and bungalows. Explicit “Split-level flat / maisonette” option to allow 2+ internal levels. |
| **Missing condition free text** | Step 4 is checkboxes only. Free-text “Anything else we should know?” lives in Step 7, not tied to condition. | Add condition free-text field in the Condition step (plus retain general notes in Review/Details). |
| **Add-on price mismatches** | Checkbox labels show `£{lo}` only; calculation uses full `lo–hi` range (and per-bath multiplier). See `docs/reference-pricing.md` §8.4. | Single CRM price powers label, calculation, review, emails, and stored breakdown. |
| **Unclear availability / date behaviour** | `minDate()` = today + 3 days with no UI explanation. Caption: “Our next available slots” implies live booking. **Exact 30-minute slots** 8:00am–7:00pm generated by `slots()`. | Preferred **date** + **arrival window** (Morning from 9am / Afternoon from 1pm / Flexible). Explain that availability is confirmed by NG. |
| **Weak validation** | HTML5 `required`/`pattern` only on some fields. No UK phone format check. No step gating. Single scrollable form, not a wizard. Browser validation only. | Client-side reactive validation **and** server-side Form Request before persistence. Realistic UK phone and postcode rules. |
| **Weak estimate summary** | Sidebar shows headline + short detail + basis text only. No structured breakdown of rooms, condition, extras, or preferred visit. No dedicated Review step. | Sticky summary listing every selection; dedicated Review step before submit. |

### 23.2 Content and copy issues

| Issue | Evidence | Rebuild requirement |
|-------|----------|---------------------|
| **Services page spacing / layout** | Live `/services.dc` renders `{{ item }}` placeholders in inclusion lists; broken two-column layout when lists fail to render. | Server-render all inclusion/exclusion lists from CMS. Consistent section spacing via design system. |
| **Dummy Guides content** | Not present on current reference export, but pen.dev prototypes often include placeholder “Guides” cards. Master Directive explicitly removes this. | **Do not introduce** a Guides/blog section at launch. No dummy advice cards. |
| **AI-looking em-dash-heavy copy** | Reference copy uses em dashes (—) frequently and long compound sentences, e.g. “Your home cleaned by a vetted, DBS-checked cleaner working to a written standard — so there is nothing left…”. | Rewrite in natural British English. **No em dashes** in public marketing copy. Shorter sentences. |
| **Oven / hob inconsistency** | Homepage deep-clean card: oven is add-on; services + estimator: oven included. | Align in CMS; one source of truth per service. |
| **Duplicate payment FAQ** | “How and when do I pay?” and “How do I pay?” overlap. | Consolidate to one FAQ entry. |

### 23.3 Template and rendering bugs (live site)

Unresolved `{{ }}` expressions visible to users on:

- **Services:** inclusion list items, not-included notes, pricing bullets
- **About:** vetting steps (`{{ step.title }}`, `{{ step.body }}`), promises (`{{ item }}`), coverage areas (`{{ area }}`)
- **Areas:** district cards (`{{ area.code }}`, `{{ area.name }}`, `{{ area.note }}`), fact bullets
- **Contact:** stepper values (`{{ baths }}`, etc.), add-on labels (`{{ x.label }}`), estimate panel (`{{ estHeadline }}`), time slots (`{{ slot }}`)

All content must be server-rendered in Blade/Livewire. No client template leaks.

### 23.4 Live inspection log (August 2026)

Pages fetched from `https://fancy-cell-e783.orange-meadow-af80.workers.dev/`:

| Path | Status | Notes |
|------|--------|-------|
| `/index.dc` | OK (content renders) | Mobile sticky CTA bar present. No Guides section on live home. |
| `/services.dc` | Broken lists | Template placeholders in inclusion/exclusion sections |
| `/about.dc` | Broken dynamic sections | Vetting, promises, coverage show raw `{{ }}` |
| `/areas.dc` | Broken district grid | All 12 area cards show placeholders |
| `/contact.dc` | Partial | Form structure visible; steppers and estimate panel show placeholders in fetch; pricing JS present in saved HTML |

**Internal links discovered:** Home ↔ Services ↔ About ↔ Contact ↔ Areas (flat nav, no service sub-pages).

---

*For site structure, UX bugs, and content inconsistencies, see sections 1–22 above. Pricing detail is expanded in `docs/reference-pricing.md`.*
