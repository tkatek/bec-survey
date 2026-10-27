<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SurveyAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SurveyAlertResolutionController extends Controller
{
    public function __invoke(Request $request, SurveyAlert $alert): RedirectResponse
    {
        $alert->update([
            'active_key' => null,
            'read_at' => $alert->read_at ?? now(),
            'resolved_at' => now(),
            'resolved_by' => $request->user()->id,
            'resolution_reason' => 'administrator',
        ]);

        return back()->with('status', 'Alert resolved.');
    }
}
