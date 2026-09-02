# NG Home Cleaners — Architecture

## Purpose

Production Laravel application for a UK cleaning business: customer-facing website plus lightweight Filament CRM.

**Phase 1 (complete):** Infrastructure foundation.  
**Phase 2 (complete):** Shared public design system and base layout.  
**Phase 3 (complete):** Core CMS data model and Filament admin CRUD.  
**Phase 4 (complete):** Public website pages wired to CMS data.  
**Phase 5 (complete):** Server-side pricing engine (single authority for estimates).  
**Phase 6 (complete):** Livewire single-page estimate form at `/get-a-quote` (live guide price, full CRM lead capture).  
**Phase 7 (complete):** Quote request persistence, lead generation, confirmation page, CRM Leads resource, queued mail/notifications.  
**Phase 8 (complete):** Production Filament Leads & Customers CRM (search, filters, status actions, final quotes, manual phone leads, safe customer matching).  
**Phase 9 (complete):** Bookings CRM, lead→booking conversion, clash warnings, admin calendar, manual payments, revenue from received money only.  
**Phase 10 (complete):** Filament business owner dashboard — live CRM stats, recent lists, period comparisons, demo seed data.  
**Phase 11 (complete):** Production SEO — metadata, sitemap, robots, JSON-LD, breadcrumbs, custom 404, canonical redirects, content cleanup.  
**Phase 12 (complete):** Engineering hardening — performance, uploads, accessibility, security, error pages, analytics hooks.

## Stack (locked)

| Layer | Choice |
|-------|--------|
| Framework | Laravel 13, PHP 8.3+ |
| Database | PostgreSQL |
| Public UI | Blade + Tailwind CSS 4 + Vite |
| Interactivity | Livewire 4 (quote estimate form) |
| Admin CRM | Filament 5 at `/admin` |
| Tests | Pest 4 |
| Queues | Database driver (`jobs` table) |
| Storage | Local disk + public disk via `storage:link` |

## CMS data model (Phase 3)

```
services                    Core service content + SEO + media
service_inclusions          Checklist items per service
service_exclusions          Exclusion task + note per service
service_faqs                FAQ per service
addons                      Single price_pence source of truth
addon_service               Optional add-ons per service
pricing_bands               (removed — replaced by starting prices + bedroom rules)
pricing_room_modifiers      (removed — replaced by per-service extra rooms)
pricing_starting_prices     Service × property type starting guide range
pricing_bedroom_rules       Per-service bedrooms included + extra bedroom range
pricing_extra_rooms         Service × room type surcharges
pricing_conditions          Service × condition fixed ranges
pricing_settings            Frequency discounts, floors baseline, rounding, furnishing %
service_areas               Area pages with SEO fields
service_area_faqs           FAQ per area
service_area_service        Services available in each area
gallery_items               Single-image gallery (admin; homepage uses Recent Work)
recent_works                Before/after homepage showcase
testimonials                Reviews with publishing, ordering, is_demo flag
site_settings               Singleton business/contact/trust/SEO + homepage/about content
legal_pages                 Privacy, terms, cookies (CMS-managed)
faqs                        Global homepage FAQs
```

### Public visibility scopes

- `Service::active()`, `Addon::active()`, `ServiceArea::active()`
- `GalleryItem::published()`, `Testimonial::published()` / `publishedForProduction()` (excludes demo in production)
- `LegalPage::published()`, `Faq::published()`

### Settings cache

`App\Services\SiteSettingsService` caches settings **attributes** (not Eloquent instances) under `site.settings.attributes`, and memoises the hydrated model for the request. `SiteSettingObserver` clears cache on save. Legacy `site.settings.record` / `site.settings.id` keys are forgotten on upgrade.

View composer shares `$settings` to all public layouts, components, and pages.

## Filament admin navigation

| Group | Resources / pages |
|-------|-------------------|
| Website | Services (inclusions, exclusions, FAQs, add-ons), Add-ons, Service Areas, Gallery, Reviews, FAQs, Legal pages |
| Pricing | Pricing (starting prices, bedrooms, rooms, conditions, frequency, advanced) |
| CRM | Dashboard, Leads (quote requests), Bookings, Calendar, Payments, Customers |
| Settings | Site settings |

All admin policies extend `AdminPolicy` (authenticated staff access).

