<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NewActNotification extends Notification
{
    use Queueable;

    protected $actividad;
    protected $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($actividad, $user)
    {
        $this->actividad = $actividad;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'mensaje' => $this->user->name.' creo la actividad: '.$this->actividad->nombre,
            'actividad' => $this->actividad
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'mensaje' => $this->user->name.' creo la actividad: '.$this->actividad->nombre,
            'actividad' => $this->actividad
        ]);
    }
}
