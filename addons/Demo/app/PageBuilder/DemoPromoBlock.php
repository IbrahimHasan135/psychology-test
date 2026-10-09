<?php

namespace Addons\Demo\PageBuilder;

class DemoPromoBlock
{
    public function definition(): array
    {
        return [
            "type" => "demo.promo-card",
            "label" => "Demo Promo Card",
            "category" => "Demo Addon",
            "icon" => "bi-stars",
            "renderer" => "addon-card",
            "fields" => [
                ["path" => "badge", "label" => "Badge", "type" => "text"],
                ["path" => "title", "label" => "Title", "type" => "text"],
                ["path" => "text", "label" => "Text", "type" => "textarea"],
                ["path" => "buttonLabel", "label" => "Button label", "type" => "text"],
                ["path" => "buttonUrl", "label" => "Button URL", "type" => "url"],
            ],
            "defaults" => [
                "badge" => "Demo addon",
                "title" => "A card provided by an addon",
                "text" => "This section is registered by the Demo addon and rendered by the NovaBase Web Editor.",
                "buttonLabel" => "Open demo",
                "buttonUrl" => "#",
            ],
        ];
    }
}
