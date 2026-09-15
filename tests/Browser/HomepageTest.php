<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class HomepageTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_public_homepage_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertTitleContains('Medicare')
                ->assertSee('About Us')
                ->assertSee('Doctors');
        });
    }
}
