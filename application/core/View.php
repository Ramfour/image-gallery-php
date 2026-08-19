<?php

namespace App\Core;

class View
{
    public function generate(string $contentView, string $templateView, ?array $data = null): void
    {
        if (is_array($data)) {
            extract($data);
        }
        include $_SERVER['DOCUMENT_ROOT'] . '/application/views/' . $templateView;
    }
}
