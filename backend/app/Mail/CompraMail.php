<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Compra;
use App\Models\Evento;
use App\Models\Usuario;

class CompraMail extends Mailable {
  use Queueable, SerializesModels;

  /**
   * El evento se recibe ya resuelto y no se busca desde la compra, porque una
   * compra agrupa entradas de un único evento y quien la confirma ya lo tiene
   * cargado
   */
  public function __construct(public Usuario $usuario, public Compra $compra, public Evento $evento) {
    //
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope {
    return new Envelope(
      subject: 'Tus entradas para ' . $this->evento->nombre,
    );
  }

  /**
   * Get the message content definition.
   */
  public function content(): Content {
    return new Content(
      markdown: 'emails.compra',
    );
  }

  /**
   * Get the attachments for the message.
   *
   * @return array<int, \Illuminate\Mail\Mailables\Attachment>
   */
  public function attachments(): array {
    return [];
  }
}
