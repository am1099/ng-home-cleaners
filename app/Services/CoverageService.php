<?php

namespace App\Services;

use App\Models\ServiceArea;

class CoverageService
{
    /**
     * @return array{covered: bool, district: ?string, area: ?ServiceArea, message: string}
     */
    public function check(string $input): array
    {
        $district = $this->districtFrom($input);

        if ($district === null) {
            return [
                'covered' => false,
                'district' => null,
                'area' => null,
                'message' => 'Enter a Nottingham postcode or district such as NG7 or NG7 1AA.',
            ];
        }

        $area = ServiceArea::query()
            ->active()
            ->where('postcode_label', $district)
            ->first();

        if ($area) {
            return [
                'covered' => true,
                'district' => $district,
                'area' => $area,
                'message' => 'Yes — we cover '.$district.' ('.$area->name.'). Travel inside NG1 to NG16 is included in the price.',
            ];
        }

        return [
            'covered' => false,
            'district' => $district,
            'area' => null,
            'message' => $district.' sits outside NG1 to NG16. Send the full postcode anyway and we will say if we can make it work.',
        ];
    }

    public function districtFrom(string $input): ?string
    {
        $compact = strtoupper(preg_replace('/\s+/', '', $input) ?? '');

        if (preg_match('/^(NG1[0-6])(?=\d?[A-Z]{0,2}$)/', $compact, $matches)) {
            return $matches[1];
        }

        if (preg_match('/^(NG[1-9])(?=\d?[A-Z]{0,2}$)/', $compact, $matches)) {
            return $matches[1];
        }

        if (preg_match('/^(NG\d{1,2})/', $compact, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
