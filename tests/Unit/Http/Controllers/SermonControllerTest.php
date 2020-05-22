<?php

namespace Tests\Unit\Http\Controllers;

use Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Sermon;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SermonControllerTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test index method on SermonController.
     * @test
     * @return void
     */
    public function it_gets_all_published_sermons_sorted_desc()
    {
        $expected = [
            factory(Sermon::class)->create([
                'published_on' => date('Y-m-d H:i:s', strtotime('-7 days'))
            ]), 
            factory(Sermon::class)->create([
                'published_on' => date('Y-m-d H:i:s', strtotime('-14 days'))
            ]), 
            factory(Sermon::class)->create([
                'published_on' => date('Y-m-d H:i:s', strtotime('-21 days'))
            ])
        ];

        // Sermon that has not been published
        $hidden_sermon = factory(Sermon::class)->create([
            'published_on' => date('Y-m-d H:i:s', strtotime('+7 days'))
        ]);

        $response = $this->get('/sermons');

        $response->assertStatus(200);
        $response->assertJson(array_map(function ($item) {
            return $item->toArray();
        }, $expected));

        $response->assertJsonMissing([$hidden_sermon->toArray()]);
    }    

    /**
     * Test index method on SermonController as Admin.
     * @test
     * @return void
     */
    public function it_gets_all_sermons_sorted_desc()
    {
        $expected = [
            factory(Sermon::class)->create([
                'published_on' => date('Y-m-d H:i:s', strtotime('+7 days'))
            ]),
            factory(Sermon::class)->create([
                'published_on' => date('Y-m-d H:i:s', strtotime('-7 days'))
            ]), 
            factory(Sermon::class)->create([
                'published_on' => date('Y-m-d H:i:s', strtotime('-14 days'))
            ]), 
            factory(Sermon::class)->create([
                'published_on' => date('Y-m-d H:i:s', strtotime('-21 days'))
            ])
        ];

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get('/sermons');

        $response->assertStatus(200);
        $response->assertJson(array_map(function ($item) {
            return $item->toArray();
        }, $expected));
    }

    /**
     * Test store method on SermonController.
     * @test
     * @return void
     */
    public function it_creates_a_new_sermon_when_logged_in()
    {
        $user = factory(User::class)->create();

        Storage::fake('public');
        
        $response = $this->actingAs($user)->postJson('/sermons', [
            'title' => 'test title',
            'description' => 'test description',
            'published_on' => '',
            'video_type' => 'youtube',
            'youtube_id' => 'oHg5SJYRHA0',
            'audio_file' => (object) [
                'name' => 'test',
                'data' => 'data:audio/mpeg;base64,fdjfdsajfdsjfsdlajfksdlfjklewj',
                'extension' => 'mp3'
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'title' => 'test title',
            'description' => 'test description',
            'video_type' => 'youtube'
        ]);

        Storage::disk('public')->assertExists('sermons/test.mp3');
    }

    /**
     * Test store method on SermonController.
     * @test
     * @return void
     */
    public function it_does_not_allow_sermon_creation_logged_out()
    {
        $unauthorized_response = $this->postJson('/sermons', [
            'title' => $this->fake->word,
            'description' => $this->fake->word,
            'published_on' => '',
            'video_type' => 'youtube',
            'youtube_id' => 'oHg5SJYRHA0'
        ]);

        $unauthorized_response->assertUnauthorized();
    }

    /**
     * Test show method on SermonController.
     * @test
     * @return void
     */
    public function it_404s_sermon_that_does_not_exist()
    {
        $not_found_response = $this->get('/sermons/x');

        $not_found_response->assertNotFound();
    }

    /**
     * Test show method on SermonController.
     * @test
     * @return void
     */
    public function it_401s_sermon_that_is_not_published_for_logged_out_user()
    {
        $sermon = factory(Sermon::class)->create([
            'published_on' => date('Y-m-d H:i:s', strtotime('+7 days'))
        ]);

        $unauthorized_response = $this->get('/sermons/'.$sermon->id);

        $unauthorized_response->assertUnauthorized();
    }

    /**
     * Test show method on SermonController.
     * @test
     * @return void
     */
    public function it_returns_unpublished_sermon_for_logged_in_user()
    {
        $user = factory(User::class)->create();

        $sermon = factory(Sermon::class)->create([
            'published_on' => date('Y-m-d H:i:s', strtotime('+7 days'))
        ]);

        $success_response = $this->actingAs($user)->get('/sermons/'.$sermon->id);

        $success_response->assertStatus(200);
        $success_response->assertJson($sermon->toArray());
    }

    /**
     * Test show method on SermonController.
     * @test
     * @return void
     */
    public function it_returns_published_sermon_for_anyone()
    {
        $sermon = factory(Sermon::class)->create([
            'published_on' => date('Y-m-d H:i:s', strtotime('-7 days'))
        ]);

        $success_response = $this->get('/sermons/'.$sermon->id);

        $success_response->assertStatus(200);
        $success_response->assertJson($sermon->toArray());
    }
    
    /**
     * Test update method on SermonController.
     * @test
     * @return void
     */
    public function it_401s_update_sermon_for_logged_out_user()
    {
        $sermon = factory(Sermon::class)->create();

        $unauthorized_response = $this->putJson('/sermons/'.$sermon->id, [
            'test' => 'value'
        ]);

        $unauthorized_response->assertUnauthorized();
    }
    
    /**
     * Test update method on SermonController.
     * @test
     * @return void
     */
    public function it_404s_update_sermon_for_sermon_that_does_not_exist()
    {
        $user = factory(User::class)->create();

        $sermon = factory(Sermon::class)->create();

        $not_found_response = $this->actingAs($user)->putJson('/sermons/xxx', [
            'test' => 'value'
        ]);

        $not_found_response->assertNotFound();
    }

    /**
     * Test update method on SermonController.
     * @test
     * @return void
     */
    public function it_updates_sermon_for_logged_in_user()
    {
        Storage::fake('public');
        
        $user = factory(User::class)->create();
        $sermon = factory(Sermon::class)->create();

        $success_response1 = $this->actingAs($user)->putJson('/sermons/'.$sermon->id, [
            'title' => 'Very new title'
        ]);

        $success_response2 = $this->actingAs($user)->putJson('/sermons/'.$sermon->id, [
            'description' => 'Very new description'
        ]);

        $success_response3 = $this->actingAs($user)->putJson('/sermons/'.$sermon->id, [
            'youtube_id' => 'Very new video'
        ]);

        $success_response4 = $this->actingAs($user)->putJson('/sermons/'.$sermon->id, [
            'audio_file' => (object) [
                'name' => 'veryNewFile',
                'data' => 'data:audio/mpeg;base64,adskdsaksdkfadsf',
                'extension' => 'mp3'
            ]
        ]);

        $success_response1->assertStatus(200);
        $success_response1->assertJson(['title' => 'Very new title']);

        $success_response2->assertStatus(200);
        $success_response2->assertJson(['description' => 'Very new description']);

        $success_response3->assertStatus(200);
        $success_response3->assertJson(['video' => ['youtube_id' => 'Very new video']]);

        $success_response4->assertStatus(200);

        Storage::disk('public')->assertExists('sermons/veryNewFile.mp3');
    }
    
    /**
     * Test destroy method on SermonController.
     * @test
     * @return void
     */
    public function it_401s_delete_sermon_if_not_logged_in()
    {
        $sermon = factory(Sermon::class)->create();

        $unauthorized_response = $this->delete('/sermons/'.$sermon->id);

        $unauthorized_response->assertUnauthorized();
    }
    
    /**
     * Test destroy method on SermonController.
     * @test
     * @return void
     */
    public function it_404s_delete_sermon_if_sermon_does_not_exist()
    {
        $user = factory(User::class)->create();

        $not_found_response = $this->actingAs($user)->delete('/sermons/xxx');

        $not_found_response->assertNotFound();
    }
    
    /**
     * Test destroy method on SermonController.
     * @test
     * @return void
     */
    public function it_deletes_sermon_for_logged_in_user()
    {
        Storage::fake('public');

        $user = factory(User::class)->create();

        $sermon = factory(Sermon::class)->create();

        $id = $sermon->id;
        $file_name = $sermon->audioFile->path.'.'.$sermon->audioFile->name;

        $success_response = $this->actingAs($user)->delete('/sermons/'.$sermon->id);

        $success_response->assertStatus(200);

        Storage::disk('public')->assertMissing($file_name);

        $this->assertNull(Sermon::find($id));
    }
}
