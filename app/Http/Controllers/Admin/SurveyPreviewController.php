<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\SurveyDraftManager;
use Illuminate\View\View;

class SurveyPreviewController extends Controller
{
    public function __invoke(SurveyDraftManager $surveys): View
    {
        $version = $surveys->editingVersion();

        return view('conversation-room.create', $surveys->surveyViewData($version, true));
    }
}
