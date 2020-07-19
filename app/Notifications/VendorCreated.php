<?php

namespace App\Notifications;

use App\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class VendorCreated extends Notification
{
    use Queueable;

    public $vendor;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Vendor $vendor)
    {
        $this -> vendor =  $vendor;
    }
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */

    public function toMail($notifiable)
    {

        $subject = sprintf('%s: لقد تم انشاء حسابكم في موقع الامامي %s!', config('app.name'), 'ahmed');
        $greeting = sprintf('مرحبا %s!', $notifiable->name);

        return (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->salutation('اعرض منتجاتك براحة تامة')
            ->line('اهلاً وسهلاً بك في المتجر الجديد لقد تم تنفيذ الطلب بنجاح .')
            ->action('الذهاب لمتجرك الخاص', url('/'))
            ->line('شكرا جزيلا لتسجيلك في الموقع ');

    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
