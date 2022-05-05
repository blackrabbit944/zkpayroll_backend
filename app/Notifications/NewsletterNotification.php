<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

use App\Exceptions\ProgramException;
use App\Helpers\Bc;

class NewsletterNotification extends Notification implements ShouldQueue
{
    // use Queueable;
    public $msg_data = null;

    /**
     * Create the discord notification.
     *
     * @return void
     */
    public function __construct($msg_data = [])
    {   
        $this->msg_data = $msg_data;
    }


    /**
     * 任务将被发送到的连接的名称
     *
     * @var string|null
     */
    public $connection = 'database';

    /**
     * 任务将被发送到的队列的名称
     *
     * @var string|null
     */
    public $queue = 'send_message';

    /**
     * 任务被处理的延迟时间（秒）
     *
     * @var int
     */
    public $delay = 0;

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable The notifiable model.
     * @return array
     */
    public function via($notifiable)
    {

        Log::info('传入的订阅者:'.json_encode($notifiable->toArray()));
        $ret = ['mail'];
        return $ret;
    }

    /**
     * 获取通知对应的邮件.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = url('/invoice/');

        return (new MailMessage)
                    ->from('no-reply@jianda.com', '简答系统邮件')
                    ->greeting('Hello!')
                    ->line('One of your invoices has been paid!')
                    ->action('View Invoice', $url)
                    ->line('Thank you for using our application!');
    }


}
