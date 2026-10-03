<?php
namespace Framework;

use App\Config\Paths, Throwable;

class PHPViewer implements ViewerInterface {

    protected array $globalData = [];

    public function share(string $key, mixed $value): self {
        $this->globalData[$key] = $value;
        return $this;
    }

    private function _outputBufferView(string $__viewPath, array $data = []): string {
        extract(array_merge($this->globalData, $data), EXTR_SKIP);
        unset($data);
        ob_start();

        try {
            include $__viewPath;

            return ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }


    public function render(string $view, array $data = []): string {
        $path = Paths::VIEWS . "/$view.view.php";

        if (!is_file($path)) {
            throw new Exceptions\ViewNotFoundException("View '{$view}' not found.");
        }

        return $this->_outputBufferView($path, $data);
    }


    public function renderForEach(string $view, array $data, string $name): string {
        $output = '';

        foreach ($data as $item) {
            $output .= $this->render($view, [$name => $item]);
        }

        return $output;
    }
}
