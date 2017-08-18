<?php

namespace Glow\Auth\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as IlluminateResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends IlluminateResetPassword
{

    /**
     * Build the reset password url
     *
     * @return string
     */
    public function url() {

        return url(config('app.url') . route('password.reset', $this->token, false));
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable) {

        return (new MailMessage)
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $this->url())
            ->line('If you did not request a password reset, no further action is required.');
    }
}
