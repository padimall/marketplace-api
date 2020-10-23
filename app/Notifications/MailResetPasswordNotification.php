<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MailResetPasswordNotification extends Notification
{
    use Queueable;
    public $token;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
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
        return (new MailMessage)
                    ->subject('Atur Ulang Kata Sandi Akun Padimall-mu')
                    ->line('Hai '.$notifiable->name.',')
                    ->line('Kami telah menerima pengajuanmu untuk mengatur ulang kata sandi Padimall kamu')
                    ->line('Atur ulang kata sandi kamu dengan menekan tombol dibawah ini')
                    ->action('Atur Ulang Kata Sandi', 'https://padimallindonesia.com/reset-password?token='.$this->token.'&email='.$notifiable->email)
                    ->line('Salam,')
                    ->line('Tim Padimall');
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
