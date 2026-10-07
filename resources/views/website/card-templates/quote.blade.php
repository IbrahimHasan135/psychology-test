<article class="site-card site-card-quote">
    @if ($card->body)<p>{{ $card->body }}</p>@endif
    <h3>{{ $card->title }}</h3>
    @if ($card->button_label && $card->button_url)
        <a class="button button-soft" href="{{ $card->button_url }}">{{ $card->button_label }}</a>
    @endif
</article>
