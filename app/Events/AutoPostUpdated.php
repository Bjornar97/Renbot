<?php

namespace App\Events;

use App\Models\AutoPost;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Laravel\Nightwatch\Facades\Nightwatch;

class AutoPostUpdated implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public AutoPost $autoPost)
    {
        Nightwatch::dontSample();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.AutoPost.'.$this->autoPost->id),
        ];
    }

    /**
     * @return array<string, \App\Models\AutoPost>
     */
    public function broadcastWith(): array
    {
        return [
            'model' => $this->autoPost,
        ];
    }

    public function broadcastAs(): string
    {
        return 'AutoPostUpdated';
    }
}
