<?php

namespace App\Http\Controllers;

use App\Events\AutoPostUpdated;
use App\Http\Requests\StoreAutoPostRequest;
use App\Http\Requests\UpdateAutoPostRequest;
use App\Models\AutoPost;
use Inertia\Inertia;

class AutoPostController extends Controller
{
    public function index()
    {
        return Inertia::render("AutoPosts/Index", [
            'autoPosts' => AutoPost::with("commands")->get(),
        ]);
    }


    public function store(StoreAutoPostRequest $request)
    {
        $this->authorize("create", AutoPost::class);

        $data = $request->validated();

        AutoPost::query()->create($data);

        return back()->with("success", "Queue created");
    }

    public function update(UpdateAutoPostRequest $request, AutoPost $autoPost)
    {
        $autoPost->update($request->validated());

        AutoPostUpdated::dispatch($autoPost);

        return back()->with("success", "Successfully updated auto post queue");
    }

    public function destroy(AutoPost $autoPost)
    {
        $this->authorize("delete", $autoPost);

        $autoPost->commands()->update([
            'auto_post_enabled' => false,
        ]);

        $autoPost->delete();

        return back()->with("success", "Deleted auto post queue");
    }
}
