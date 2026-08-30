<?php

namespace App\Enums;

enum PricingLineItemKind: string
{
    case StartingPrice = 'starting_price';
    case ExtraBedrooms = 'extra_bedrooms';
    case ExtraRoom = 'extra_room';
    case Condition = 'condition';
    case FurnishingAdjustment = 'furnishing_adjustment';
    case FrequencyDiscount = 'frequency_discount';
    case Addon = 'addon';
    case MinimumFloor = 'minimum_floor';

    /** @deprecated Historical snapshots only */
    case Base = 'base';

    /** @deprecated Historical snapshots only */
    case RoomModifier = 'room_modifier';

    /** @deprecated Historical snapshots only */
    case StatusMultiplier = 'status_multiplier';

    /** @deprecated Historical snapshots only */
    case ConditionUplift = 'condition_uplift';

    /** @deprecated Historical snapshots only */
    case MinimumAdjustment = 'minimum_adjustment';

    /** @deprecated Historical snapshots only */
    case RangeNarrowing = 'range_narrowing';

    case FixedAdjustment = 'fixed_adjustment';

    case PercentageAdjustment = 'percentage_adjustment';
}
