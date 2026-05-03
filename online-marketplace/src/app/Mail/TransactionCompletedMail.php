<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransactionCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $transaction;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return void
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction->load([
            'product',
            'buyer.profile',
            'seller.profile',
        ]);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $productName = $this->transaction->product->name ?? '商品';

        return $this->subject('取引完了のお知らせ')
            ->view('emails.transaction-completed')
            ->with([
                'transaction' => $this->transaction,
                'productName' => $productName,
                'buyerName' => optional($this->transaction->buyer->profile)->nickname
                    ?? $this->transaction->buyer->name,
                'sellerName' => optional($this->transaction->seller->profile)->nickname
                    ?? $this->transaction->seller->name,
            ]);
    }
}
