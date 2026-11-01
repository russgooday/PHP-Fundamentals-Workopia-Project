<?php

namespace Framework\Validation;

/**
 * @mixin \Framework\Validation\Validator
 */
trait ValidatorChecks {

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


    public function validateRequired(mixed $value): ?string {
        return ($this->is_empty($value)) ? 'fail' : null;
    }


    public function validateNullable(mixed $value): ?string {
        return ($this->is_empty($value)) ? 'skip' : null;
    }


    public function validateString(mixed $value): ?string {
        return (is_string($value)) ? null : 'fail';
    }


    public function validateNumeric(mixed $value): ?string {
        return (is_string($value) && !is_null(toFloat($value))) ? null : 'fail';
    }


    public function validateMin(mixed $value, string $type, int|float $min): ?string {
        return ($this->getSize($value, $type) < $min) ? 'fail' : null;
    }


    public function validateMax(mixed $value, string $type, int|float $max): ?string {
        return ($this->getSize($value, $type) > $max) ? 'fail' : null;
    }


    public function validateBetween(mixed $value, string $type, int|float $min, int|float $max): ?string {
        $size = $this->getSize($value, $type);

        return ($size < $min || $size > $max) ? 'fail' : null;
    }


    public function validateEmail(mixed $value): ?string {
        return (!filter_var($value, FILTER_VALIDATE_EMAIL)) ? 'fail' : null;
    }


    public function validateSame(mixed $value, string $other): ?string {
        if (array_key_exists($other, $this->data) && $value === $this->data[$other]) {
            return null;
        }

        return 'fail';
    }


    public function validateArray(mixed $arr, string ...$keys): ?string {
        if (is_array($arr)) {
            if (empty($keys) || $this->allowedKeys($arr, $keys)) {
                return null;
            }
        }

        return 'fail';
    }
}