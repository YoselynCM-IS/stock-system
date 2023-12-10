<?php

namespace App\Mail\movimientos;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class LibrosDay extends Mailable
{
    use Queueable, SerializesModels;

    private $movimientos;
    private $fecha;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($movimientos, $fecha)
    {
        $this->movimientos = $movimientos;
        $this->fecha = $fecha;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $nombre_archivo = $this->fecha.'-movimientos.xlsx';

        return $this->from('stock.majesticeducation@gmail.com')
            ->subject(__($this->fecha.' - Movimientos de libros'))
            ->attachData($this->movimientos, $nombre_archivo)
            ->markdown('mails.movimientos.libros')
            ->with('fecha', $this->fecha);
    }
}
