<?php

namespace App\Events;

use App\Models\Item;

class CreateItemEvent extends Event
{

    public $item;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    public function getItem() {
        return $this->item;
    }
}
