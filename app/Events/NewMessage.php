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

        $message = ProjectMessage::find($message->id);
        $message->project = Project::find($message->project_id)->getDetail();
        $message->lecture = User::find($message->lecture_id)->getDetail();
        unset($message->project_id);
        unset($message->lecture_id);

        $this->message = 'Pesan berhasil terkirim.';
        $this->data = null;
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
