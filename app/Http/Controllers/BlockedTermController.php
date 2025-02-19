<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBlockedTermRequest;
use App\Http\Requests\UpdateBlockedTermRequest;
use App\Models\BlockedTerm;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use romanzipp\Twitch\Twitch;

class BlockedTermController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $this->authorize('moderate');

        /** @var Collection<int, \stdClass> $terms */
        $terms = Cache::remember('blocked-terms', 30, function () use ($request) {
            $terms = collect();

            /** @var User $user */
            $user = $request->user();

            $twitch = new Twitch;
            $twitch->setToken($user->twitch_access_token);

            do {
                $nextCursor = null;

                // If this is not the first iteration, get the page cursor to the next set of results
                if (isset($result)) {
                    $nextCursor = $result->next();
                }

                $options = [
                    'broadcaster_id' => config('services.twitch.channel_id'),
                    'moderator_id' => $user->twitch_id,
                    'first' => 100,
                ];

                $result = $twitch->getBlockedTerms($options, $nextCursor);

                $terms = $terms->merge($result->data());

                // Continue until there are no results left
            } while ($result->hasMoreResults());

            return $terms->filter(fn($item) => $item->expires_at === null);
        });

        $models = BlockedTerm::query()->whereIn('twitch_id', $terms->pluck('id'))->get();

        $terms = $terms->map(function ($term) use ($models) {
            $model = $models->where('twitch_id', $term->id)->first();

            return [
                'twitch_id' => $term->id,
                'term' => $term->text,
                'created_at' => $term->created_at,
                'updated_at' => $term->updated_at,
                'expires_at' => $term->expires_at,
                'comment' => $model?->comment,
            ];
        });

        return Inertia::render('BlockedTerms/Index', [
            'terms' => $terms->values(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBlockedTermRequest $request): RedirectResponse
    {
        $data = $request->validated();

        /** @var User $user */
        $user = $request->user();

        $twitch = new Twitch;
        $twitch->setToken($user->twitch_access_token);

        try {
            // throw new Exception("TEst");
            $result = $twitch->addBlockedTerm([
                'broadcaster_id' => config('services.twitch.channel_id'),
                'moderator_id' => $user->twitch_id,

            ], [
                'text' => $data['term'],
            ]);

            if ($result->getStatus() !== 200) {
                return back()->withErrors(['Something went wrong']);
            }

            $twitchData = $result->data();

            BlockedTerm::query()->create([
                'twitch_id' => $twitchData[0]->id,
                'comment' => $data['comment'],
            ]);

            Cache::forget('blocked-terms');

            return back()->with('success', 'Successfully added term to Twitch');
        } catch (\Throwable $th) {
            Log::error($th);

            return back()->withErrors(['Something went wrong']);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBlockedTermRequest $request, string $blockedTerm): RedirectResponse
    {
        $data = $request->validated();

        BlockedTerm::query()->where('twitch_id', $blockedTerm)->updateOrCreate([
            'twitch_id' => $blockedTerm,
        ], $data);

        return back()->with('success', 'Successfully updated term comment');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $blockedTerm, Request $request): RedirectResponse
    {
        Gate::authorize('moderate');

        /** @var User $user */
        $user = $request->user();

        $twitch = new Twitch;
        $twitch->setToken($user->twitch_access_token);

        try {
            $result = $twitch->removeBlockedTerm([
                'broadcaster_id' => config('services.twitch.channel_id'),
                'moderator_id' => $user->twitch_id,
                'id' => $blockedTerm,
            ]);

            if ($result->getStatus() !== 204) {
                return back()->with('error', 'Something went wrong');
            }

            BlockedTerm::query()->where('twitch_id', $blockedTerm)->delete();

            Cache::forget('blocked-terms');
        } catch (\Throwable $th) {
            Log::error($th);

            return back()->with('error', 'Something went wrong');
        }

        return back()->with('success', 'Successfully deleted term from Twitch');
    }
}
