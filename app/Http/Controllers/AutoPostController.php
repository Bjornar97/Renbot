<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAutoPostRequest;
use App\Models\AutoPost;
use Illuminate\Http\Request;

class AutoPostController extends Controller
{
    public function store(StoreAutoPostRequest $request)
    {
        $this->authorize("create", AutoPost::class);

        $data = $request->validated();

        AutoPost::query()->create($data);

        return back()->with("success", "Queue created");
    }

    public function destroy(AutoPost $autoPost)
    {
        $this->authorize("delete", AutoPost::class);

        $autoPost->commands()->update([
            'auto_post_enabled' => false,
        ]);

        $autoPost->delete();

        return back()->with("success", "Deleted auto post queue");
    }
}
