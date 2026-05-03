<aside class="trade-sidebar">
    <h2 class="trade-sidebar__title">その他の取引</h2>

    <div class="trade-sidebar__list">
        @forelse($otherTransactions as $otherTransaction)
            @php
                $unreadCount = $otherTransaction->unreadMessagesCountFor(Auth::id());
            @endphp

            <a href="{{ route('trades.show', $otherTransaction->id) }}" class="trade-sidebar__item">
                <span class="trade-sidebar__item-name">
                    {{ $otherTransaction->product->name ?? '商品名' }}
                </span>

                @if($unreadCount > 0)
                    <span class="trade-sidebar__badge">
                        {{ $unreadCount }}
                    </span>
                @endif
            </a>
        @empty
            <p class="trade-sidebar__empty">他の取引はありません</p>
        @endforelse
    </div>
</aside>
