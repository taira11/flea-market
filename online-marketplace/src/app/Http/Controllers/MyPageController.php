<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ProfileRequest;
use App\Models\Product;
use App\Models\Transaction;

class MyPageController extends Controller
{
    public function index(Request $request)
    {
        $user    = Auth::user();
        $tab     = $request->query('tab', 'selling');
        $keyword = $request->query('keyword');

        $items = collect();
        $transactions = collect();

        $tradingTransactions = Transaction::query()
            ->with(['product', 'messages'])
            ->withMax('messages', 'created_at')
            ->whereNull('completed_at')
            ->where(function ($query) use ($user) {
                $query->where('buyer_id', $user->id)
                    ->orWhere('seller_id', $user->id);
            })
            ->orderByDesc('messages_max_created_at')
            ->orderByDesc('purchased_at')
            ->get();

        $tradingUnreadCount = $tradingTransactions->sum(function ($transaction) use ($user) {
            return $transaction->unreadMessagesCountFor($user->id);
        });

        if ($tab === 'selling') {
            $page = 'sell';

            $items = Product::where('seller_id', $user->id)
                ->when($keyword, function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%");
                })
                ->get();
        } elseif ($tab === 'bought') {
            $page = 'buy';

            $items = Product::whereIn(
                'id',
                $user->purchases()->pluck('product_id')
            )
                ->when($keyword, function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%");
                })
                ->get();
        } elseif ($tab === 'trading') {
            $page = 'trade';

            $transactions = $tradingTransactions->filter(function ($transaction) use ($keyword) {
                if (!$keyword) {
                    return true;
                }

                return $transaction->product
                    && strpos($transaction->product->name, $keyword) !== false;
            });
        } else {
            return redirect('/mypage');
        }

        return view('mypage.index', compact(
            'page',
            'items',
            'transactions',
            'tab',
            'keyword',
            'tradingUnreadCount'
        ));
    }

    public function edit()
    {
        $user    = Auth::user();
        $profile = $user->profile ?? null;

        return view('mypage.edit', compact('user', 'profile'));
    }

    public function update(ProfileRequest $request)
    {
        $user    = Auth::user();
        $profile = $user->profile;

        if (!$profile) {
            $profile = $user->profile()->create([
                'nickname' => $request->nickname,
                'postal_code' => $request->postal_code,
                'address' => $request->address,
                'building' => $request->building,
                'profile_image' => null,
            ]);
        }

        $profile->nickname = $request->nickname;
        $profile->postal_code = $request->postal_code;
        $profile->address = $request->address;
        $profile->building = $request->building;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('profiles', 'public');
            $profile->profile_image = $path;
        }

        $profile->save();

        return redirect('/mypage')->with('message', 'プロフィールを更新しました！');
    }
}
