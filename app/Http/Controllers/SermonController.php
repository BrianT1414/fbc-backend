<?php

namespace App\Http\Controllers;

use Auth;
use Storage;
use App\Models\File;
use App\Models\Sermon;
Use App\Models\YoutubeVideo;
use Illuminate\Http\Request;

class SermonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::check()) {
            return Sermon::get();
        }

        return Sermon::public()->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        ini_set('memory_limit','100M');
        if (Auth::check()) {
            $file_id = null;
            if ($request->get('audio_file')) {
                $file_data = $request->get('audio_file');
                $content = explode(',', $file_data['data']);
                $name = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $file_data['name'])).'.'.$file_data['extension']; 
                
                Storage::disk('public')->put('sermons/'.$name, base64_decode($content[1]));
                $file = File::create([
                    'title' => 'Sermon Audio',
                    'driver' => 'public',
                    'path' => 'sermons/',
                    'name' => $name,
                    'type' => 'audio'
                ]);

                $file_id = $file->id;
            }

            if ($request->get('video_type') === 'youtube') {
                $youtube_video = YoutubeVideo::create([
                    'youtube_id' => $request->get('youtube_id'),
                    'user_id' => Auth::user()->id
                ]);
            }

            $sermon = Sermon::create([
                'title' => $request->get('title'),
                'description' => $request->get('description'),
                'published_on' => $request->get('published_on') ? $request->get('published_on') : date("Y-m-d H:i:s"),
                'video_type' => $request->get('video_type'),
                'video_id' => $youtube_video->id,
                'audio_file_id' => $file_id
            ]);
            return response()->json($sermon, 200);
        } else {
            return response()->json(['message' => 'unauthorized'], 401);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Sermon  $sermon
     * @return \Illuminate\Http\Response
     */
    public function show(Sermon $sermon)
    {
        if (Auth::check()) {
            return response()->json($sermon, 200);
        } else {
            return response()->json(['message' => 'unauthorized'], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Sermon  $sermon
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sermon $sermon)
    {
        if (Auth::check()) {
            $updateValues = [];
            if ($request->get('audio_file') && isset($request->get('audio_file')['data'])) {
                $file_data = $request->get('audio_file');
                $content = explode(',', $file_data['data']);
                $name = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $file_data['name'])).'.'.$file_data['extension']; 
                
                Storage::disk('public')->put('sermons/'.$name, base64_decode($content[1]));
                $file = File::create([
                    'title' => 'Sermon Audio',
                    'driver' => 'public',
                    'path' => 'sermons/',
                    'name' => $name,
                    'type' => 'audio'
                ]);

                $updateValues['audio_file_id'] = $file->id;
            }
            if ($request->get('title') && $sermon->title !== $request->get('title')) {
                $updateValues['title'] = $request->get('title');
            }
            if ($request->get('description') && $sermon->description !== $request->get('description')) {
                $updateValues['description'] = $request->get('description');
            }
            if ($request->get('published_on') && $sermon->published_on !== $request->get('published_on')) {
                $updateValues['published_on'] = $request->get('published_on');
            }

            $youtube_video = YoutubeVideo::find($sermon->video_id);
            if ($request->get('youtube_id') && $youtube_video->youtube_id !== $request->get('youtube_id')) {
                $youtube_video->youtube_id = $request->get('youtube_id');
                $youtube_video->save();
            }

            $sermon->update($updateValues);

            $sermon->fresh();
            return response()->json($sermon, 200);
        } else {
            return response()->json(['message' => 'unauthorized'], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Sermon  $sermon
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sermon $sermon)
    {
        if (Auth::check()) {
            if ($sermon->video_type === 'youtube') {
                $video = YoutubeVideo::find($sermon->video_id);
                $video->delete();
            }
            if ($sermon->audio_file_id) {
                Storage::disk($sermon->audioFile->driver)->delete($sermon->audioFile->path.'.'.$sermon->audioFile->name);
                $file = File::find($sermon->audio_file_id);
                $file->delete();
            }

            $sermon->delete();

            return response()->json(['message' => 'deleted'], 200);
        } else {
            return response()->json(['message' => 'unauthorized'], 401);
        }
    }
}
