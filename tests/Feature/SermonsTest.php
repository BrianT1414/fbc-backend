<?php

namespace Tests\Feature;

use Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Sermon;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SermonsTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test index method on SermonController.
     *
     * @return void
     */
    public function testGetAllSermons()
    {
        $expected = [
            factory(Sermon::class)->create(), 
            factory(Sermon::class)->create(), 
            factory(Sermon::class)->create()
        ];

        // Sermon that has not been published
        $hidden_sermon = factory(Sermon::class)->create([
            'published_on' => date('Y-m-d H:i:s', strtotime('+7 days'))
        ]);

        $response = $this->get('/sermons');

        $response->assertStatus(200);
        $response->assertJson([
            $expected[0]->toArray(), 
            $expected[1]->toArray(), 
            $expected[2]->toArray()
        ]);

        $response->assertJsonMissing([$hidden_sermon->toArray()]);
    }    

    /**
     * Test index method on SermonController as Admin.
     *
     * @return void
     */
    public function testGetAllSermonsAsAdmin()
    {
        $expected = [
            factory(Sermon::class)->create(), 
            factory(Sermon::class)->create(), 
            factory(Sermon::class)->create(),
            factory(Sermon::class)->create([
                'published_on' => date('Y-m-d H:i:s', strtotime('+7 days'))
            ])
        ];

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get('/sermons');

        $response->assertStatus(200);
        $response->assertJson([
            $expected[0]->toArray(), 
            $expected[1]->toArray(), 
            $expected[2]->toArray(), 
            $expected[3]->toArray()
        ]);
    }

    /**
     * Test store method on SermonController.
     *
     * @return void
     */
    public function testCreateSermon()
    {
        $user = factory(User::class)->create();

        Storage::fake('public');

        // Test unauthorized
        $unauthorized_response = $this->postJson('/sermons', [
            'title' => 'test title',
            'description' => 'test description',
            'published_on' => '',
            'video_type' => 'youtube',
            'youtube_id' => 'oHg5SJYRHA0',
            'audio_file' => (object) [
                'name' => 'test',
                'data' => 'data:audio/mpeg;base64,fdjfdsajfdsjfsdlajfksdlfjklewj',
                'extension' => '.mp3'
            ]
        ]);

        $unauthorized_response->assertUnauthorized();

        // Test successful creation
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
     * Test show method on SermonController.
     *
     * @return void
     */
    public function testGetSermon()
    {
        $user = factory(User::class)->create();

        $sermon = factory(Sermon::class)->create();

        // Test unauthorized
        $unauthorized_response = $this->get('/sermons/'.$sermon->id);

        $unauthorized_response->assertUnauthorized();

        // Test success
        $success_response = $this->actingAs($user)->get('/sermons/'.$sermon->id);

        $success_response->assertStatus(200);
        $success_response->assertJson($sermon->toArray());

        // Test not found
        $not_found_response = $this->actingAs($user)->get('/sermons/50000');

        $not_found_response->assertNotFound();
    }
    
    /**
     * Test update method on SermonController.
     *
     * @return void
     */
    public function testUpdateSermon()
    {
        $user = factory(User::class)->create();

        $sermon = factory(Sermon::class)->create();

        // Test unauthorized
        $unauthorized_response = $this->putJson('/sermons/'.$sermon->id, [
            'test' => 'value'
        ]);

        $unauthorized_response->assertUnauthorized();

        // Test not found
        $not_found_response = $this->actingAs($user)->putJson('/sermons/50000', [
            'test' => 'value'
        ]);

        $not_found_response->assertNotFound();

        // Test success
        $success_response1 = $this->actingAs($user)->putJson('/sermons/'.$sermon->id, [
            'title' => 'Very new title'
        ]);

        $success_response1->assertStatus(200);
        $success_response1->assertJson(['title' => 'Very new title']);

        // Test success
        $success_response2 = $this->actingAs($user)->putJson('/sermons/'.$sermon->id, [
            'description' => 'Very new description'
        ]);

        $success_response2->assertStatus(200);
        $success_response2->assertJson(['description' => 'Very new description']);

        // Test success
        $success_response3 = $this->actingAs($user)->putJson('/sermons/'.$sermon->id, [
            'youtube_id' => 'Very new video'
        ]);

        $success_response3->assertStatus(200);
        $success_response3->assertJson(['video' => ['youtube_id' => 'Very new video']]);

        // Test success
        $success_response4 = $this->actingAs($user)->putJson('/sermons/'.$sermon->id, [
            'audio_file' => (object) [
                'name' => 'veryNewFile',
                'data' => 'data:audio/mpeg;base64,adskdsaksdkfadsf',
                'extension' => 'mp3'
            ]
        ]);

        $success_response4->assertStatus(200);

        Storage::disk('public')->assertExists('sermons/veryNewFile.mp3');
    }
    
    /**
     * Test destroy method on SermonController.
     *
     * @return void
     */
    public function testDeleteSermon()
    {
        $user = factory(User::class)->create();

        $sermon = factory(Sermon::class)->create();

        $id = $sermon->id;
        $file_name = $sermon->audioFile->path.'.'.$sermon->audioFile->name;

        // Test unauthorized
        $unauthorized_response = $this->delete('/sermons/'.$sermon->id);

        $unauthorized_response->assertUnauthorized();

        // Test not found
        $not_found_response = $this->actingAs($user)->delete('/sermons/50000');

        $not_found_response->assertNotFound();

        // Test success
        $success_response = $this->actingAs($user)->delete('/sermons/'.$sermon->id);

        $success_response->assertStatus(200);

        Storage::disk('public')->assertMissing($file_name);

        $this->assertNull(Sermon::find($id));
    }
}