## Public routes (Phase 4)

| Route | Controller / view | Data source |
|-------|-------------------|-------------|
| `/` | `HomeController` | Services, gallery, areas, testimonials, FAQs, site settings |
| `/services` | `ServiceController@index` | Active services |
| `/services/{slug}` | `ServiceController@show` | Service + inclusions, exclusions, add-ons, FAQs |
| `/areas` | `AreaController@index` | Active service areas + coverage checker |
| `/areas/{slug}` | `AreaController@show` | Area content, linked services, FAQs |
| `/areas/{area}/{service}` | `AreaController@service` | Service × area landing pages |
| `/reviews` | `ReviewsController` | Published, non-demo testimonials |
| `/move-in-move-out` | `MoveInOutController` | Move-in / move-out landing using the pricing engine |
| `/about` | `AboutController` | Site settings (story, promises) |
| `/contact` | `ContactController` | Site settings (phone, email, WhatsApp, hours) |
| `/get-a-quote` | `EstimateWizard` (Livewire) | Multi-step estimator; submits to lead pipeline |
| `/get-a-quote/confirmation/{reference}` | `QuoteConfirmationController` | Post-submit confirmation (reference, estimate, next steps) |
| `/privacy`, `/terms`, `/cookies` | `LegalPageController` | `legal_pages` table |

Inactive services/areas return 404 via route model binding scopes.

## Reusable public components

Located in `resources/views/components/public/`: layout primitives, `service-card`, `trust-strip`, `gallery-grid`, `testimonial-card`, `faq-list`, `final-cta`, `page-hero`, SEO partial.

Empty states render when optional CMS sections have no published data (gallery, testimonials, etc.).

## Pricing engine (Phase 5)

All estimate calculations go through `App\Pricing\PricingEngine`. No frontend price constants.

### Domain layout

```
app/Pricing/
  Money.php                 Integer pence math (bcmath for percentages)
  PriceRange.php            Non-negative min/max ranges
  PriceAdjustment.php       Signed line-item deltas
  PricingConfiguration.php  Cached starting prices, bedroom rules, rooms, conditions, settings
  PricingEngine.php         Simplified calculation pipeline
  AddonPriceFormatter.php   Display labels from same Add-on DB values as engine
  Data/
    EstimateInput.php       Request DTO (service, rooms, flags, add-ons)
    CalculationLineItem.php Structured line item (fixed or percentage)
    CalculationResult.php   Full breakdown + snapshot JSON for quotes
```

### Calculation pipeline (simplified)

1. Starting price (`pricing_starting_prices`: service × property type)
2. Extra bedrooms (`pricing_bedroom_rules`: included count + per-extra range)
3. Extra rooms (`pricing_extra_rooms`: service × room type)
4. Conditions (`pricing_conditions`: service × condition, fixed £)
5. Add-ons (existing `addons` table — single source of truth)
6. Furnishing adjustment (deep/EOT only: empty / furnished percentages)
7. Frequency discount (regular clean only)
8. Round to nearest £5

Commercial (`office-commercial`) returns a manual-quote result with no numeric price.

See `docs/pricing-migration-review.md` for the mapping from old bedroom bands.

### Admin pricing (Filament → Pricing)

One page: **Pricing** (`/admin/pricing`) with tabs — Starting prices, Bedrooms, Extra rooms, Conditions, Frequency, Advanced.

Add-ons stay under **Website → Add-ons** (not duplicated).

Changes take effect immediately after save (pricing cache cleared).

### Add-on price consistency

`Addon::formattedPrice()` and `AddonPriceFormatter` use `PricingEngine::calculateAddonContribution()`. The same `price_pence` / `price_max_pence` values feed admin display, public service pages, and estimate calculations.

### Quote snapshots

`CalculationResult::snapshot` is stored on each `quote_requests` row at submission time (immutable pricing breakdown).

## Estimate form (Phase 6)

Route: `/get-a-quote` → `App\Livewire\EstimateWizard` (full-page Livewire component).

### Layout

Single-page numbered sections (Joya-style), wide grid so property/rooms/details use horizontal space. Sticky live guide estimate on desktop; compact bar on mobile (Livewire island). Commercial hides rooms, condition, and extras via Alpine.

### Sections (residential)

Service → Property → Rooms → Condition → Extras (optional accordion) → When & details → Submit (email or WhatsApp)

