@php
    $isOpen = $shouldShowSellerReviewModal ? 'is-open' : '';
@endphp

<div class="review-modal {{ $isOpen }}" id="reviewModal">
    <div class="review-modal__overlay" id="reviewModalOverlay"></div>

    <div class="review-modal__content">
        <form method="POST" action="{{ route('trades.complete', $transaction->id) }}">
            @csrf

            <div class="review-modal__header">
                <p class="review-modal__title">取引が完了しました。</p>
            </div>

            <div class="review-modal__body">
                <p class="review-modal__text">今回の取引相手はどうでしたか？</p>

                <div class="review-stars">
                    <button type="button" class="review-star" data-value="1">★</button>
                    <button type="button" class="review-star" data-value="2">★</button>
                    <button type="button" class="review-star" data-value="3">★</button>
                    <button type="button" class="review-star" data-value="4">★</button>
                    <button type="button" class="review-star" data-value="5">★</button>
                </div>

                <input type="hidden" name="rating" id="ratingInput" value="">
            </div>

            <div class="review-modal__footer">
                <button type="submit" class="review-modal__submit">
                    送信する
                </button>
            </div>
        </form>
    </div>
</div>
