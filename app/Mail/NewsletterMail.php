<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Models\NewsletterPost;
use App\Models\Newsletter;

class NewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * 周刊
     *
     * @var NewsletterPost
     */
    public $post;
    public $user;

    /**
     * 创建一个消息实例。
     *
     * @param  \App\Models\NewsletterPost  $post
     * @return void
     */
    public function __construct(NewsletterPost $post ,Newsletter $user )
    {
        $this->post = $post;
        $this->user = $user;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        
        $title = 'Dexduck周刊No.'.$this->post->issue_no.'已发布';

        $unsubscribe_url = config('global.site_url').'/newsletter/unsubscribe/'.urlencode($this->user->email).'/'.$this->user->getVerifyCode();
        $view_url = config('global.site_url').'/newsletter/post/'.$this->post->post_id;

        return $this->view('mail.newsletter')->subject($title)->with([
            'unsubscribe_url' => $unsubscribe_url,
            'view_url'        => $view_url
        ]);
    }
}