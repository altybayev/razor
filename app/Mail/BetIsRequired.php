<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class BetIsRequired extends Mailable
{
    use Queueable, SerializesModels;

    public $target;
    public $qntt;
    public $color;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($target, $qntt)
    {
        $this->target = $target;
        $this->qntt = $qntt;
        $this->color = '#000000';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        switch ($this->target) {
            case 'red':
                $this->color = '#FF0000';
                break;
            case 'grey':
                $this->color = '#CCCCCC';
                break;
            case 'black':
                $this->color = '#000000';
                break;

            default:
                # code...
                break;
        }

        return $this->from([
            'address' => 'noreply@razor.thevision.kz',
            'name' => $this->qntt . ' не было ' . $this->target 
            ])->view('emails.bets.wheel');
    }
}
