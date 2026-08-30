# NG Home Cleaners — Reference Pricing Documentation

**Source:** `docs/reference/contact.dc.html` JavaScript (`estimate()`, `bands()`, `extraList()`, room modifiers)  
**Audit date:** August 2026  
**Currency:** GBP (£)  
**Purpose:** Document every pricing rule embedded in the reference quote estimator before rebuilding a single-source-of-truth pricing engine in Laravel CRM.

---

## 1. Overview

The reference site calculates guide prices entirely in the browser. There is **no server-side validation** of prices on submission. The estimate panel updates reactively as the user completes the form.

### 1.1 Estimate output types

| Service | Output format | Example |
|---------|---------------|---------|
| Regular clean | Single “From £X a visit” | After frequency discount |
| Deep clean | Range “£X–£Y” | Rounded to nearest £5 |
| End of tenancy | Range “£X–£Y” | Rounded to nearest £5 |
| Office/commercial | Fixed message | “Priced per visit” — no numeric estimate |
| Incomplete form | Placeholder text | “Pick a service to start” / “Add the bedrooms” |

### 1.2 Rounding

All displayed prices use `round5(n)`: `Math.round(n / 5) * 5` (nearest £5).

---

## 2. Base price bands

Base prices are looked up by **service × property type × bedroom count**.

- **Bedroom keys:** `0` (studio), `1`, `2`, `3`, `4`, `5` (5 bed or more)
- **Property types:** `Flat`, `House`
- **Values:** `[lo, hi]` inclusive range in pounds

### 2.1 Regular clean

Charge-out model comment in source: ~£38–43/hr against £15/hr sub, £3 supplies, £2.50 travel (~55–60% gross).

| Bedrooms | Flat [lo, hi] | House [lo, hi] |
|----------|---------------|----------------|
| 0 (studio) | 65 – 75 | 70 – 80 |
| 1 | 75 – 85 | 75 – 90 |
| 2 | 85 – 100 | 95 – 110 |
| 3 | 110 – 125 | 130 – 150 |
| 4 | 140 – 160 | 170 – 195 |
| 5+ | 165 – 190 | 205 – 235 |

### 2.2 Deep clean

Comment in source: hours × 1.8 @ ~£32/hr charge-out.

| Bedrooms | Flat [lo, hi] | House [lo, hi] |
|----------|---------------|----------------|
| 0 (studio) | 130 – 150 | 145 – 165 |
| 1 | 155 – 180 | 170 – 200 |
| 2 | 185 – 215 | 200 – 230 |
| 3 | 215 – 250 | 230 – 265 |
| 4 | 245 – 280 | 260 – 300 |
| 5+ | 275 – 315 | 290 – 330 |

### 2.3 End of tenancy clean

Comment in source: hours × 2.2 @ ~£34/hr charge-out (sub £20/hr).

| Bedrooms | Flat [lo, hi] | House [lo, hi] |
|----------|---------------|----------------|
| 0 (studio) | 170 – 195 | 185 – 215 |
| 1 | 205 – 235 | 225 – 255 |
| 2 | 245 – 280 | 260 – 300 |
| 3 | 280 – 320 | 300 – 340 |
| 4 | 320 – 365 | 340 – 385 |
| 5+ | 355 – 405 | 375 – 430 |

### 2.4 Office or commercial

**No base bands.** Estimate returns:

- Headline: “Priced per visit”
- Detail: “Commercial premises are quoted after a short walk-round…”
- CRM value: “Quote after walk-round”

---

## 3. Room modifiers

Applied **after** base band lookup. Uses different per-unit rates for **Regular** vs **Deep/EOT**.

### 3.1 Modifier rates (per unit, added to both lo and hi)

| Room type | Count logic | Regular [lo, hi] per unit | Deep/EOT [lo, hi] per unit |
|-----------|-------------|---------------------------|----------------------------|
| Extra bathroom | `max(bathrooms - 1, 0)` | 18 – 20 | 40 – 50 |
| Separate toilet (WC) | `wcs` (all count) | 6 – 8 | 15 – 18 |
| Extra kitchen | `max(kitchens - 1, 0)` | 12 – 15 | 28 – 35 |
| Extra reception room | `max(living - 1, 0)` | 12 – 15 | 28 – 35 |
| Extra floor | `max(floors - 2, 0)` | 6 – 8 | 15 – 20 |
| Extra room (checkbox) | `rooms.length` (Conservatory, Office, Utility, Loft) | 6 – 8 | 15 – 20 |

