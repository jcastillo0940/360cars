<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Recupera tu acceso a Movikaa')
            ->greeting('Hola '.$notifiable->name.',')
            ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta.')
            ->action('Restablecer contrasena', $url)
            ->line('Si no solicitaste este cambio, puedes ignorar este correo con seguridad.')
            ->line('Este enlace expirara en '.config('auth.passwords.'.config('auth.defaults.passwords').'.expire').' minutos.');
    }
}
