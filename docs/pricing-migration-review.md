# Pricing migration review — bedroom bands → starting prices

**Date:** August 2026  
**Goal:** Replace per-bedroom base bands with starting price + per-extra-bedroom rules that owners can understand in under 30 seconds.

Historical `quote_requests.pricing_snapshot` rows are **not** recalculated.

---

## Method

1. Take the **1-bedroom** band as the **starting price** for each service × property type (most common booking size).
2. Set **bedrooms included = 1** (studio / 0-bed and 1-bed both use the starting price only).
3. Choose a **single extra-bedroom range per service** that approximates the old steps without reproducing every nonlinear jump.
4. Seed **bungalow** starting prices equal to **house** (estimator supports bungalow; old bands mapped bungalow → house).
5. Copy room modifier rates onto **per-service** extra-room rows (Deep and End of Tenancy get separate rows with the same £ values today).
6. Replace % condition uplift with **fixed £ ranges per service × condition** (~7% of a typical mid job).

---

## Regular clean

### Old base bands (£)

| Beds | Flat | House |
|------|------|-------|
| 0 | 65–75 | 70–80 |
| 1 | 75–85 | 75–90 |
| 2 | 85–100 | 95–110 |
| 3 | 110–125 | 130–150 |
| 4 | 140–160 | 170–195 |
| 5+ | 165–190 | 205–235 |

### Proposed simplified values

| Setting | Value |
|---------|--------|
| Flat starting | **£75–£85** (old 1-bed) |
| House starting | **£75–£90** (old 1-bed) |
| Bungalow starting | **£75–£90** (= house) |
| Bedrooms included | **1** |
| Each extra bedroom | **£25–£35** |

### Worked comparison (house, no extras)

| Beds | Old band | New (start + (beds−1)×extra) | Notes |
|------|----------|------------------------------|--------|
| 0–1 | 70–80 / 75–90 | 75–90 | Studio uses starting price (simplification) |
| 2 | 95–110 | 100–125 | Close |
| 3 | 130–150 | 125–160 | Close; old jumped harder at 3 beds |
| 4 | 170–195 | 150–195 | Min lower; max matches |
| 5+ | 205–235 | 175–230 | Simplified; large homes slightly lower on min |

Owners can raise the extra-bedroom range later without touching every bedroom row.

---

## Deep clean

### Old base bands (£)

| Beds | Flat | House |
|------|------|-------|
| 0 | 130–150 | 145–165 |
| 1 | 155–180 | 170–200 |
| 2 | 185–215 | 200–230 |
| 3 | 215–250 | 230–265 |
| 4 | 245–280 | 260–300 |
| 5+ | 275–315 | 290–330 |

### Proposed

| Setting | Value |
|---------|--------|
| Flat starting | **£155–£180** |
| House / bungalow starting | **£170–£200** |
| Bedrooms included | **1** |
| Each extra bedroom | **£30–£35** |

Old steps were already nearly linear (~£30–£35 per bedroom) — this maps closely.

---

## End of tenancy

### Old base bands (£)

| Beds | Flat | House |
|------|------|-------|
| 0 | 170–195 | 185–215 |
| 1 | 205–235 | 225–255 |
| 2 | 245–280 | 260–300 |
| 3 | 280–320 | 300–340 |
| 4 | 320–365 | 340–385 |
| 5+ | 355–405 | 375–430 |

### Proposed

| Setting | Value |
|---------|--------|
| Flat starting | **£205–£235** |
| House / bungalow starting | **£225–£255** |
| Bedrooms included | **1** |
| Each extra bedroom | **£35–£45** |

Again nearly linear historically; approximation stays within a few tens of pounds of old bands for typical sizes.

---

## Extra rooms (unchanged £ rates, new shape)

Old: one row per room type with Regular vs Deep/EOT columns.  
New: **service × room type** (Deep and EOT duplicated so they can diverge later).

| Room | Regular | Deep | End of tenancy |
|------|---------|------|----------------|
| Extra bathroom | £18–£20 | £40–£50 | £40–£50 |
| Separate WC | £6–£8 | £15–£18 | £15–£18 |
| Extra kitchen | £12–£15 | £28–£35 | £28–£35 |
| Extra reception | £12–£15 | £28–£35 | £28–£35 |
| Extra floor | £6–£8 | £15–£20 | £15–£20 |
| Extra room | £6–£8 | £15–£20 | £15–£20 |

---

## Conditions (new fixed ranges)

Old: +7% per flag, capped at 28% (flag identity ignored).  
New: fixed guide range per service × condition (same £ for each flag within a service).

| Service | Per condition |
|---------|----------------|
| Regular | **£8–£12** |
| Deep | **£15–£25** |
| End of tenancy | **£20–£30** |

Free-text condition notes still never change price.

---

## Frequency (unchanged)

| Frequency | Discount |
|-----------|----------|
| One-off | 0% |
| Weekly | 5% |
| Fortnightly | 2.5% |
| Monthly | 0% |

Applies to **Regular clean** only. Shown as a readable line item on new snapshots.

---

## Furnishing (Deep / EOT only, unchanged effect)

| Status | Adjustment shown to owners |
|--------|----------------------------|
| Empty | **−8%** |
| Part furnished | **0%** |
| Fully furnished | **+5%** |

---

## Advanced (kept)

| Setting | Value |
|---------|--------|
| Floors included in starting price | 2 |
| Round guide estimates to | £5 |
| Regular “from” floor (optional) | £55 |

---

## Removed from new calculations

- Per-bedroom base-band lookup  
- Range narrowing / signals / max narrowing  
- Single-price bias (60% of span)  
- Global % condition uplift  

---

## Add-ons

**Unchanged.** Existing `addons` table remains the only source of truth (Website → Add-ons).
