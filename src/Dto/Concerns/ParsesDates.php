<?php

namespace CodebarAg\Zendesk\Dto\Concerns;

use DateTimeInterface;
use Illuminate\Support\Carbon;

trait ParsesDates
{
    // Carbon::parse(null) returns "now", which would silently invent timestamps.
    protected static function parseDate(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value);
        }

        return Carbon::parse($value);
    }
}
