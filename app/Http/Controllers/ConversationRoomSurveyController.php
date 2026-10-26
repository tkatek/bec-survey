<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ConversationRoomSurveyController extends Controller
{
    /**
     * Display the Conversation Room survey.
     */
    public function create(): View
    {
        return view('conversation-room.create');
    }

    /**
     * Display the survey completion page.
     */
    public function thankYou(): View
    {
        return view('conversation-room.thank-you');
    }
}
