<?php
namespace App\Events;

use App\Models\DiscordLog;

class CreateDiscordLogEvent extends Event
{

    public $log;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(DiscordLog $log)
    {
        $this->log = $log;
    }

    public function getLog() {
        return $this->log;
    }

}
