# Testing

## Automated commands

```bash
# Full suite (SQLite in-memory — PostgreSQL not required)
php artisan test

# Focused suites
php artisan test --filter=AuthAndPublicAccess
php artisan test --filter=ServicesVisibility
php artisan test --filter=PricingEngine
php artisan test --filter=EstimateWizard
php artisan test --filter=QuoteRequest
php artisan test --filter=CustomerMatcher
php artisan test --filter=BookingsPayments
php artisan test --filter=Dashboard
php artisan test --filter=Seo
php artisan test --filter=Hardening

# Format + build (handoff)
vendor/bin/pint --dirty
npm run build
```

Tests use SQLite in-memory (`phpunit.xml`). Seeders (`CmsSeeder`, `CrmDemoSeeder`) run inside feature tests with `RefreshDatabase`.

## Coverage map

| Area | Primary tests | Asserts |
|------|---------------|---------|
| **AUTH** | `AuthAndPublicAccessTest`, `AdminAccessTest` | Guests redirected from CRM routes; public pages stay public; bad password stays guest; seeded admin reaches `/admin` |
| **SERVICES** | `ServicesVisibilityTest`, `PublicWebsiteTest`, `CmsDataModelTest` | Active visible; inactive 404 + hidden from home/estimator/sitemap; CMS rename reflected; `?service=` preselect |
| **PRICING** | `PricingEngineTest`, `SeededAddonPricingTest`, `MoneyTest`, `PriceRangeTest` | Starting prices, bedrooms, every room modifier path, every condition flag, frequency discounts, furnishing %, add-ons, £5 rounding, regular minimum floor, bungalow=house start, no dead `percentage_adjustment` line items, admin price edit affects new quotes only, UI/DB add-on consistency |
| **ESTIMATOR** | `EstimateWizardTest`, `HardeningTest` | Single-page sections; dynamic description; one-off; flat=1 floor; split-level >1; free-text notes; past date / phone / email / postcode; required fields on submit; deep needs status; regular needs frequency; honeypot; state preserved; rate limit |
| **LEADS** | `QuoteRequestSubmissionTest`, `CrmLeadsCustomersTest` | Web + WhatsApp; duplicate-click protection; immutable snapshot; source tracking; admin status + final quote; confirmation page |
| **EMAILS** | `QuoteRequestMailAndDispatchTest`, submission tests | Internal + customer mails queued with reference/body; configured recipients; lead survives dispatch failure |
| **CUSTOMERS** | `CustomerMatcherTest`, `CrmLeadsCustomersTest` | Email match; phone match when email compatible; conflicting email+phone no merge; customer history |
| **BOOKINGS** | `BookingsPaymentsRevenueTest` | Won conversion; non-won blocked; double convert blocked; clash warning; cancel; calendar hides cancelled |
| **PAYMENTS** | `BookingsPaymentsRevenueTest` | Deposit/balance/outstanding; refunds vs revenue; overpayment |
| **DASHBOARD** | `DashboardMetricsTest`, `CrmDemoDashboardVerificationTest` | Critical stats vs DB; demo seed consistency |
| **SEO** | `SeoTest` | Titles/canonicals; JSON-LD without invented ratings; sitemap excludes inactive + admin + confirmation; robots; 404; trailing slash |
| **Recent work** | `HomeRecentWorkTest`, `ServicesVisibilityTest` | Toggle + published gate; seeded demo placeholders |

## Development demo data

```bash
php artisan migrate:fresh --seed
php artisan storage:link   # once
```

| Seeder | Contents | Production |
|--------|----------|------------|
| `AdminUserSeeder` | `admin@nghomecleaners.co.uk` / `password` | Skipped |
| `CmsSeeder` | Services, pricing, areas, FAQs, legal, site copy, **demo Recent Work SVG placeholders**, one `is_demo` testimonial | Demo extras skipped |
| `CrmDemoSeeder` | 6 customers, multi-status leads, bookings, deposits/balance/refund | Skipped |

**No fake Google/production review claims.** Demo testimonials are flagged `is_demo` and excluded from the homepage in production.

Recent Work placeholders are labelled “Demo placeholder — replace with real photos” in the SVG. Replace via **Website → Recent work** before go-live.

## Manual regression checklist

### Public site

- [ ] `/` hero, services, how-it-works, recent work (if enabled), coverage panel, testimonials, FAQ, sticky mobile CTA
- [ ] Toggle off Recent Work in Site settings → Homepage; section disappears
- [ ] Inactive service removed from home, services index, estimator, sitemap
- [ ] Coverage panel links to area pages; CTA opens estimate

### Estimator scenarios

- [ ] Regular clean: frequency chips visible; weekly “From £” lower than one-off
- [ ] Deep / end of tenancy: frequency hidden; property status required
- [ ] Commercial: rooms/extras hidden; “Priced per visit”
- [ ] Flat → 1 floor; split-level unlocks floors ≥ 2
- [ ] Sticky estimate updates when bedrooms / extras change (no full-page jump)
- [ ] Invalid phone / email / NG postcode / past date show errors and scroll into view
- [ ] Submit empty required fields → error summary; no lead created
- [ ] Web submit → confirmation `NG-*`; WhatsApp submit opens chat with reference
- [ ] Double-click submit does not duplicate leads
- [ ] Honeypot filled → redirect home, no lead

### CRM scenarios

- [ ] Guest cannot open `/admin/*`
- [ ] Lead appears under Quote requests with source badge and snapshot breakdown
- [ ] Status: New → Contacted → Quote sent → Won / Lost (timestamps set)
- [ ] Convert to booking only on Won and only once
- [ ] Manual phone lead create; conflicting phone+email does not merge customers
- [ ] Customer page shows linked leads/bookings

### Financial scenarios

- [ ] Booking agreed price; deposit then balance → outstanding decreases
- [ ] Refund reduces paid and dashboard revenue
- [ ] Clash warning for same-day overlapping windows (cancelled ignored)
- [ ] Dashboard: new leads, awaiting quotes, upcoming, completed, revenue, outstanding, conversion %, most-requested

### SEO checks

- [ ] Unique `<title>` + description + canonical on home, service, area, about, contact, quote
- [ ] `/sitemap.xml` — no `/admin`, no confirmation URLs, no inactive services/areas
- [ ] `/robots.txt` disallows `/admin` and confirmation
- [ ] Confirmation `noindex`; custom 404 `noindex`
- [ ] JSON-LD LocalBusiness present; no invented `aggregateRating`

## Phase handoff notes

See agent response for Implemented / Files / Database / Tests / Manual / Remaining after the suite is green.
