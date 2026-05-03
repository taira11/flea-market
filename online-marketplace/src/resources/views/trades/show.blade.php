@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/trade.css') }}">
@endpush

@section('content')
@php
    $loginUserId = Auth::id();
    $isBuyer = (int) $transaction->buyer_id === (int) $loginUserId;
    $hasReviewed = $transaction->hasReviewBy($loginUserId);
    $showBuyerCompleteButton = $isBuyer && is_null($transaction->buyer_completed_at) && !$hasReviewed;
@endphp

<div class="trade-page">
    @include('trades.partials.sidebar', [
        'otherTransactions' => $otherTransactions
    ])

    <div class="trade-main">
        <div class="trade-header">
            <div class="trade-header__left">
                <img
                    src="{{ $partner->profile && $partner->profile->profile_image ? asset('storage/' . $partner->profile->profile_image) : asset('images/default-icon.png') }}"
                    alt="ユーザー画像"
                    class="trade-header__avatar"
                >
                <h1 class="trade-header__title">
                    「{{ $partner->profile->nickname ?? $partner->name }}」さんとの取引画面
                </h1>
            </div>

            @if($showBuyerCompleteButton)
                <button type="button" class="trade-complete-btn" id="openReviewModal">
                    取引を完了する
                </button>
            @endif
        </div>

        <div class="trade-product">
            <div class="trade-product__image">
                @if($transaction->product && $transaction->product->image)
                    <img src="{{ asset('storage/' . $transaction->product->image) }}" alt="{{ $transaction->product->name }}">
                @else
                    <span>商品画像</span>
                @endif
            </div>

            <div class="trade-product__body">
                <h2 class="trade-product__name">
                    {{ $transaction->product->name ?? '商品名' }}
                </h2>
                <p class="trade-product__price">
                    ¥{{ number_format($transaction->price) }}
                </p>
            </div>
        </div>

        @if(session('message'))
            <p class="trade-flash trade-flash--success">{{ session('message') }}</p>
        @endif

        @if(session('error'))
            <p class="trade-flash trade-flash--error">{{ session('error') }}</p>
        @endif

        @if($errors->any())
            <div class="trade-errors">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="trade-messages">
            @foreach($transaction->messages->sortBy('created_at') as $message)
                @include('trades.partials.message', [
                    'message' => $message,
                    'transaction' => $transaction,
                    'loginUserId' => $loginUserId
                ])
            @endforeach
        </div>

        <form
            method="POST"
            action="{{ route('trades.messages.store', $transaction->id) }}"
            enctype="multipart/form-data"
            class="trade-message-form"
        >
            @csrf

            <input
                type="text"
                name="body"
                class="trade-message-form__input"
                value="{{ old('body') }}"
                placeholder="取引メッセージを記入してください"
            >

            <label class="trade-message-form__image-btn">
                画像を追加
                <input type="file" name="image" id="tradeImageInput" accept=".jpg,.jpeg,.png">
            </label>

            <span class="trade-message-form__file-name" id="tradeImageName">
                選択されていません
            </span>

            <button type="submit" class="trade-message-form__submit">
                <span>送信</span>
            </button>
        </form>
    </div>
</div>

@include('trades.partials.review-modal', [
    'transaction' => $transaction,
    'shouldShowSellerReviewModal' => $shouldShowSellerReviewModal
])

<script>
document.addEventListener('DOMContentLoaded', function () {
    const openReviewModalButton = document.getElementById('openReviewModal');
    const reviewModal = document.getElementById('reviewModal');
    const reviewModalOverlay = document.getElementById('reviewModalOverlay');
    const ratingInput = document.getElementById('ratingInput');
    const stars = document.querySelectorAll('.review-star');
    const tradeImageInput = document.getElementById('tradeImageInput');
    const tradeImageName = document.getElementById('tradeImageName');

    if (tradeImageInput && tradeImageName) {
        tradeImageInput.addEventListener('change', function () {
            if (tradeImageInput.files && tradeImageInput.files.length > 0) {
                tradeImageName.textContent = tradeImageInput.files[0].name;
                tradeImageName.classList.add('is-selected');
            } else {
                tradeImageName.textContent = '選択されていません';
                tradeImageName.classList.remove('is-selected');
            }
        });
    }

    if (openReviewModalButton && reviewModal) {
        openReviewModalButton.addEventListener('click', function () {
            reviewModal.classList.add('is-open');
        });
    }

    if (reviewModalOverlay && reviewModal) {
        reviewModalOverlay.addEventListener('click', function () {
            reviewModal.classList.remove('is-open');
        });
    }

    stars.forEach(function (star) {
        star.addEventListener('click', function () {
            const value = Number(star.dataset.value);
            ratingInput.value = value;

            stars.forEach(function (targetStar) {
                const targetValue = Number(targetStar.dataset.value);

                if (targetValue <= value) {
                    targetStar.classList.add('is-active');
                } else {
                    targetStar.classList.remove('is-active');
                }
            });
        });
    });

    document.querySelectorAll('.message-edit-trigger').forEach(function (button) {
        button.addEventListener('click', function () {
            const messageId = button.dataset.messageId;
            const display = document.getElementById('messageDisplay' + messageId);
            const form = document.getElementById('messageEditForm' + messageId);

            if (display && form) {
                display.classList.toggle('is-hidden');
                form.classList.toggle('is-hidden');
            }
        });
    });
});
</script>
@endsection
