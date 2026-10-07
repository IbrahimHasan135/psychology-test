<article class="site-card site-card-cta">
    <div>
        <h3>{{ $card->title }}</h3>
        @if ($card->body)<p>{{ $card->body }}</p>@endif
    </div>
    @if ($card->button_label && $card->button_url)
        <a class="button button-primary" href="{{ $card->button_url }}">{{ $card->button_label }}</a>
    @endif
</article>