**Source comments:** Extras priced from hours — 0.75h bathroom, 0.25h WC, 0.5h kitchen/reception, 0.25h floor/extra room.

### 3.2 Default room counts (initial state)

| Field | Default | Notes |
|-------|---------|-------|
| Bathrooms | 1 | First bathroom included in base |
| WCs | 0 | — |
| Kitchens | 1 | First kitchen included |
| Reception rooms | 1 | First reception included |
| Floors | **2** | **Bug:** should be 1 for typical flat |

With defaults, **no room surcharges apply** except the implicit floor baseline (first 2 floors free).

---

## 4. Property status multipliers

Applied to **Deep clean** and **End of tenancy** only (`!regular`).

| Status | Multiplier | Applied to |
|--------|------------|------------|
| Empty | × 0.92 (−8%) | lo and hi |
| Furnished | × 1.05 (+5%) | lo and hi |
| Part-furnished | **No modifier** | Treated same as default (no change) |

**Note:** “Part-furnished” appears in the UI but has no pricing effect in reference JavaScript.

Regular cleans do **not** apply furnished/empty multipliers.

---

## 5. Condition uplift

Each checked condition flag adds **7%** to both lo and hi (multiplicative, compounding on current totals).

| Flag | Label in UI |
|------|-------------|
| Heavy limescale | Heavy limescale |
| Mould | Mould or damp patches |
| Pets | Pets in the home |
| Heavy grease | Heavy kitchen grease |
| Clutter | Cluttered surfaces |
| Not cleaned in months | Not cleaned in months |

**Formula:**

```
uplift = 1 + min(flagCount × 0.07, 0.28)
lo *= uplift
hi *= uplift
```

| Flags ticked | Uplift |
|--------------|--------|
| 1 | +7% |
| 2 | +14% |
| 3 | +21% |
| 4+ | +28% (capped) |

Condition uplift applies to **Regular, Deep, and EOT** (not gated by service type in code).

---

## 6. Regular clean minimum and frequency discounts

### 6.1 Minimum price (Regular only)

After room modifiers and condition uplift:

```
lo = max(lo, 55)
hi = max(hi, 65)
```

Comment in source: “2h minimum at £24/hr” (marketing copy elsewhere says two-hour minimum visit).

### 6.2 Frequency discounts (Regular only)

Applied when computing the single displayed “From £X a visit” price (not to the lo/hi band directly).

| Frequency | Discount |
|-----------|----------|
| Weekly | 5% (`× 0.95`) |
| Fortnightly | 2.5% (`× 0.975`) |
| Monthly | **0%** (no discount in code) |

**Default frequency:** Fortnightly.

### 6.3 Regular single-price formula

```
midBias = lo + (hi - lo) × 0.6   // 60% toward top of range
single = max(55, round5(midBias × (1 - freqOff)))
```

Display: `From £{single} a visit`

Frequency note appended when discount applies: e.g. “Fortnightly visits include a 2.5% standing discount.”

---

## 7. Range narrowing (Deep / EOT / band display)

After modifiers, status, condition, and minimum (regular), the estimator **narrows** the lo–hi spread based on “signals” (not applied to regular’s single-price path before frequency calc, but applied to lo/hi before regular min/single).

### 7.1 Signals (each worth 12% narrowing, max 50%)

| Signal | Trigger |
|--------|---------|
| 1 | Any condition flags OR any extra rooms checked |
| 2 | Postcode trimmed length ≥ 3 |
| 3 | Preferred date not empty |
| 4 | Parking ≠ “Free parking outside” OR access ≠ “I will be home” |
| 5 | Details text length > 12 characters |

**Formula:**

```
mid = (lo + hi) / 2
half = ((hi - lo) / 2) × (1 - min(signals × 0.12, 0.5))
lo = mid - half
hi = mid + half
```

Deep/EOT final display: `£{round5(lo)}–{round5(hi)}`

Basis text includes “Answer the condition and access questions and this range narrows” when `signals < 2`.

---

## 8. Add-ons (extras)

Priced **on top** of the clean estimate after range narrowing. **Not** subject to range narrowing.

### 8.1 Add-on catalogue

