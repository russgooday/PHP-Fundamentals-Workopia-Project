<?php
namespace Framework;

interface ViewerInterface {
    public function share(string $key, mixed $value): void;
    public function render(string $view, array $data = []): ?string;
    public function renderForEach(string $view, array $data, string $name): string;
}