### Key behaviours

- Live guide price via `PricingEngine` as answers change (island updates for most fields)
- Service / property-type changes remorph the form so extras and floor defaults stay correct
- Flat defaults to 1 floor; split-level / maisonette unlocks multi-floor
- Regular clean frequencies: One-off, Weekly, Fortnightly, Monthly
- Arrival windows (not exact slots); UK phone, email, NG postcode validation
- Full CRM lead capture on submit; honeypot + IP rate limit; confirmation page unchanged

## Lead generation (Phase 7)

### Data model

```
customers                   Contact records (upserted by email, then phone)
quote_reference_counters    Sequential counter for public references (starts NG-1001)
quote_requests              Immutable lead + selections + pricing snapshot
notifications               Filament database notifications for staff
```

Public references use `NG-{n}` from `QuoteReferenceGenerator` (not database IDs).

### Sources and statuses

| Source | Values |
|--------|--------|
| Source | `web`, `whatsapp`, `phone`, `manual` |
| Status | `new`, `contacted`, `quote_sent`, `won`, `lost` |

### Submission flow

1. Final validation of all estimate sections
2. Server-side recalculation via `PricingEngine`
3. Customer upsert (`QuoteRequestService`)
4. `quote_requests` row with `selections_snapshot` + `pricing_snapshot`
5. Filament database notification to all admin users
6. Queued internal email (`InternalQuoteRequestMail`) to `lead_notification_emails` (fallback: site email)
7. Queued customer acknowledgement (`CustomerQuoteAcknowledgementMail`)
8. Redirect to `/get-a-quote/confirmation/{reference}`

Mail is sent **after** the database transaction commits — mail failure does not roll back the saved lead. New-lead customer and internal emails are sent immediately via Resend.

### WhatsApp flow

Review step → **Continue on WhatsApp**:

1. Same validation and server-side recalculation
2. Save lead first (`source=whatsapp`, `whatsapp_initiated_at` set)
3. CRM notification + queued emails
4. Open pre-filled WhatsApp URL (reference included in message)
5. UI states request was saved and WhatsApp was opened (does not claim message was sent)

Duplicate-click prevention: `$submitting` flag + `$savedReference` on the Livewire component.

### Filament admin (Phase 7–9)

| Group | Resource |
|-------|----------|
| CRM | Leads (`/admin/quote-requests`), Bookings (`/admin/bookings`), Calendar (`/admin/booking-calendar`), Payments (`/admin/payments`), Customers (`/admin/customers`) |

## CRM Leads & Customers (Phase 8)

### Lead table

Columns: reference, created date, customer, source, service, postcode, preferred date, arrival, guide estimate, final quoted amount, status.

Filters: status, source, service, preferred date range, submitted date range.

Search: reference, customer name, email, phone, postcode.

### Lead detail

- Full submitted estimate breakdown from `pricing_snapshot`
- Internal notes + final quote amount (pence)
- Status timestamps: `contacted_at`, `quote_sent_at`, `won_at`, `lost_at`
- Header actions: Mark contacted / quote sent / won / lost; **Convert to Booking** when Won

### Manual leads

`QuoteRequestService::createManual()` with `source=phone|manual`. Staff form at **Add phone / manual lead**.

### Customer matching

`CustomerMatcher` matches by exact email, or exact phone only when emails are compatible (same or one missing). Conflicting phone+email pairs are **not** merged — a new customer is created.

### Customers

Store name, phone (normalised + display), email (nullable unique), address, postcode, notes. View shows related leads and bookings.

## Bookings, payments & revenue (Phase 9)

### Booking

Fields: customer, source lead, service, address, postcode, booking date, arrival window, optional expected duration, agreed price (pence), status (`scheduled` / `completed` / `cancelled`), internal notes. Public refs `BK-1001+`.

### Lead → booking

`BookingConversionService` prefills customer, service, address, postcode, preferred date/arrival, final quote (fallback to guide), and notes. Won leads show **Convert to Booking** (create form with `?lead=`).

### Clash warning

`BookingClashDetector` finds other non-cancelled bookings on the same day with conflicting arrival windows (same window, or either Flexible). Form shows a warning; save is not blocked.

### Calendar

`/admin/booking-calendar` — month grid with customer, service, arrival, status; clickable into booking; month revenue (received only).

