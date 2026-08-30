<?php

/* PSR-4 Autoloader */
class Autoloader implements Stringable {
    protected array $namespaces = [];


    public function register(): self {
        spl_autoload_register([$this, 'loader']);
        return $this;
    }


    protected function loader(string $class) {
        static $root = str_replace("\\", "/", __DIR__);

        // working from right to left the classname is split on a separator into two segments.
        // The left segment is used to find a match in the namespace prefix keys,
        // and the right segment is used as a relative path when a match is found.
        while (preg_match('#(.*\\\)(.*)#', $class, $m)) {
            $class = $m[1];
            $relative = isset($relative) ? "{$m[2]}/$relative" : $m[2];

            if ($base_dirs = $this->namespaces[$class] ?? null) {

                // loop through namespace base directories
                foreach($base_dirs as $base_dir) {
                    // e.g.      {www.workopia}/{src/App/}{Config/Routes}.php
                    $filename = "{$root}/{$base_dir}{$relative}.php";

                    if ($this->requireFile($filename)) {
                        return true;
                    }
                }
            }

            $class = rtrim($class, '\\');
        }
        return false;
    }


    protected function requireFile(string $filename): bool {
        if (file_exists($filename)) {
            require $filename;
            return true;
        }
        return false;
    }


    /**
     * Adds a base directory for a namespace prefix.
     *
     * @param string $class_name The namespace prefix.
     * @param string $base_dir A base directory for class files in the namespace.
     * @param bool $prepend If true, prepends the base directory to the stack instead of appending it;
     * this causes it to be searched first rather than last.
     * @return self - chainable
     */
    public function addNamespace(string $class_name, string $base_dir, bool $prepend = false): self {
        // allow for multiple base directories per namespace
        $this->namespaces[$class_name] ??= [];

        // option to prioritise the order base directories are checked in.
        if ($prepend) {
            array_unshift($this->namespaces[$class_name], $base_dir);
        } else {
            array_push($this->namespaces[$class_name], $base_dir);
        }

        return $this;
    }

    /**
     * Returns a JSON representation of the autoloader's namespace namespaces.
     *
     * @return string - JSON representation of the autoloader's namespace namespaces.
     */
    public function __toString(): string {
        return "<pre>" . json_encode(
            $this->namespaces,
            JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT
        ) . "</pre>";
    }
}
