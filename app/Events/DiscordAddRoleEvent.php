<?php
namespace App\Events;

use App\Models\DiscordGuild;
use App\Models\DiscordUser;

class DiscordAddRoleEvent extends Event
{

    public $guild;
    public $discord_user;
    public $member_name;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(DiscordGuild $guild,DiscordUser $discord_user,$member_name)
    {
        $this->guild = $guild;
        $this->discord_user = $discord_user;
        $this->member_name = $member_name;
    }

    public function getGuild() {
        return $this->guild;
    }

    public function getDiscordUser() {
        return $this->discord_user;
    }

    public function getMemberName() {
        return $this->member_name;
    }
}