### Payments

Manual records: booking, signed `amount_pence`, type (Deposit / Balance / Full / Adjustment / Refund), method, paid date, reference, notes. Refunds stored negative. Booking shows Agreed / Paid / Outstanding; overpayment warns but does not hard-block.

### Revenue

`RevenueCalculator` sums payment `amount_pence` only. Quotes, guide estimates, and unpaid booking totals are never revenue.

## Business dashboard (Phase 10)

`App\Filament\Pages\Dashboard` replaces the default Filament dashboard.

### Stats (`CrmStatsOverview`)

All figures from `DashboardMetrics` (live DB queries, no cache):

| Card | Source |
|------|--------|
| New leads | `quote_requests.status = new` |
| Leads this month | submitted this month (+ vs last month) |
| Awaiting response | `status = quote_sent` |
| Upcoming bookings | scheduled, `booking_date >= today` |
| Completed jobs | completed this month (+ vs last month) |
| Revenue this month / all time | sum of `payments.amount_pence` (received only) |
| Outstanding balance | agreed − paid on non-cancelled bookings |
| Lead conversion | won ÷ (won + lost) this month |
| Most requested service | top `service_id` on leads |

Cards link to filtered Leads / Bookings / Payments indexes where useful.

### Lists

- Recent leads, Upcoming bookings (eager-loaded table widgets)
- Recent activity (merged leads, bookings, payments)

### Local demo data

`CrmDemoSeeder` (non-production, skipped if leads/bookings already exist) seeds customers, leads across statuses, bookings, and payments for manual dashboard checks. Called from `DatabaseSeeder`.

## SEO (Phase 11)

### Metadata

`SeoService` builds a `SeoPage` per route: unique title, description, canonical, Open Graph, Twitter card, robots. Service/area/legal SEO fields come from CRM; site settings are fallback only.

### Discovery

- `/sitemap.xml` — homepage, services index + active services, areas index + active areas, about, contact, published privacy/terms/cookies
- `/robots.txt` — allows public site; disallows `/admin`, Livewire internals, quote confirmation; points at sitemap
- Quote confirmation uses `noindex,nofollow`

### Structured data

Server-rendered JSON-LD:

- `LocalBusiness` / `HomeAndConstructionBusiness` from settings only (no invented ratings, hours, or address)
- `BreadcrumbList` when the page has a breadcrumb trail

### HTML / UX

Breadcrumbs, one H1, semantic landmarks, descriptive service-card labels, custom branded 404, trailing-slash 301 via `ForceCanonicalUrls`.

### Local SEO

Service pages link to related areas; area pages link to services and nearby districts. Area copy is per-district from CMS (not doorway templates).

## Engineering hardening (Phase 12)

### Performance

- Site settings cached as a full model (no per-request `findOrFail` after cache hit)
- `PricingEngine` reuses loaded `PricingConfiguration` for the request singleton
- Estimate form: island-scoped live pricing fields; service/property remorph for extras and defaults
- Fonts loaded asynchronously with `display=swap`; Vite hashed assets
- Gallery images use width/height + lazy loading; primary above-fold heroes are not lazy-loaded when wired via `x-public.img` (`priority`)

### Uploads

`SecureImageUpload` for CRM media: MIME allow-list, 5 MB max, resize/contain downscale, UUID filenames on the public disk.

### Accessibility

Skip link, focus styles, reduced-motion CSS, estimate section labels, field `aria-invalid` / error summary, estimate `aria-live`, honeypot excluded from AT.

### Security & errors

- Quote honeypot + IP rate limit (5 / minute)
- Mail/notification failures logged without failing the lead save path
- Session `secure` cookie defaults on in production
- Branded `404` / `419` / `500` (minimal layout for 419/500 so DB outages still render)
- Analytics events are provider-agnostic (`window.ngTrack`); no vendor unless `ANALYTICS_ENABLED` + driver

### Analytics events

`quote_started`, `quote_step_completed`, `quote_completed`, `whatsapp_quote`, `whatsapp_clicked`, `phone_clicked`, `service_viewed`

## Next phases

- Filament CRUD for global FAQs and legal pages (optional; seeded for now)
- Optional self-hosted fonts / responsive `srcset` when hero photography ships on public pages

See `.cursor/rules/master-directive.mdc`.
