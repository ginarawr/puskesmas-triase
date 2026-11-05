<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get('/masuk')->assertOk();
    }
}
