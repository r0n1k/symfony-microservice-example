<?php

namespace App\Services\Dictionary;

class HtmlDTO
{
    public string $html;
    public string $preview;

    public function __construct(string $html, string $preview)
    {
        $this->html = $html;
        $this->preview = $preview;
    }
}
