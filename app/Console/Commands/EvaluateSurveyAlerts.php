<?php

namespace App\Console\Commands;

use App\Services\SurveyAlertService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('survey:evaluate-alerts')]
#[Description('Evaluate Conversation Room feedback alert thresholds')]
class EvaluateSurveyAlerts extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(SurveyAlertService $alerts): int
    {
        $alerts->evaluate();
        $this->info('Survey feedback alerts evaluated.');

        return self::SUCCESS;
    }
}
