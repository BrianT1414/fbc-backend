<?php

namespace Tests\Unit\Http\Controllers;

use Storage;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ErrorControllerTest extends TestCase
{
    use RefreshDatabase;   

    /**
     * Test index method on ErrorController.
     * @test
     * @return void
     */
    public function it_gets_parsed_errors_as_admin()
    {
    }

    /**
     * Test index method on ErrorController.
     * @test
     * @return void
     */
    public function it_403s_getting_errors_if_not_admin()
    {
    }

    /**
     * Test store method on ErrorController.
     * @test
     * @return void
     */
    public function it_appends_error_to_file()
    {
        Storage::fake();

        $request = [
            'error' => $this->fake->word,
            'errorInfo' => $this->fake->sentence
        ];

        $response = $this->post('/errors', $request);

        $response->assertOk();

        Storage::assertExists('errors/js_errors.log');
    }
}
