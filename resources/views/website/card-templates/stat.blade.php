<article class="site-card site-card-stat">
    <strong>{{ $card->title }}</strong>
    @if ($card->body)<p>{{ $card->body }}</p>@endif
    @if ($card->button_label && $card->button_url)
        <a class="button button-soft" href="{{ $card->button_url }}">{{ $card->button_label }}</a>
    @endif
</article>
