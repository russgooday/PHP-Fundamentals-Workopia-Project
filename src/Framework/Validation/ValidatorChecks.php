<?php

namespace Framework\Validation;

/**
 * @mixin \Framework\Validation\Validator
 */
class ValidatorChecks {

    private function getSize(mixed $value, string $type): int|float {
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


    private function allowedKeys(array $arr, array $allowed_keys): bool {
        return empty(array_diff(array_keys($arr), $allowed_keys));
    }



    private function is_empty(mixed $value): bool {
        return ($value === null || $value === '' || $value === []);
    }


    public function required(mixed $value): ?string {
        return ($this->is_empty($value)) ? 'fail' : null;
    }


    public function nullable(mixed $value): ?string {
        return ($this->is_empty($value)) ? 'skip' : null;
    }


    public function string(mixed $value): ?string {
        return (is_string($value)) ? null : 'fail';
    }


    public function numeric(mixed $value): ?string {
        return (is_string($value) && !is_null(toFloat($value))) ? null : 'fail';
    }


    public function min(mixed $value, string $type, int|float $min): ?string {
        return ($this->getSize($value, $type) < $min) ? 'fail' : null;
    }


    public function max(mixed $value, string $type, int|float $max): ?string {
        return ($this->getSize($value, $type) > $max) ? 'fail' : null;
    }


    public function between(mixed $value, string $type, int|float $min, int|float $max): ?string {
        $size = $this->getSize($value, $type);

        return ($size < $min || $size > $max) ? 'fail' : null;
    }


    public function email(mixed $value): ?string {
        return (!filter_var($value, FILTER_VALIDATE_EMAIL)) ? 'fail' : null;
    }


    public function same(mixed $value, string $other): ?string {
        if (array_key_exists($other, $this->data) && $value === $this->data[$other]) {
            return null;
        }

        return 'fail';
    }


    public function array(mixed $arr, string ...$keys): ?string {
        if (is_array($arr)) {
            if (empty($keys) || $this->allowedKeys($arr, $keys)) {
                return null;
            }
        }

        return 'fail';
    }
}