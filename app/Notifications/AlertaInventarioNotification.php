<?php

namespace App\Notifications;

use App\Models\Alerta;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

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
        // Primer nombre para un saludo más cercano
        $nombre = Str::before($notifiable->name, ' ');

        return (new MailMessage)
            ->success() // botón verde en lugar del negro por defecto
            ->subject('Alerta de inventario: '.$this->alerta->producto?->nombre)
            ->greeting('Hola '.$nombre.',')
            ->line($this->alerta->mensaje)
            ->action('Revisar alertas', route('alertas.index'))
            ->line('Notificación automática de StockMype.')
            ->salutation('Saludos, equipo StockMype');
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