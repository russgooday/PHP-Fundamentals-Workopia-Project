<?php

namespace Framework\Validation;

class ValidatorChecks {

    private function getSize(string $value, string $has_type): int|float {
        $has_numeric = ($has_type === 'numeric');

        if ($has_numeric) {
            $float_value = toFloat($value);

            if (is_numeric($float_value)) {
                return $float_value;
            }
        }

        return mb_strlen($value);
    }

    private function empty(?string $value): bool {
        return ($value === null || $value === '');
    }

    public function sometimes(?string $value): ?string {
        return ($this->empty($value)) ? 'skip' : null;
    }

    public function required(?string $value): ?string {
        return ($this->empty($value)) ? 'fail' : null;
    }

    public function nullable(?string $value): ?string {
        return ($this->empty($value)) ? 'skip' : null;
    }

    public function numeric(?string $value): ?string {
        return (is_null(toFloat($value))) ? 'fail' : null;
    }

    public function min(string $value, string $has_type, int|float $min): ?string {
        return ($this->getSize($value, $has_type) < $min) ? 'fail' : null;
    }

    public function max(string $value, string $has_type, int|float $max): ?string {
        return ($this->getSize($value, $has_type) > $max) ? 'fail' : null;
    }

    public function between(string $value, string $has_type, int|float $min, int|float $max): ?string {
        $size = $this->getSize($value, $has_type);

        return ($size < $min || $size > $max) ? 'fail' : null;
    }

    public function email(?string $value): ?string {
        return (!filter_var($value, FILTER_VALIDATE_EMAIL)) ? 'fail' : null;
    }
}