<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRequest extends Notification
{
    use Queueable;
    public $data;
    /**
     * Create a new notification instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('📢 Nuevo Préstamo de Elementos Audiovisuales')
            ->greeting('Estimado equipo de TI,')
            ->line('Se informa que el usuario **' . ($this->data['user']['name'] ?? 'No User Retrieved') . '** ha realizado una solicitud de préstamo de los siguientes elementos el día **' . now()->format('d/m/Y H:i') . '**:')
            ->line('');

        foreach ($this->data['products'] as $product) {
            $mail->line("🔹 **{$product['unit_nombre']}** (Marca: {$product['unit_marca']}, Modelo: {$product['unit_modelo']}, Código: {$product['unit_codigo_inventario']}, Serie: {$product['unit_serie']})");
        }

        $mail->line('')
            ->line('En caso de confirmar el préstamo, se debe recordar que el prestatario debe hacer entrega de su tarjeta de identificación personal (TIP) hasta que devuelva los objetos prestados.')
            ->action('Ver Solicitud', $this->data['request_path']) // Botón para ver la solicitud específica
            // ->action('📋 Ver Lista de Solicitudes', url('http://127.0.0.1:8000/areaTI/requests/')) // Botón para ver la lista de solicitudes
            ->salutation(' '); // 🔹 Elimina "Regards, GestionDePrestamosTIUCC" mensaje por defecto que dejan las notificaciones.

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
