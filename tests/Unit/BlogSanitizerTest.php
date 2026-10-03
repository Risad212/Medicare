<?php

namespace Tests\Unit;

use App\Services\BlogSanitizer;
use PHPUnit\Framework\TestCase;

class BlogSanitizerTest extends TestCase
{
    public function test_keeps_allowed_formatting(): void
    {
        $input = '<p>Hello <b>world</b> <strong>!</strong></p><ul><li>item</li></ul>';

        $this->assertSame($input, BlogSanitizer::sanitize($input));
    }

    public function test_strips_script_and_iframe_tags(): void
    {
        $output = BlogSanitizer::sanitize('<p>hi</p><script>alert(1)</script><iframe src="x"></iframe>');

        $this->assertStringNotContainsString('<script', $output);
        $this->assertStringNotContainsString('<iframe', $output);
        $this->assertStringContainsString('<p>hi</p>', $output);
    }

    public function test_strips_event_handler_attributes(): void
    {
        $output = BlogSanitizer::sanitize('<p onclick="alert(1)">hi</p><li onerror="x()">item</li>');

        $this->assertStringNotContainsStringIgnoringCase('onclick', $output);
        $this->assertStringNotContainsStringIgnoringCase('onerror', $output);
    }

    public function test_strips_anchor_and_img_tags_leaving_text(): void
    {
        // <a href="javascript:..."> and <img onerror> must not survive (tags not allowlisted)
        $output = BlogSanitizer::sanitize('<a href="javascript:alert(1)">click</a><img src="x" onerror="alert(1)">');

        $this->assertStringNotContainsString('<a', $output);
        $this->assertStringNotContainsString('<img', $output);
        $this->assertStringContainsString('click', $output);
    }
}