| Key | UI label | lo | hi | Special rules |
|-----|----------|----|----|---------------|
| Single oven | Oven interior (single) | 45 | 55 | — |
| Double oven | Double oven or range cooker | 70 | 85 | — |
| Fridge and freezer | Inside fridge and freezer | 40 | 50 | — |
| Inside wardrobes | Inside wardrobes and drawers | 35 | 45 | — |
| Wall wipe | Light wall wipe-down throughout | 45 | 65 | Long disclaimer note |
| Limescale treatment | Heavy limescale or mould treatment (per bathroom) | 30 | 70 | **`perBath: true`** |
| Rubbish removal | Rubbish removal | 40 | 120 | **`from: true`** (display prefix) |

### 8.2 Per-bathroom multiplier (Limescale treatment)

```
bathCount = max(1, bathrooms + wcs)
addonTotal = lo/hi + (lo/hi × bathCount)   // for perBath items only
```

Uses combined bathrooms + separate WCs, minimum 1.

### 8.3 Extras total calculation

```javascript
extrasTotal = sum of selected add-ons:
  mult = perBath ? max(1, baths + wcs) : 1
  lo += x.lo × mult
  hi += x.hi × mult
```

Then: `lo += ex.lo; hi += ex.hi` on main estimate.

### 8.4 Display vs calculation conflict (BUG)

**UI checkbox labels** (from `extraOptions`):

```javascript
label: x.label + ' · ' + (x.from ? 'from £' : '£') + x.lo
```

Examples shown to customer:

- “Oven interior (single) · **£45**” (actually £45–£55)
- “Rubbish removal · **from £40**” (actually £40–£120)
- “Heavy limescale… (per bathroom) · **£30**” (actually £30–£70 × bath count)

**Calculation** always uses full `[lo, hi]` range (and per-bath multiplier where applicable).

| Add-on | Customer sees | Calculation uses |
|--------|-----------------|------------------|
| Single oven | £45 | £45–£55 added to range |
| Double oven | £70 | £70–£85 |
| Fridge/freezer | £40 | £40–£50 |
| Wardrobes | £35 | £35–£45 |
| Wall wipe | £45 | £45–£65 |
| Limescale (per bath) | £30 | (£30–£70) × bath count |
| Rubbish | from £40 | £40–£120 |

**Rebuild rule:** One CRM price record must power label, calculation, review step, emails, and stored quote breakdown. Display should show the same range or single price used in maths (e.g. “£45–£55” or “from £40” where the upper bound still applies to the guide maximum).

### 8.5 Add-on disclaimer notes (shown when selected)

**Wall wipe:** Removal of scuff marks, crayon, pen, grease and visible wall marks where possible. Results vary; some marks permanent. Areas unreachable without specialist equipment excluded.

**Limescale treatment:** Built-up limescale on taps, screens, tiling; surface mould on sealant/grout. Heavy staining, perished sealant, grout mould may not fully recover.

---

## 9. Worked example (Deep clean, 2 bed house)

**Inputs:**

- Service: Deep clean  
- Type: House, 2 bed  
- Rooms: 1 bath, 0 WC, 1 kitchen, 1 living, **2 floors** (default)  
- Status: Furnished  
- Condition: Pets + Heavy limescale (2 flags → +14%)  
- Extras: Single oven  
- Postcode entered, no date  

**Step 1 — Base:** House 2 bed Deep = `[200, 230]`

**Step 2 — Room modifiers:** All defaults → +0

**Step 3 — Furnished:** × 1.05 → `[210, 241.5]`

**Step 4 — Condition:** × 1.14 → `[239.4, 275.31]`

**Step 5 — Narrowing:** 2 signals (condition + postcode) → 24% tighter  
- mid ≈ 257.35, half ≈ 21.59 → `[235.76, 278.94]`

**Step 6 — Extras:** Single oven + `[45, 55]` → `[280.76, 333.94]`

**Step 7 — Display:** `£280–£335` (rounded to £5)

**UI would show oven add-on as “· £45”** whilst adding £45–£55 to the range.

---

## 10. Worked example (Regular clean, 1 bed flat, weekly)

**Inputs:**

- Service: Regular, Weekly  
- Flat, 1 bed  
- Defaults for rooms, furnished, no flags  

**Base:** `[75, 85]`  
**Modifiers:** none  
**Min check:** lo=75≥55, hi=85≥65 ✓  
**Narrowing:** 0 signals → full width  
**Single price:** `lo + (hi-lo)×0.6 = 75 + 6 = 81`  
**Weekly 5% off:** 81 × 0.95 = 76.95 → **round5 → £75**

**Display:** “From £75 a visit”

---

## 11. Content-linked pricing mentions (non-estimator)

These appear in marketing copy but are **not** in the JavaScript calculator:

