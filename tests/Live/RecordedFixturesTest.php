<?php

// Reminder: the live fixtures must be recorded against the real Zendesk API before they can be trusted.
it('replays fixtures recorded from the live Zendesk API', function () {
    $synthetic = collect(glob(__DIR__.'/../Fixtures/Saloon/*/*.json'))
        ->filter(fn (string $path): bool => (json_decode(file_get_contents($path), true)['context']['synthetic'] ?? false) === true)
        ->map(fn (string $path): string => basename(dirname($path)).'/'.basename($path, '.json'))
        ->values();

    if ($synthetic->isNotEmpty()) {
        $this->markTestSkipped(sprintf(
            '%d fixtures are hand-written placeholders (%s). Record them with `composer test:record`.',
            $synthetic->count(),
            $synthetic->implode(', '),
        ));
    }

    expect($synthetic)->toBeEmpty();
})->group('live');
