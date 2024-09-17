<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBlockedTermRequest;
use App\Http\Requests\UpdateBlockedTermRequest;
use App\Models\BlockedTerm;
use App\Models\User;
use Illuminate\Http\Request;
use romanzipp\Twitch\Twitch;

class BlockedTermController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('moderate');

        /** @var User $user */
        $user = $request->user();

        $twitch = new Twitch();
        $twitch->setToken($user->twitch_access_token);

        $terms = collect();

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

        dd($terms);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBlockedTermRequest $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBlockedTermRequest $request, BlockedTerm $blockedTerm)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BlockedTerm $blockedTerm)
    {
        //
    }
}