| Mention | Location | Value |
|---------|----------|-------|
| Internal glass extra | Services “not included” | from £20 |
| Two-hour minimum | Services regular card; form helper | policy, not a separate line item |
| Travel inside NG1–NG16 | Services pricing bullets | included (no surcharge in JS) |
| Van load rubbish | Services not included | quoted separately (Rubbish add-on £40–£120 in form) |

---

## 12. CRM / Google Form fields (pricing-related)

Hidden fields submitted with estimate:

| Field | Content |
|-------|---------|
| `entry.1912039907` | `estimateForCrm` — headline price or range text |
| `entry.860632411` | Service type |
| `entry.1646095870` | Source (default “Website quote form”) |
| Property details block | Full room/condition/extras breakdown in `propertyDetails()` |

CRM receives the **display headline** (`est.crm`), not a structured JSON breakdown.

---

## 13. Pricing conflicts summary

| ID | Conflict | Severity | Rebuild guidance |
|----|----------|----------|------------------|
| P1 | Add-on UI shows `lo` only; calc uses `lo–hi` | High | Single price record; show range or consistent “from” |
| P2 | Part-furnished option has no modifier | Medium | Define rule or remove option |
| P3 | Monthly frequency no discount | Low | Add rule or remove option |
| P4 | Default 2 floors inflates “extra floor” threshold | Medium | Default 1 floor for flats |
| P5 | Deep clean marketing says oven included; homepage says add-on | High | CMS content alignment |
| P6 | Services page: oven included deep/EOT; form sells oven extra | Medium | Clarify oven interior as optional extra even on deep, or remove from extras when deep selected |
| P7 | Condition uplift on regular may exceed minimum band differently than deep | Low | Test edge cases in pricing engine tests |
| P8 | No server-side price verification on submit | High | Laravel PricingService + persisted snapshot |

---

## 14. Rebuild pricing engine requirements

The Laravel pricing service should implement:

1. **Base bands** — Table 2.1–2.3 (CRM-editable)  
2. **Room modifiers** — Table 3.1 with service-type branching  
3. **Status multipliers** — Section 4 (deep/EOT only)  
4. **Condition uplift** — Section 5 (7% per flag, max 28%)  
5. **Regular minimum** — £55 lo / £65 hi floor after modifiers  
6. **Frequency discounts** — Weekly 5%, Fortnightly 2.5%  
7. **Regular single price** — 60% bias + discount + round5  
8. **Range narrowing** — Section 7 signals  
9. **Add-ons** — Section 8 with per-bath and from-price display rules  
10. **Rounding** — Nearest £5 on all outputs  
11. **Snapshot on lead save** — Immutable breakdown even if CRM prices change later  

### 14.1 Automated tests (minimum)

- Each base band lookup (service × type × beds)  
- Each room modifier at count 0, 1, 2  
- Empty / furnished / part-furnished  
- Condition 0, 1, 4, 5 flags (cap at 28%)  
- Regular minimum enforcement  
- Weekly vs fortnightly vs monthly  
- Range narrowing 0–5 signals  
- Each add-on alone and combined  
- **Add-on displayed price equals calculated increment**  
- Limescale per-bath multiplier with WCs  
- Commercial → no numeric price  

---

## 15. Source code reference

Pricing logic lives in `docs/reference/contact.dc.html` approximately lines 409–577:

- `extraList()` — add-on definitions  
- `extrasTotal()` — add-on aggregation  
- `bands()` — base price tables  
- `estimate()` — full calculation pipeline  
- `extraOptions` mapping — display label bug (`x.lo` only)  

## 16. Rebuild checklist — known estimator defects

When implementing the Livewire quote wizard, verify each item below is **fixed** (not reproduced):

- [ ] Service description changes immediately when clean type changes (from CRM `Service` record)
- [ ] Regular cleaning offers **One-off**, Weekly, Fortnightly, Monthly
- [ ] Flats default to **1 floor**; split-level / maisonette option available
- [ ] Condition step includes free-text “Anything else we should know?”
- [ ] Add-on displayed price **equals** calculated adjustment (see §8.4)
- [ ] Preferred date uses **arrival windows**, not exact 30-minute slots
- [ ] Date/availability copy does not imply live booking
- [ ] Full estimate summary shows all selections before submit
- [ ] Server-side validation on all fields (UK phone, NG postcode, past dates blocked)
- [ ] No em-dash-heavy AI copy in customer-facing strings

---

*For site structure, UX bugs, and content inconsistencies, see `docs/reference-audit.md`.*
