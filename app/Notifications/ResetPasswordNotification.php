<?php

namespace App\Notifications;

use App\Services\Branding\BrandingService;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email atur ulang kata sandi berbahasa Indonesia.
 *
 * Dikirim lewat User::sendPasswordResetNotification(), jadi token, masa
 * berlaku, dan penyimpanannya tetap memakai broker bawaan Laravel.
 */
class ResetPasswordNotification extends Notification
{
    public function __construct(public readonly string $token) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $nama = app(BrandingService::class)->appName();
        $berlaku = (int) config('auth.passwords.users.expire', 60);

        return (new MailMessage)
            ->subject('Atur ulang kata sandi '.$nama)
            ->greeting('Halo '.($notifiable->name ?: $notifiable->username).',')
            ->line('Kami menerima permintaan atur ulang kata sandi untuk akun Anda di '.$nama.'.')
            ->action('Atur Ulang Kata Sandi', $this->url($notifiable))
            ->line('Tautan ini berlaku '.$berlaku.' menit dan hanya bisa dipakai sekali.')
            ->line('Abaikan email ini bila Anda tidak meminta atur ulang kata sandi.')
            ->salutation('Salam, '.$nama);
    }

    private function url(object $notifiable): string
    {
        return route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }
}
