<?php

namespace App\Events;

use App\Models\Order;

class CreateOrderEvent extends Event
{

    public $order;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function getOrder() {
        return $this->order;
    }
}