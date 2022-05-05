<?php

namespace App\Events;

use App\Models\DiscordGuild;

class SendDiscordMessageEvent extends Event
{

    public $guild;
    public $message;
    public $link;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(DiscordGuild $guild , $message = '',$link = '')
    {
        $this->guild = $guild;
        $this->message = $message;
        $this->link = $link;

    }

    public function getGuild() {
        return $this->guild;
    }

    public function getMessage() {
        return $this->message;
    }

    public function getLink() {
        return $this->link;
    }
}
