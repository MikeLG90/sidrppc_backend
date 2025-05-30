<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificacionMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $mensaje;
    public $link;
    public $clase;

    public function __construct($mensaje, $link, $clase)
    {
        $this->mensaje = $mensaje;
        $this->link = $link;
        $this->clase = $clase;
    }

    public function build()
    {
        return $this->subject('Notificación SIDRPPPC')
                    ->view('emails.notificacion');
    }
}
