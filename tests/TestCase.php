<?php

namespace Tests;

use Faker;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $fake;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->fake = Faker\Factory::create();
    }
}
