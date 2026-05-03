@php
    $isMine = (int) $message->sender_id === (int) $loginUserId;
    $sender = $message->sender;
@endphp

<div class="trade-message {{ $isMine ? 'trade-message--mine' : 'trade-message--partner' }}">
    <div class="trade-message__user">
        @if(!$isMine)
            <img
                src="{{ $sender->profile && $sender->profile->profile_image ? asset('storage/' . $sender->profile->profile_image) : asset('images/default-icon.png') }}"
                alt="ユーザー画像"
                class="trade-message__avatar"
            >
            <span class="trade-message__name">
                {{ $sender->profile->nickname ?? $sender->name }}
            </span>
        @else
            <span class="trade-message__name">
                {{ $sender->profile->nickname ?? $sender->name }}
            </span>
            <img
                src="{{ $sender->profile && $sender->profile->profile_image ? asset('storage/' . $sender->profile->profile_image) : asset('images/default-icon.png') }}"
                alt="ユーザー画像"
                class="trade-message__avatar"
            >
        @endif
    </div>

    <div class="trade-message__body" id="messageDisplay{{ $message->id }}">
        <p class="trade-message__text">{{ $message->body }}</p>

        @if($message->image_path)
            <div class="trade-message__image">
                <img src="{{ asset('storage/' . $message->image_path) }}" alt="投稿画像">
            </div>
        @endif

        @if($message->edited_at)
            <p class="trade-message__edited">編集済み</p>
        @endif
    </div>

    @if($isMine)
        <form
            method="POST"
            action="{{ route('trades.messages.update', [$transaction->id, $message->id]) }}"
            enctype="multipart/form-data"
            class="trade-message__edit-form is-hidden"
            id="messageEditForm{{ $message->id }}"
        >
            @csrf
            @method('PUT')

            <input
                type="text"
                name="body"
                value="{{ old('body', $message->body) }}"
                class="trade-message__edit-input"
            >

            <label class="trade-message__edit-image">
                画像を変更
                <input type="file" name="image" accept=".jpg,.jpeg,.png">
            </label>

            <button type="submit" class="trade-message__edit-submit">
                保存
            </button>
        </form>

        <div class="trade-message__actions">
            <button
                type="button"
                class="trade-message__action message-edit-trigger"
                data-message-id="{{ $message->id }}"
            >
                編集
            </button>

            <form
                method="POST"
                action="{{ route('trades.messages.destroy', [$transaction->id, $message->id]) }}"
                class="trade-message__delete-form"
            >
                @csrf
                @method('DELETE')

                <button type="submit" class="trade-message__action">
                    削除
                </button>
            </form>
        </div>
    @endif
</div>
