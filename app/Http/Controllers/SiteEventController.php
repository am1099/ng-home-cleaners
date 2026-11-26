<?php

namespace App\Http\Controllers;

use App\Services\SiteEventRecorder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SiteEventController extends Controller
{
    public function __invoke(Request $request, SiteEventRecorder $recorder): Response
    {
        $payload = $request->json()->all();

        if ($payload === []) {
            $decoded = json_decode($request->getContent(), true);
            $payload = is_array($decoded) ? $decoded : [];
        }

        $recorder->record($payload, $request);

        return response()->noContent();
    }
}
