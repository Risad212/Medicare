<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\GeneralSetting;
use App\Models\Service;
use App\Models\Slider;
use App\Models\Blog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StructuredDataTest extends TestCase
{
    use RefreshDatabase;

    /** T8: layout JSON-LD — Organization + WebSite from real settings. */
    public function test_layout_emits_organization_and_website_schema(): void
    {
        $this->createSettings();

        $html = $this->html(route('home'));

        $org = $this->firstGraphOfType($html, 'Organization');
        $this->assertNotNull($org);
        $this->assertSame('MediCare Hospital', $org['name']);
        $this->assertSame('12 Green Road, Dhaka 1215', $org['address']['streetAddress']);

        $website = $this->firstGraphOfType($html, 'WebSite');
        $this->assertNotNull($website);
        $this->assertSame(url('/'), $website['url']);
    }

    /** T8: without real settings nothing is invented for the schema. */
    public function test_layout_omits_organization_when_settings_missing(): void
    {
        $html = $this->html(route('about'));

        $this->assertNull($this->firstGraphOfType($html, 'Organization'));
        $this->assertNull($this->firstGraphOfType($html, 'WebSite'));
        // The breadcrumb JSON-LD on this page must still exist and decode.
        $this->assertNotNull($this->firstGraphOfType($html, 'BreadcrumbList'));
    }

    /** T8: Physician built only from populated doctor fields. */
    public function test_doctor_detail_emits_physician_schema(): void
    {
        $this->createSettings();
        $doctor = Doctor::create([
            'name' => 'Schema Doctor',
            'slug' => 'schema-doctor-'.uniqid(),
            'department' => 'Cardiology',
            'specialist' => 'Interventional Cardiology',
            'status' => 1,
        ]);

        $physician = $this->firstGraphOfType($this->html(route('doctor.show', $doctor->id)), 'Physician');

        $this->assertNotNull($physician);
        $this->assertSame('Schema Doctor', $physician['name']);
        $this->assertSame('Cardiology', $physician['medicalSpecialty']);
        $this->assertSame(route('doctor.show', $doctor->id), $physician['url']);
        $this->assertArrayNotHasKey('telephone', $physician, 'Empty phone must not be invented');
    }

    /** T8: MedicalService on service detail with provider from settings. */
    public function test_service_detail_emits_medical_service_schema(): void
    {
        $this->createSettings();
        $service = Service::create([
            'title' => 'Schema Service',
            'slug' => 'schema-service-'.uniqid(),
            'description' => "Modern diagnostic care.\nSecond paragraph about equipment.",
            'status' => 1,
        ]);

        $svc = $this->firstGraphOfType($this->html(route('service.show', $service)), 'MedicalService');

        $this->assertNotNull($svc);
        $this->assertSame('Schema Service', $svc['name']);
        $this->assertStringContainsString('Modern diagnostic care.', $svc['description']);
        $this->assertSame('Organization', $svc['provider']['@type']);
        $this->assertNotSame('', $svc['provider']['name']);
    }

    /** T8: BlogPosting with the real stored author. */
    public function test_blog_detail_emits_blog_posting_schema(): void
    {
        $this->createSettings();
        $blog = Blog::create([
            'title' => 'Schema Post',
            'slug' => 'schema-post-'.uniqid(),
            'author' => 'MediCare Team',
            'excerpt' => 'Excerpt',
            'content' => '<p>Body</p>',
            'image' => 'blogs/blog-1.jpeg',
            'status' => 1,
        ]);

        $post = $this->firstGraphOfType($this->html(route('blog.show', $blog->slug)), 'BlogPosting');

        $this->assertNotNull($post);
        $this->assertSame('Schema Post', $post['headline']);
        $this->assertSame('MediCare Team', $post['author']['name']);
        $this->assertNotEmpty($post['datePublished']);
        $this->assertNotEmpty($post['publisher']['name']);
    }

    /** T8: every breadcrumb page emits a decodable BreadcrumbList. */
    public function test_breadcrumb_pages_emit_breadcrumb_list(): void
    {
        $this->createSettings();

        foreach (['about', 'contact', 'service'] as $route) {
            $list = $this->firstGraphOfType($this->html(route($route)), 'BreadcrumbList');

            $this->assertNotNull($list, "BreadcrumbList missing on [{$route}]");
            $this->assertCount(2, $list['itemListElement']);
            $this->assertSame('Home', $list['itemListElement'][0]['name']);
        }
    }

    /** T8: every emitted block decodes and carries no empty/null values. */
    public function test_all_jsonld_blocks_decode_without_null_or_blank_values(): void
    {
        $this->createSettings();
        Slider::create(['title' => 'Health First', 'description' => 'd', 'button_text' => 'b', 'bg_image' => '']);
        $doctor = Doctor::create(['name' => 'A Doctor', 'slug' => 'a-doctor-'.uniqid(), 'status' => 1]);
        $service = Service::create(['title' => 'A Service', 'slug' => 'a-service-'.uniqid(), 'status' => 1]);
        $blog = Blog::create(['title' => 'A Post', 'slug' => 'a-post-'.uniqid(), 'status' => 1, 'content' => '<p>x</p>']);

        $pages = [
            route('home'),
            route('about'),
            route('doctor'),
            route('doctor.show', $doctor->id),
            route('service'),
            route('service.show', $service),
            route('blog'),
            route('blog.show', $blog->slug),
            route('contact'),
            route('appointment'),
        ];

        foreach ($pages as $page) {
            $blocks = $this->jsonLdBlocks($this->html($page));
            $this->assertNotEmpty($blocks, "JSON-LD expected on {$page}");

            foreach ($blocks as $block) {
                $this->assertIsArray($block);
                $this->assertNoNullOrBlankScalars($block, $page);
            }
        }
    }

    private function assertNoNullOrBlankScalars(array $node, string $page): void
    {
        $walker = function ($value, $path) use (&$walker, $page) {
            if (is_null($value)) {
                $this->fail("Null value at [{$path}] in JSON-LD on {$page}");
            }
            if (is_string($value) && trim($value) === '') {
                $this->fail("Blank string at [{$path}] in JSON-LD on {$page}");
            }
            if (is_array($value)) {
                foreach ($value as $key => $child) {
                    $walker($child, $path.'.'.$key);
                }
            }
        };

        $nodes = $node['@graph'] ?? [$node];
        foreach ($nodes as $i => $n) {
            foreach ($n as $key => $value) {
                $walker($value, "graph[$i].{$key}");
            }
        }
    }

    private function html(string $url): string
    {
        return (string) $this->get($url)->assertOk()->getContent();
    }

    /** @return array<int,array<string,mixed>> */
    private function jsonLdBlocks(string $html): array
    {
        preg_match_all('/<script type="application\/ld\+json"[^>]*>(.*?)<\/script>/s', $html, $matches);

        $blocks = [];
        foreach ($matches[1] as $payload) {
            $decoded = json_decode(trim($payload), true);
            $this->assertIsArray($decoded, 'JSON-LD payload must decode');
            $blocks[] = $decoded;
        }

        return $blocks;
    }

    /** For @graph documents: the node of the given type; flat docs: itself. */
    private function firstGraphOfType(string $html, string $type): ?array
    {
        foreach ($this->jsonLdBlocks($html) as $block) {
            foreach ($block['@graph'] ?? [$block] as $node) {
                if (($node['@type'] ?? null) === $type) {
                    return $node;
                }
            }
        }

        return null;
    }

    private function createSettings(): void
    {
        GeneralSetting::create([
            'site_name' => 'MediCare Hospital',
            'logo' => 'settings/logo.png',
            'address' => '12 Green Road, Dhaka 1215',
            'phone' => '+880 2-5815-1234',
            'email' => 'info@medicarehospital.com',
        ]);
    }
}
