@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
@endpush

@section('content')
@php
    $user = Auth::user();
    $averageRating = $user->average_rating ?? 0;
    $roundedRating = (int) round($averageRating);
@endphp

<div class="mypage-container">
    <div class="mypage-header">
        <img
            src="{{ $user->profile && $user->profile->profile_image ? asset('storage/' . $user->profile->profile_image) : asset('images/default-icon.png') }}"
            class="mypage-avatar"
            alt="プロフィール画像"
        >

        <div class="mypage-user-info">
            <h2 class="mypage-username">
                {{ optional($user->profile)->nickname ?? $user->name }}
            </h2>

            <div class="mypage-rating">
                @for($i = 1; $i <= 5; $i++)
                    <span class="mypage-rating__star {{ $i <= $roundedRating ? 'is-active' : '' }}">★</span>
                @endfor
            </div>
        </div>

        <a href="/mypage/edit" class="mypage-edit-btn">
            プロフィールを編集
        </a>
    </div>

    <div class="mypage-tabs">
        <a href="/mypage?tab=selling" class="mypage-tab {{ request('tab', 'selling') === 'selling' ? 'active' : '' }}">
            出品した商品
        </a>

        <a href="/mypage?tab=bought" class="mypage-tab {{ request('tab') === 'bought' ? 'active' : '' }}">
            購入した商品
        </a>

        <a href="/mypage?tab=trading" class="mypage-tab {{ request('tab') === 'trading' ? 'active' : '' }}">
            取引中の商品

            @if($tradingUnreadCount > 0)
                <span class="mypage-tab-badge">
                    {{ $tradingUnreadCount }}
                </span>
            @endif
        </a>
    </div>

    <div class="mypage-line"></div>

    @if ($page === 'sell')
        <div class="mypage-items">
            @forelse ($items as $item)
                <a href="/item/{{ $item->id }}" class="mypage-item-card">
                    <div class="mypage-item-image">
                        @if($item->image)
                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}">
                        @else
                            <span>商品画像</span>
                        @endif
                    </div>

                    <p class="mypage-item-name">{{ $item->name }}</p>
                </a>
            @empty
                <p class="mypage-empty-text">出品した商品はありません</p>
            @endforelse
        </div>
    @endif

    @if ($page === 'buy')
        <div class="mypage-items">
            @forelse ($items as $item)
                <div class="mypage-item-card mypage-item-card--sold">
                    <div class="mypage-item-image">
                        @if($item->image)
                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}">
                        @else
                            <span>商品画像</span>
                        @endif
                    </div>

                    <p class="mypage-item-name">{{ $item->name }}</p>
                </div>
            @empty
                <p class="mypage-empty-text">購入した商品はありません</p>
            @endforelse
        </div>
    @endif

    @if ($page === 'trade')
        <div class="mypage-items">
            @forelse ($transactions as $transaction)
                @php
                    $unreadCount = $transaction->unreadMessagesCountFor($user->id);
                    $product = $transaction->product;
                @endphp

                <a href="{{ route('trades.show', $transaction->id) }}" class="mypage-item-card mypage-item-card--trade">
                    @if($unreadCount > 0)
                        <span class="mypage-trade-badge">
                            {{ $unreadCount }}
                        </span>
                    @endif

                    <div class="mypage-item-image">
                        @if($product && $product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                        @else
                            <span>商品画像</span>
                        @endif
                    </div>

                    <p class="mypage-item-name">
                        {{ $product->name ?? '商品名' }}
                    </p>
                </a>
            @empty
                <p class="mypage-empty-text">取引中の商品はありません</p>
            @endforelse
        </div>
    @endif
</div>
@endsection
