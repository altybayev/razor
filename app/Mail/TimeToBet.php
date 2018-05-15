<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TimeToBet extends Mailable
{
    use Queueable, SerializesModels;

    public $game;
    public $bet;
    public $qntt;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($game, $bet, $qntt)
    {
        $this->game = $game;
        $this->bet = $bet;
        $this->qntt = $qntt;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('noreply@razor.thevision.kz')->view('emails.bets.' . $this->game);
    }
}
