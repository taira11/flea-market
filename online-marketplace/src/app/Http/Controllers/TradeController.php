<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionMessageRequest;
use App\Models\Transaction;
use App\Models\TransactionMessage;
use App\Models\TransactionReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Mail\TransactionCompletedMail;
use Illuminate\Support\Facades\Mail;

class TradeController extends Controller
{
    public function show(Transaction $transaction)
    {
        $userId = Auth::id();

        abort_unless($transaction->isParticipant($userId), 403);

        $transaction->load([
            'product',
            'buyer.profile',
            'seller.profile',
            'messages.sender.profile',
            'reviews',
        ]);

        $transaction->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        $otherTransactions = Transaction::query()
            ->with(['product', 'messages'])
            ->withMax('messages', 'created_at')
            ->whereNull('completed_at')
            ->where(function ($query) use ($userId) {
                $query->where('buyer_id', $userId)
                    ->orWhere('seller_id', $userId);
            })
            ->where('id', '!=', $transaction->id)
            ->orderByDesc('messages_max_created_at')
            ->orderByDesc('purchased_at')
            ->get();

        $partner = $transaction->partnerFor($userId);

        $shouldShowSellerReviewModal =
            (int) $transaction->seller_id === (int) $userId
            && !is_null($transaction->buyer_completed_at)
            && !$transaction->hasReviewBy($userId);

        return view('trades.show', compact(
            'transaction',
            'otherTransactions',
            'partner',
            'shouldShowSellerReviewModal'
        ));
    }

    public function storeMessage(TransactionMessageRequest $request, Transaction $transaction)
    {
        $userId = Auth::id();

        abort_unless($transaction->isParticipant($userId), 403);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('trade_messages', 'public');
        }

        TransactionMessage::create([
            'transaction_id' => $transaction->id,
            'sender_id' => $userId,
            'body' => $request->body,
            'image_path' => $imagePath,
        ]);

        return redirect()
            ->route('trades.show', $transaction->id)
            ->with('message', 'メッセージを送信しました');
    }

    public function updateMessage(TransactionMessageRequest $request, Transaction $transaction, TransactionMessage $message)
    {
        $userId = Auth::id();

        abort_unless($transaction->isParticipant($userId), 403);
        abort_unless((int) $message->transaction_id === (int) $transaction->id, 404);
        abort_unless((int) $message->sender_id === (int) $userId, 403);

        $imagePath = $message->image_path;

        if ($request->hasFile('image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            $imagePath = $request->file('image')->store('trade_messages', 'public');
        }

        $message->update([
            'body' => $request->body,
            'image_path' => $imagePath,
            'edited_at' => now(),
        ]);

        return redirect()
            ->route('trades.show', $transaction->id)
            ->with('message', 'メッセージを編集しました');
    }

    public function destroyMessage(Transaction $transaction, TransactionMessage $message)
    {
        $userId = Auth::id();

        abort_unless($transaction->isParticipant($userId), 403);
        abort_unless((int) $message->transaction_id === (int) $transaction->id, 404);
        abort_unless((int) $message->sender_id === (int) $userId, 403);

        if ($message->image_path) {
            Storage::disk('public')->delete($message->image_path);
        }

        $message->delete();

        return redirect()
            ->route('trades.show', $transaction->id)
            ->with('message', 'メッセージを削除しました');
    }

    public function complete(Request $request, Transaction $transaction)
    {
        $userId = Auth::id();

        abort_unless($transaction->isParticipant($userId), 403);

        $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ], [
            'rating.required' => '評価を選択してください',
            'rating.integer' => '評価を選択してください',
            'rating.min' => '評価を選択してください',
            'rating.max' => '評価を選択してください',
        ]);

        if ($transaction->hasReviewBy($userId)) {
            return redirect()
                ->route('trades.show', $transaction->id)
                ->with('error', 'この取引はすでに評価済みです');
        }

        $isBuyer = (int) $transaction->buyer_id === (int) $userId;
        $revieweeId = $isBuyer ? $transaction->seller_id : $transaction->buyer_id;

        TransactionReview::create([
            'transaction_id' => $transaction->id,
            'reviewer_id' => $userId,
            'reviewee_id' => $revieweeId,
            'rating' => $request->rating,
        ]);

        if ($isBuyer) {
            $transaction->update([
                'buyer_completed_at' => now(),
            ]);

            Mail::to($transaction->seller->email)
            ->send(new TransactionCompletedMail($transaction));
        } else {
            $transaction->update([
                'seller_completed_at' => now(),
                'completed_at' => now(),
                'status' => Transaction::STATUS_COMPLETED,
            ]);
        }

        return redirect('/')
            ->with('message', '取引が完了しました');
    }
}
