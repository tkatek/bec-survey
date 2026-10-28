<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Conversation Room Feedback Report</title>
    <style>
        @page { margin: 34px 38px 42px; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #0b1739; font-family: "DejaVu Sans", sans-serif; font-size: 10.5px; line-height: 1.45; }
        h1, h2, h3, p { margin: 0; }
        .header { border-bottom: 1px solid #dce5f2; padding-bottom: 16px; }
        .logo { width: 180px; height: auto; }
        .title { margin-top: 14px; font-size: 23px; }
        .subtitle { margin-top: 5px; color: #52658b; }
        .meta { margin-top: 14px; width: 100%; border-collapse: collapse; }
        .meta td { width: 50%; border: 1px solid #e5ebf5; padding: 7px 9px; vertical-align: top; }
        .label { color: #64748b; font-size: 8.5px; text-transform: uppercase; }
        .value { margin-top: 2px; font-weight: 700; }
        .section { margin-top: 18px; }
        .section.break { page-break-before: always; }
        .section-title { border-bottom: 2px solid #1457ff; padding-bottom: 6px; font-size: 16px; }
        .kpis { margin-top: 12px; width: 100%; border-collapse: separate; border-spacing: 6px; }
        .kpis td { width: 25%; border: 1px solid #e5ebf5; border-radius: 8px; background: #f7faff; padding: 10px; vertical-align: top; }
        .kpi-number { margin-top: 4px; font-size: 19px; font-weight: 700; color: #1457ff; }
        .kpi-detail { margin-top: 3px; color: #52658b; font-size: 8.5px; }
        .chart-card { margin-top: 12px; border: 1px solid #e5ebf5; border-radius: 10px; padding: 12px; }
        .chart-card h2 { font-size: 14px; }
        .chart-note { margin-top: 3px; color: #64748b; font-size: 9px; }
        .question { margin-top: 12px; page-break-inside: avoid; border: 1px solid #e5ebf5; border-radius: 9px; padding: 10px; }
        .question h3 { font-size: 12px; }
        .question-meta { margin-top: 3px; color: #64748b; font-size: 8.5px; }
        table.distribution { margin-top: 7px; width: 100%; border-collapse: collapse; }
        .distribution th { background: #eef4ff; color: #334155; text-align: left; }
        .distribution th, .distribution td { border-bottom: 1px solid #e9eef6; padding: 5px 6px; }
        .distribution .number { width: 58px; text-align: right; }
        .bar { display: inline-block; width: 120px; height: 6px; border-radius: 3px; background: #e7edf7; vertical-align: middle; }
        .bar span { display: block; height: 6px; border-radius: 3px; background: #1457ff; }
        .notice { margin-top: 8px; border-left: 3px solid #1457ff; background: #f1f5ff; padding: 8px 10px; color: #42547a; }
        .feedback { margin-top: 8px; border: 1px solid #e5ebf5; border-radius: 8px; padding: 8px 10px; page-break-inside: avoid; }
        .feedback-tags { margin-top: 4px; color: #1457ff; font-size: 8.5px; }
        .empty { margin-top: 10px; border: 1px dashed #b9c6dc; border-radius: 8px; padding: 12px; color: #64748b; text-align: center; }
        .footer-note { margin-top: 16px; color: #64748b; font-size: 8px; }
    </style>
</head>
<body>
    @php
        $seriesPoints = collect($analytics['series']['points']);
        $chartWidth = 500;
        $plotTop = 12;
        $plotBottom = 106;
        $maximum = max(1, (int) $seriesPoints->max('value'));
        $pointDivisor = max(1, $seriesPoints->count() - 1);
        $svgPoints = $seriesPoints->values()->map(function (array $point, int $index) use ($chartWidth, $plotTop, $plotBottom, $maximum, $pointDivisor) {
            $x = 18 + (($chartWidth - 36) * ($index / $pointDivisor));
            $y = $plotBottom - (($point['value'] / $maximum) * ($plotBottom - $plotTop));
            return ['x' => $x, 'y' => $y, 'value' => $point['value'], 'label' => $point['label']];
        });
        $linePath = $svgPoints->map(fn (array $point, int $index) => ($index === 0 ? 'M ' : 'L ').round($point['x'], 2).' '.round($point['y'], 2))->join(' ');
        $areaPath = $svgPoints->isEmpty() ? '' : $linePath.' L '.round($svgPoints->last()['x'], 2).' '.$plotBottom.' L '.round($svgPoints->first()['x'], 2).' '.$plotBottom.' Z';
        $questionsBySection = collect($analytics['questions'])->groupBy('section');
    @endphp

    <header class="header">
        @if ($logoData)
            <img class="logo" src="{{ $logoData }}" alt="Boston English Center">
        @endif
        <h1 class="title">Conversation Room Feedback Report</h1>
        <p class="subtitle">A filtered summary of completed, non-demo survey responses.</p>

        <table class="meta">
            <tr>
                <td><div class="label">Selected period</div><div class="value">{{ $analytics['meta']['filter']['label'] }}</div></td>
                <td><div class="label">Reporting timezone</div><div class="value">{{ $analytics['meta']['timezone'] }}</div></td>
            </tr>
            <tr>
                <td><div class="label">Response scope</div><div class="value">{{ $analytics['meta']['scope'] }}</div></td>
                <td><div class="label">Data as of</div><div class="value">{{ \Carbon\CarbonImmutable::parse($analytics['meta']['dataAsOf'])->format('M j, Y g:i A') }}</div></td>
            </tr>
        </table>
        <p class="footer-note">{{ $analytics['meta']['exclusions'] }} Anonymous response counts are not verified unique-student counts.</p>
    </header>

    <section class="section">
        <h2 class="section-title">Overview</h2>
        <table class="kpis">
            <tr>
                <td><div class="label">Responses in period</div><div class="kpi-number">{{ number_format($analytics['overview']['responses']) }}</div><div class="kpi-detail">Distinct completed response records.</div></td>
                <td><div class="label">Positive activity feedback</div><div class="kpi-number">{{ $analytics['overview']['positiveActivity']['percentage'] === null ? 'N/A' : $analytics['overview']['positiveActivity']['percentage'].'%' }}</div><div class="kpi-detail">{{ $analytics['overview']['positiveActivity']['count'] }} of {{ $analytics['overview']['positiveActivity']['denominator'] }} valid answers.</div></td>
                <td><div class="label">Main attendance barrier</div><div class="value">{{ $analytics['overview']['mainBarrier']['label'] }}</div><div class="kpi-detail">{{ $analytics['overview']['mainBarrier']['count'] }} responses - {{ $analytics['overview']['mainBarrier']['percentage'] === null ? 'N/A' : $analytics['overview']['mainBarrier']['percentage'].'%' }}</div></td>
                <td><div class="label">Most preferred time</div><div class="value">{{ $analytics['overview']['preferredTime']['label'] }}</div><div class="kpi-detail">{{ $analytics['overview']['preferredTime']['count'] }} responses - {{ $analytics['overview']['preferredTime']['percentage'] === null ? 'N/A' : $analytics['overview']['preferredTime']['percentage'].'%' }}</div></td>
            </tr>
        </table>
    </section>

    <section class="chart-card">
        <h2>Responses over time</h2>
        <p class="chart-note">{{ $analytics['series']['label'] }} in {{ $analytics['series']['timezone'] }}. Total: {{ $analytics['series']['total'] }}.</p>
        @if ($seriesPoints->isEmpty())
            <div class="empty">No elapsed reporting buckets are available.</div>
        @else
            <svg viewBox="0 0 500 126" width="100%" height="126" role="img" aria-label="Responses over time">
                <defs><linearGradient id="reportFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#1457ff" stop-opacity=".28"/><stop offset="1" stop-color="#1457ff" stop-opacity=".03"/></linearGradient></defs>
                <line x1="18" y1="106" x2="482" y2="106" stroke="#dfe7f3"/>
                <line x1="18" y1="59" x2="482" y2="59" stroke="#edf1f7"/>
                <line x1="18" y1="12" x2="482" y2="12" stroke="#edf1f7"/>
                <path d="{{ $areaPath }}" fill="url(#reportFill)"/>
                <path d="{{ $linePath }}" fill="none" stroke="#1457ff" stroke-width="2.2"/>
                @foreach ($svgPoints as $point)
                    <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="2.5" fill="#fff" stroke="#1457ff" stroke-width="1.5"/>
                @endforeach
            </svg>
        @endif
    </section>

    @foreach ($questionsBySection as $sectionName => $questions)
        <section class="section break">
            <h2 class="section-title">{{ $sectionName }}</h2>
            @foreach ($questions as $question)
                <article class="question">
                    <h3>{{ $question['questionOrder'] }}. {{ $question['prompt'] }}</h3>
                    <p class="question-meta">Answered: {{ $question['answered'] }} | Skipped: {{ $question['skipped'] }} | Not present/ineligible: {{ $question['ineligible'] }}</p>
                    @forelse ($question['distributions'] as $distribution)
                        @if ($distribution['group'])
                            <p class="question-meta"><strong>{{ $distribution['group'] }}</strong> - {{ $distribution['answered'] }} answers</p>
                        @endif
                        <table class="distribution">
                            <thead><tr><th>Answer option</th><th>Distribution</th><th class="number">Count</th><th class="number">Share</th></tr></thead>
                            <tbody>
                            @foreach ($distribution['options'] as $option)
                                <tr>
                                    <td>{{ $option['label'] }}</td>
                                    <td><span class="bar"><span style="width: {{ min(100, $option['percentage'] ?? 0) }}%"></span></span></td>
                                    <td class="number">{{ $option['count'] }}</td>
                                    <td class="number">{{ $option['percentage'] === null ? 'N/A' : $option['percentage'].'%' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @empty
                        <div class="empty">Open feedback is summarized in the reviewed feedback section.</div>
                    @endforelse
                    @if ($question['isMultiSelect'])
                        <div class="notice">Multiple selections allowed; percentages may exceed 100%. Percentages use distinct respondents answering this question.</div>
                    @endif
                </article>
            @endforeach
        </section>
    @endforeach

    <section class="section break">
        <h2 class="section-title">Reviewed written feedback themes</h2>
        <div class="notice">Theme counts may overlap because one response can have more than one administrator-reviewed tag.</div>
        @forelse ($analytics['feedbackThemes'] as $theme)
            <div class="feedback"><strong>{{ $theme['label'] }}</strong> - {{ $theme['count'] }} matching responses</div>
        @empty
            <div class="empty">No written feedback has been reviewed and tagged in this period.</div>
        @endforelse

        <h2 class="section-title" style="margin-top: 18px">Reviewed excerpts</h2>
        @forelse ($analytics['reviewedFeedback'] as $feedback)
            <div class="feedback">
                <div>{{ $feedback['text'] }}</div>
                <div class="feedback-tags">{{ implode(', ', $feedback['themes']) }} | {{ $feedback['date'] }}</div>
            </div>
        @empty
            <div class="empty">No privacy-reviewed excerpts are available for this period.</div>
        @endforelse
    </section>

    <section class="section">
        <h2 class="section-title">Active feedback alerts</h2>
        <p class="chart-note">Monitoring period: {{ $alerts['monitoringLabel'] }}. This alert window is separate from the dashboard filter.</p>
        @forelse ($alerts['alerts'] as $alert)
            <div class="feedback"><strong>{{ $alert['title'] }}</strong><br>{{ $alert['matchingCount'] }} matching responses of {{ $alert['denominator'] }} - threshold {{ $alert['threshold'] }}.<br>{{ $alert['explanation'] }}</div>
        @empty
            <div class="empty">No active threshold alerts.</div>
        @endforelse
    </section>
</body>
</html>
