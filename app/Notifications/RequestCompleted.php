<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestCompleted extends Notification
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
            ->subject('📢 Préstamo de Elementos Audiovisuales Completado')
            ->greeting('Estimado usuario,')
            ->line('Se le informa que el prestamo de los siguientes elementos ha sido completado exitosamente.');

        foreach ($this->data['products'] as $product) {
            $mail->line("🔹 **{$product['unit_nombre']}** (Marca: {$product['unit_marca']}, Modelo: {$product['unit_modelo']}, Código: {$product['unit_codigo_inventario']}, Serie: {$product['unit_serie']})");
        }

        $mail->line('')
            ->action('Ver Solicitud', $this->data['request_path']) // Botón para ver la solicitud específica
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
