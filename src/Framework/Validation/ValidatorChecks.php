<?php

namespace Framework\Validation;

class ValidatorChecks {

    const PASSWORD =
    '^'
    . '(?=.*[A-Z])' // at least 1 uppercase letter
    . '(?=.*[a-z])' // at least 1 lowercase letter
    . '(?=.*[0-9])' // at least 1 number
    . '(?=.*[-@_$!%*?&])' // at least 1 symbol
    . '[-A-Za-z0-9@_$!%*?&]+'
    .'$';


    public function __construct(private Model\Validations $validations){}


    private function getSize(mixed $value, string $type): int|float {

        if (is_array($value)) {
            return count($value);
        }

        if (is_string($value)) {

            if ($type === 'numeric') {
                $float_value = toFloat($value);

                if (is_numeric($float_value)) {
                    return $float_value;
                }
            }

            return mb_strlen($value);
        }

        return 0;
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


    public function password(mixed $value): ?string {
        return (is_string($value) && preg_match("#" . self::PASSWORD . "#", $value)) ? null : 'fail';
    }


    public function min(mixed $value, Rule $rule): ?string {
        $min = $rule->values[0];

        return ($this->getSize($value, $rule->field_type) < $min) ? 'fail' : null;
    }


    public function max(mixed $value, Rule $rule): ?string {
        $max = $rule->values[0];

        return ($this->getSize($value, $rule->field_type) > $max) ? 'fail' : null;
    }


    public function between(mixed $value, Rule $rule): ?string {
        [$min, $max] = $rule->values;
        $size = $this->getSize($value, $rule->field_type);

        return ($size < $min || $size > $max) ? 'fail' : null;
    }


    public function email(mixed $value): ?string {
        return (!filter_var($value, FILTER_VALIDATE_EMAIL)) ? 'fail' : null;
    }


    public function same(mixed $value, Rule $rule): ?string {
        $data = $rule->form_data;
        $other = $rule->values[0];

        if (array_key_exists($other, $data) && $value === $data[$other]) {
            return null;
        }

        return 'fail';
    }


    public function unique(mixed $value, Rule $rule): ?string {
        $table = $rule->values[0];
        $column = $rule->values[1] ?? $rule->field_name;

        return ($this->validations->exists($table, $column, $value)) ? 'fail' : null;
    }


    public function pattern(mixed $value, Rule $rule): ?string {
        $pattern = $rule->values[0];

        return (is_string($value) && preg_match("#$pattern#", $value)) ? null : 'fail';
    }


    public function array(mixed $arr, Rule $rule): ?string {
        if (is_array($arr)) {
            if (empty($rule->values) || $this->allowedKeys($arr, $rule->values)) {
                return null;
            }
        }

        return 'fail';
    }
}