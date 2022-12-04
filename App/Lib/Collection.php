<?php

namespace App\Lib;

class Collection implements \JsonSerializable {
    protected array $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function map(callable $callback): array {
        return array_map($callback, $this->items);
    }

    public function filter(callable $callback): array {
        return array_filter($this->items, $callback);
    }

    public function toArray(): array
    {
        return $this->map(function($item) {
            return $item->toArray();
        });
    }

    public function jsonSerialize(): array
    {
        return $this->items;
    }
}