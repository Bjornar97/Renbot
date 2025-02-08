<?php

namespace App\Http\Controllers;

use App\Events\AutoPostUpdated;
use App\Http\Requests\StoreAutoPostRequest;
use App\Http\Requests\UpdateAutoPostRequest;
use App\Models\AutoPost;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AutoPostController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('AutoPosts/Index', [
            'autoPosts' => AutoPost::with('commands')->get(),
        ]);
    }

    public function store(StoreAutoPostRequest $request): RedirectResponse
    {
        $this->authorize('create', AutoPost::class);

        $data = $request->validated();

        AutoPost::query()->create($data);

        return back()->with('success', 'Queue created');
    }

    public function update(UpdateAutoPostRequest $request, AutoPost $autoPost): RedirectResponse
    {
        $autoPost->update($request->validated());

        AutoPostUpdated::dispatch($autoPost);

        return back()->with('success', 'Successfully updated auto post queue');
    }

    public function destroy(AutoPost $autoPost): RedirectResponse
    {
        $this->authorize('delete', $autoPost);

        $autoPost->commands()->update([
            'auto_post_enabled' => false,
        ]);

        $autoPost->delete();

        return back()->with('success', 'Deleted auto post queue');
    }
}
