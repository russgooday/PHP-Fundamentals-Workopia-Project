<?php
namespace Framework;
use Paths;

class Template {

    public function __construct(private string $base_path = Paths::VIEWS) {
    }

    private function outputBufferView(string $viewPath, array $data = []): string {
        extract($data, EXTR_SKIP);

        ob_start();

        include $viewPath;

        return ob_get_clean();
    }

    public function render(string $view, array $data): ?string {
        $viewPath = "$this->base_path/$view.view.php";

        if (file_exists($viewPath)) {

            return $this->outputBufferView($viewPath, $data) ?? null;
        } else {

            logError("View file '{$view}' not found at: {$viewPath}");
            return null;
        }
    }

    public function renderPartial(string $name, array $data = []): ?string {
        return $this->render("partials/_$name", $data);
    }

    public function renderPartials(array $names, array $data = []): string {
        $output = '';

        foreach ($names as $name) {
            $partialOutput = $this->renderPartial($name, $data);

            if ($partialOutput !== null) {
                $output .= $partialOutput;
            }
        }

        return $output;
    }
}
