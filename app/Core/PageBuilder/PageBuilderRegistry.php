<?php

namespace App\Core\PageBuilder;

use App\Core\Addons\AddonRegistry;
use App\Models\User;

class PageBuilderRegistry
{
    public function __construct(private readonly AddonRegistry $addons) {}

    public function templates(): array
    {
        return BlockRegistry::templates();
    }

    public function definitions(?User $user = null): array
    {
        return array_merge(BlockRegistry::definitions(), $this->addons->pageBuilderDefinitions($user));
    }

    public function defaults(string $type, ?User $user = null): array
    {
        $definition = $this->definitions($user)[$type] ?? null;
        if (! is_array($definition) || ! array_key_exists("defaults", $definition)) {
            throw new \InvalidArgumentException("Unknown page block type.");
        }

        return $definition["defaults"];
    }
}
