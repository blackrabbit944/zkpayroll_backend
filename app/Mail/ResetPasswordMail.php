<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;


class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * 重置密码邮件
     *
     * @var 
     */
    public $url = null;

    public $email = null;
    /**
     * 创建一个消息实例。
     *
     * @param  string  $url
     * @return void
     */
    public function __construct($url,$email)
    {
        $this->url = $url;
        $this->email = $email;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mail.resetpassword')->subject('重置你的简答密码')->with([
            'view_url'        => $this->url,
        ])->to($this->email);
    }
}