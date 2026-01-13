<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Item;
use App\Models\User;

class TransactionCompletedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $item;
    public $buyer;

    /**
     * Create a new message instance.
     */
    public function __construct(Item $item, User $buyer)
    {
        $this->item = $item;
        $this->buyer = $buyer;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('【COACHTECH】取引が完了しました')
                    ->view('emails.transaction_completed');
    }
}