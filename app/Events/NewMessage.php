<?php

namespace App\Events;

use App\Models\Project;
use App\Models\ProjectMessage;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $data;

    public function __construct($message)
    {

        $this->message = 'Pesan berhasil terkirim.';
        $this->data = [
            "id" => $message->id,
            "sender" => $message->sender,
            "message" => $message->message,
            "read_at" => $message->read_at,
            "created_at" => $message->created_at,
            "updated_at" => $message->updated_at,
            "updated_at" => $message->updated_at,
            "project" => Project::find($message->project_id)->getDetail(),
            "lecture" => User::find($message->lecture_id)->getDetail(),
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            'project-message',
        ];
    }

    public function broadcastAs(): string
    {
        return 'new-message';
    }
}
