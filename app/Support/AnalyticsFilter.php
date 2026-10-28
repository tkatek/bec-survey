<?php

namespace App\Support;

use Carbon\CarbonImmutable;

class AnalyticsFilter
{
    public function __construct(
        public readonly string $preset,
        public readonly CarbonImmutable $localStart,
        public readonly CarbonImmutable $localEnd,
        public readonly CarbonImmutable $databaseStart,
        public readonly CarbonImmutable $databaseEnd,
        public readonly CarbonImmutable $cutoff,
        public readonly string $timezone,
        public readonly string $label,
    ) {}

    /**
     * @param  array{preset: string, start?: string|null, end?: string|null}  $validated
     */
    public static function fromValidated(array $validated): self
    {
        $timezone = (string) config('survey.reporting_timezone', config('app.timezone'));
        $databaseTimezone = (string) config('app.timezone', 'UTC');
        $now = CarbonImmutable::now($timezone);
        $preset = $validated['preset'];

        [$start, $end, $label] = match ($preset) {
            'today' => [$now->startOfDay(), $now, 'Today'],
            'last7' => [$now->subDays(6)->startOfDay(), $now, 'Last 7 days'],
            'month' => [$now->startOfMonth(), $now, 'This month'],
            'custom' => self::customPeriod($validated, $now),
        };

        return new self(
            preset: $preset,
            localStart: $start,
            localEnd: $end,
            databaseStart: $start->setTimezone($databaseTimezone),
            databaseEnd: ($end->equalTo($now) ? $end->addSecond() : $end)->setTimezone($databaseTimezone),
            cutoff: $now,
            timezone: $timezone,
            label: $label,
        );
    }

    /**
     * @return array<string, int|string>
     */
    public function query(): array
    {
        $query = ['preset' => $this->preset];

        if ($this->preset === 'custom') {
            $query['start'] = $this->localStart->toDateString();
            $query['end'] = $this->displayEndDate();
        }

        return $query;
    }

    /**
     * @return array<string, int|string>
     */
    public function summary(): array
    {
        return [
            'preset' => $this->preset,
            'label' => $this->label,
            'start' => $this->localStart->toDateString(),
            'end' => $this->displayEndDate(),
            'timezone' => $this->timezone,
            'cutoff' => $this->cutoff->toIso8601String(),
        ];
    }

    public function dayCount(): int
    {
        return (int) $this->localStart->startOfDay()->diffInDays($this->localEnd->startOfDay()) + 1;
    }

    public function includesCurrentDay(): bool
    {
        return $this->displayEndDate() === $this->cutoff->toDateString();
    }

    private function displayEndDate(): string
    {
        if ($this->localEnd->equalTo($this->cutoff)) {
            return $this->cutoff->toDateString();
        }

        return $this->localEnd->subMicrosecond()->toDateString();
    }

    /**
     * @param  array{start?: string|null, end?: string|null}  $validated
     * @return array{CarbonImmutable, CarbonImmutable, string}
     */
    private static function customPeriod(array $validated, CarbonImmutable $now): array
    {
        $timezone = $now->timezone->getName();
        $start = CarbonImmutable::parse($validated['start'], $timezone)->startOfDay();
        $selectedEnd = CarbonImmutable::parse($validated['end'], $timezone)->startOfDay();
        $end = $selectedEnd->isToday() ? $now : $selectedEnd->addDay();
        $label = $start->isSameDay($selectedEnd)
            ? $start->format('M j, Y')
            : $start->format('M j, Y').' - '.$selectedEnd->format('M j, Y');

        return [$start, $end, $label];
    }
}
