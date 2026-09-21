<?php

namespace App\Core;

class View
{
    private static string $basePath = __DIR__ . '/../../resources/views/';

    public function render(string $view, array $data = [], ?string $layout = 'layouts.app'): string
    {
        $content = $this->renderRaw($view, $data);

        if ($layout === null) {
            return $content;
        }

        $data['content'] = $content;
        return $this->renderRaw($layout, $data);
    }

    public function renderRaw(string $view, array $data = []): string
    {
        $path = self::$basePath . str_replace('.', '/', $view) . '.php';
        if (!is_file($path)) {
            throw new \RuntimeException("View not found: $view");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        include $path;
        return ob_get_clean();
    }

    public static function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }

    /** Include a partial from within a view template. */
    public static function partial(string $view, array $data = []): void
    {
        echo (new self())->renderRaw($view, $data);
    }
}
