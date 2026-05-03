<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    const STATUS_PENDING   = 0;
    const STATUS_COMPLETED = 1;

    protected $fillable = [
        'product_id',
        'seller_id',
        'buyer_id',
        'price',
        'payment_method',
        'shipping_address',
        'status',
        'purchased_at',
        'buyer_completed_at',
        'seller_completed_at',
        'completed_at',
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'buyer_completed_at' => 'datetime',
        'seller_completed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function messages()
    {
        return $this->hasMany(TransactionMessage::class);
    }

    public function reviews()
    {
        return $this->hasMany(TransactionReview::class);
    }

    public function buyerReview()
    {
        return $this->hasOne(TransactionReview::class, 'transaction_id')
            ->where('reviewer_id', $this->buyer_id);
    }

    public function sellerReview()
    {
        return $this->hasOne(TransactionReview::class, 'transaction_id')
            ->where('reviewer_id', $this->seller_id);
    }

    public function isBuyerCompleted()
    {
        return !is_null($this->buyer_completed_at);
    }

    public function isSellerCompleted()
    {
        return !is_null($this->seller_completed_at);
    }

    public function isCompleted()
    {
        return !is_null($this->completed_at);
    }

    public function partnerFor($userId)
    {
        if ((int) $this->buyer_id === (int) $userId) {
            return $this->seller;
        }

        return $this->buyer;
    }

    public function isParticipant($userId)
    {
        return (int) $this->buyer_id === (int) $userId
            || (int) $this->seller_id === (int) $userId;
    }

    public function unreadMessagesCountFor($userId)
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();
    }

    public function hasReviewBy($userId)
    {
        return $this->reviews()
            ->where('reviewer_id', $userId)
            ->exists();
    }
}
