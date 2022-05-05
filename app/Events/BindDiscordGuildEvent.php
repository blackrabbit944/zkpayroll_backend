<?php

namespace App\Events;

use App\Models\DiscordGuild;

class BindDiscordGuildEvent extends Event
{

    public $guild;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(DiscordGuild $guild)
    {
        $this->guild = $guild;
    }

    public function getGuild() {
        return $this->guild;
    }
}
