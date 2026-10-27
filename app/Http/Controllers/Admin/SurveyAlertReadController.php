<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SurveyAlert;
use Illuminate\Http\RedirectResponse;

class SurveyAlertReadController extends Controller
{
    public function __invoke(SurveyAlert $alert): RedirectResponse
    {
        $alert->update(['read_at' => $alert->read_at ?? now()]);

        return back();
    }
}
