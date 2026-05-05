<?php

namespace Tests\Unit;

use App\Support\TextFormatter;
use PHPUnit\Framework\TestCase;

class TextFormatterTest extends TestCase
{
    public function test_it_converts_plain_urls_to_links(): void
    {
        $html = TextFormatter::linkifyUrls('Read https://example.com/docs and www.example.org.');

        $this->assertStringContainsString('<a href="https://example.com/docs" target="_blank" rel="noopener noreferrer">https://example.com/docs</a>', $html);
        $this->assertStringContainsString('<a href="https://www.example.org" target="_blank" rel="noopener noreferrer">www.example.org</a>.', $html);
    }

    public function test_it_escapes_non_url_content(): void
    {
        $html = TextFormatter::linkifyUrls('<script>alert("x")</script> https://example.com?a=1&b=2');

        $this->assertStringContainsString('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;', $html);
        $this->assertStringContainsString('href="https://example.com?a=1&amp;b=2"', $html);
    }

    public function test_it_does_not_link_truncated_excerpt_urls(): void
    {
        $html = TextFormatter::linkifyUrls('Read https://example.com/very-long...', true);

        $this->assertSame('Read https://example.com/very-long...', $html);
    }

    public function test_it_links_full_urls_with_trailing_ellipsis_as_punctuation(): void
    {
        $html = TextFormatter::linkifyUrls('Read https://example.com...');

        $this->assertSame('Read <a href="https://example.com" target="_blank" rel="noopener noreferrer">https://example.com</a>...', $html);
    }
}
