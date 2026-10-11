<?php

namespace Framework\Validation;

class ValidatorChecks {

    private function _getSize(mixed $value, string $type): int|float {
        if ($type === 'numeric') {
            $float_value = toFloat($value);

            if (is_numeric($float_value)) {
                return $float_value;
            }

        } elseif ($type === 'array') {
            return count($value);
        }

        return mb_strlen($value);
    }

    private function _allowedKeys(array $arr, array $allowed_keys): bool {
        return empty(array_diff(array_keys($arr), $allowed_keys));
    }

    private function _empty(mixed $value): bool {
        return ($value === null || $value === '' || $value === []);
    }

    public function required(mixed $value): ?string {
        return ($this->_empty($value)) ? 'fail' : null;
    }

    public function nullable(mixed $value): ?string {
        return ($this->_empty($value)) ? 'skip' : null;
    }

    public function string(mixed $value): ?string {
        return (is_string($value)) ? null : 'fail';
    }

    public function numeric(mixed $value): ?string {
        return (is_string($value) && !is_null(toFloat($value))) ? null : 'fail';
    }

    public function min(mixed $value, string $type, int|float $min): ?string {
        return ($this->_getSize($value, $type) < $min) ? 'fail' : null;
    }

    public function max(mixed $value, string $type, int|float $max): ?string {
        return ($this->_getSize($value, $type) > $max) ? 'fail' : null;
    }

    public function between(mixed $value, string $type, int|float $min, int|float $max): ?string {
        $size = $this->_getSize($value, $type);

        return ($size < $min || $size > $max) ? 'fail' : null;
    }

    public function email(mixed $value): ?string {
        return (!filter_var($value, FILTER_VALIDATE_EMAIL)) ? 'fail' : null;
    }

    public function array(mixed $arr, array $keys = []): ?string {
        if (is_array($arr)) {
            if (empty($keys) || $this->_allowedKeys($arr, $keys)) {
                return null;
            }
        }
        return 'fail';
    }
}