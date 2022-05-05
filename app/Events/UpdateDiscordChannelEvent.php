<?php

namespace App\Events;

use App\Models\DiscordGuild;

class UpdateDiscordChannelEvent extends Event
{

    public $guild;
    public $data;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(DiscordGuild $guild,$data)
    {
        $this->guild = $guild;
        $this->data = $data;
    }

    public function getGuild() {
        return $this->guild;
    }

    public function getData() {

        /*
            [
                'floor_price'   =>  '0.11',
                'nft_sold'      =>  1
            ]
        */
        return $this->data;
    }
}
