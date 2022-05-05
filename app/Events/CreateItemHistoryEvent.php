<?php

namespace App\Events;

use App\Models\ItemHistory;

class CreateItemHistoryEvent extends Event
{

    public $item_history;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ItemHistory $item_history)
    {
        $this->item_history = $item_history;
    }

    public function getItemHistory() {
        return $this->item_history;
    }
}
