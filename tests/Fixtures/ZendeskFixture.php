<?php

namespace CodebarAg\Zendesk\Tests\Fixtures;

use Saloon\Data\RecordedResponse;
use Saloon\Http\Faking\Fixture;
use Saloon\Http\Faking\MockResponse;

/**
 * Replays recorded Zendesk responses. With ZENDESK_RECORD_FIXTURES=true every request is sent to
 * the live API and the fixture is overwritten. Secrets and personal data are redacted before saving.
 */
class ZendeskFixture extends Fixture
{
    public const string SUBDOMAIN = 'codebar-zendesk';

    public static function recording(): bool
    {
        return filter_var(getenv('ZENDESK_RECORD_FIXTURES'), FILTER_VALIDATE_BOOL);
    }

    public static function exists(string $name): bool
    {
        return is_file(__DIR__.'/Saloon/'.$name.'.json');
    }

    public function getMockResponse(): ?MockResponse
    {
        return self::recording() ? null : parent::getMockResponse();
    }

    /**
     * Header names (case-insensitive) whose values are replaced. Saloon's own header redaction
     * skips array values, which is how real responses store headers, so beforeSave() handles them.
     *
     * @return array<string, string>
     */
    protected function redactedHeaders(): array
    {
        return [
            'Set-Cookie' => 'REDACTED',
            'Report-To' => 'REDACTED',
            'NEL' => 'REDACTED',
            'CF-RAY' => 'REDACTED',
            'x-request-id' => 'REDACTED',
            'x-zendesk-origin-server' => 'REDACTED',
        ];
    }

    protected function defineSensitiveJsonParameters(): array
    {
        return [
            'access_token' => 'redacted-access-token',
            'refresh_token' => fn (mixed $value) => $value === null ? null : 'redacted-refresh-token',
            'email' => fn (mixed $value) => $value === null ? null : 'redacted@example.com',
            'name' => fn (mixed $value) => $value === null ? null : 'Redacted',
            'phone' => fn (mixed $value) => $value === null ? null : 'REDACTED',
        ];
    }

    protected function defineSensitiveRegexPatterns(): array
    {
        $patterns = [
            '/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/' => 'redacted@example.com',
        ];

        $subdomain = getenv('ZENDESK_SUBDOMAIN');

        if (is_string($subdomain) && $subdomain !== '' && $subdomain !== self::SUBDOMAIN) {
            $patterns['/\b'.preg_quote($subdomain, '/').'\.zendesk\.com/i'] = self::SUBDOMAIN.'.zendesk.com';
        }

        return $patterns;
    }

    protected function beforeSave(RecordedResponse $recordedResponse): RecordedResponse
    {
        $redacted = array_change_key_case($this->redactedHeaders(), CASE_LOWER);

        foreach ($recordedResponse->headers as $name => $value) {
            if (array_key_exists(strtolower((string) $name), $redacted)) {
                $recordedResponse->headers[$name] = $redacted[strtolower((string) $name)];
            }
        }

        // The regex patterns only run on the body, headers can contain the host as well.
        $patterns = $this->defineSensitiveRegexPatterns();

        array_walk_recursive($recordedResponse->headers, function (mixed &$value) use ($patterns): void {
            if (is_string($value)) {
                $value = preg_replace(array_keys($patterns), array_values($patterns), $value);
            }
        });

        return $recordedResponse;
    }
}
