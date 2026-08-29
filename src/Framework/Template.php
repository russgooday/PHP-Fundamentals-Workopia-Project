<?php
namespace Framework;
use App\Config\Paths;
use Throwable;

class Template {

    protected array $globalData = [];

    public function share(string $key, mixed $value): void {
        $this->globalData[$key] = $value;
    }

    private function outputBufferView(string $__viewPath, array $data = []): string {
        extract(array_merge($this->globalData, $data), EXTR_SKIP);
        unset($data);

        ob_start();

        try {
            include $__viewPath;

            return ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();
            logError("Error rendering view: {$e->getMessage()}");
            return '';
        }
    }


    public function render(string $view, array $data = []): ?string {
        $view_path = Paths::VIEWS . "/$view.view.php";

        if (file_exists($view_path)) {

            return $this->outputBufferView($view_path, $data);
        } else {

            logError("View file '{$view}' not found at: {$view_path}");
            return null;
        }
    }


    public function renderForEach(string $view, array $data, string $name): string {
        $output = '';

        foreach ($data as $item) {
            $output .= $this->render($view, [$name => $item]) ?? '';
        }

        return $output;
    }
}
