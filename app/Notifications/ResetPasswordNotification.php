<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Mail\ResetPasswordMail;

class ResetPasswordNotification extends Notification
{
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = config('global.site_url') . '/user/reset_password?token='.$this->token. '&email='.$notifiable->getEmailForPasswordReset();
        return new ResetPasswordMail($url,$notifiable->email);
    }
}