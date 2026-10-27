<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SurveyAlert;
use Illuminate\Http\RedirectResponse;

class SurveyAlertReadAllController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        SurveyAlert::query()
            ->whereNull('resolved_at')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back();
    }
}
