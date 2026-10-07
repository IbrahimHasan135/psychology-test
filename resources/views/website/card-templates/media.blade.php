<article class="site-card site-card-media image-{{ $card->image_position }}">
    @if ($card->image_url)
        <img src="{{ $card->image_url }}" alt="{{ $card->title }}">
    @endif
    <div>
        <h3>{{ $card->title }}</h3>
        @if ($card->body)<p>{{ $card->body }}</p>@endif
        @if ($card->button_label && $card->button_url)
            <a class="button button-primary" href="{{ $card->button_url }}">{{ $card->button_label }}</a>
        @endif
    </div>
</article>