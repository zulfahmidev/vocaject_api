<?php

namespace App\Events;

use App\Models\Proposal;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubmitProposal
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $data;

    public function __construct($proposal)
    {

        $this->message = 'Proposal berhasil diajukan.';
        $this->data = Proposal::find($proposal->id)->getDetail();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            'project',
        ];
    }

    public function broadcastAs(): string
    {
        return 'submit-proposal';
    }
}
