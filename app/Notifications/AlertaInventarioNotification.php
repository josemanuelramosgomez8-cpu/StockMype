<?php

namespace App\Notifications;

use App\Models\Alerta;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AlertaInventarioNotification extends Notification
{
    use Queueable;

    public function __construct(public Alerta $alerta) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Alerta de inventario: '.$this->alerta->producto?->nombre)
            ->greeting('Hola '.$notifiable->name)
            ->line($this->alerta->mensaje)
            ->action('Revisar alertas', route('alertas.index'))
            ->line('Notificación automática de StockMype.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'alerta_id' => $this->alerta->id,
            'tipo' => $this->alerta->tipo,
            'mensaje' => $this->alerta->mensaje,
            'producto_id' => $this->alerta->producto_id,
        ];
    }
}
