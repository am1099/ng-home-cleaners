<?php

namespace App\Enums;

enum ParkingOption: string
{
    case OnStreetFree = 'on_street_free';
    case OnStreetPermit = 'on_street_permit';
    case Driveway = 'driveway';
    case AllocatedSpace = 'allocated_space';
    case NearbyCarPark = 'nearby_car_park';
    case Restricted = 'restricted';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::OnStreetFree => 'On-street (free)',
            self::OnStreetPermit => 'On-street (permit or pay)',
            self::Driveway => 'Driveway',
            self::AllocatedSpace => 'Allocated parking space',
            self::NearbyCarPark => 'Car park nearby',
            self::Restricted => 'Restricted / no parking',
            self::Other => 'Other',
        };
    }

    /**
     * @return list<string>
     */
    public static function labels(): array
    {
        return array_map(fn (self $option) => $option->label(), self::cases());
    }
}
