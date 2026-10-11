<?php
namespace Framework\Validation;

class Validator {
    private array $primary_rules = [
        'sometimes',
        'required',
        'nullable',
        'array',
        'string',
        'numeric'
    ];

    private array $missing_rules = ['sometimes', 'required'];

    private array $size_rules = ['min', 'max', 'between'];

    private array $errors = [];

    // Instance of ValidatorChecks to perform the actual validation logic.
    private ValidatorChecks $checks;

    /**
     * @param array $data The data to validate, keyed by field name.
     * @param array $rules The rules to validate each field against, keyed by field name.
     * @param array $messengers The message templates used to build error messages, keyed by rule name.
     */
    public function __construct(
        private array $data,
        private array $rules,
        private array $messengers
    ) {
        $this->checks = new ValidatorChecks();
        $this->data = $this->trimStrings($data);
    }


    /**
     * Trims all string values in the given data array,
     * optionally excluding certain keys.
     *
     * @param array $data The data array to trim.
     * @param array $exclude The keys to exclude from trimming.
     * @return array The trimmed data array.
     */
    public function trimStrings(array $data, array $exclude = []): array {
        $exclude = array_flip($exclude);
        $trimmed = $data;

        foreach($data as $key => $value) {
            if (is_string($value) && !isset($exclude[$key])) {
                $trimmed[$key] = trim($value);
            }
        }

        return $trimmed;
    }

    /**
     * Validates the data against the rules, collecting an error for each failing field.
     *
     * @return bool True if every field passed validation, false otherwise.
     */
    public function validate(): bool {
        $data = $this->data;
        $this->errors = [];

        foreach ($this->rules as $name => $rules) {
            $field_exists = array_key_exists($name, $this->data);
            $value = $field_exists ? $data[$name] : null;

            if ($error = $this->validateField($name, $value, $rules, !$field_exists)) {
                $this->errors[$name] = $error;
            }
        }

        return empty($this->errors);
    }


    /**
     * Runs a field's value through its rules in order, stopping at the first
     * skip or failure. If the field is missing from the data, only the
     * 'sometimes' and 'required' rules are applied.
     *
     * @param string $name The field name.
     * @param mixed $value The field's value, or null if it is missing.
     * @param array $rules The rule strings, e.g. ['required', 'between:3,10'].
     * @param bool $field_missing True if the field was not present in the data.
     *
     * @return string|null The error message if a rule fails, otherwise null.
     *
     * @throws \Exception If a rule has no matching check method.
     */
    private function validateField(string $name, mixed $value, array $rules, bool $field_missing): ?string {

        if ($field_missing) {
            // only include 'sometimes' and 'required' checks, if given.
            $rules = array_intersect($this->missing_rules, $rules);
        }

        $type = $this->getType($value, $rules);

        foreach ($this->sortRules($rules) as $rule) {
            [$rule, $args] = $this->parseRule($rule);

            if ($rule === 'sometimes') {
                // if missing skip to next field check
                if ($field_missing) return null;
                // otherwise skip to next rule
                continue;
            }

            if (!method_exists($this->checks, $rule)) {
                throw new \Exception("Validation rule '$rule' does not exist.");
            }

            // Determine if the rule is a size-related rule (e.g., 'min', 'max', 'between').
            if ($this->sizeRule($rule)) {

                $result = $this->checks->$rule($value, $type, ...$args);
            } else {

                $result = $this->checks->$rule($value, $args);
            }

            if ($result === 'skip') {
                return null;
            }

            if ($result === 'fail') {
                return $this->getMessage($rule, $name, $type, $args);
            }
        }

        return null;
    }


    /**
     * Moves primary rules to the front,
     * in $primary_rules order; the rest keep their order.
     *
     * @param array $rules The rule strings for the field.
     * @return array The reordered rule strings.
     * @example ['numeric', 'min:10', 'required'] => ['required', 'numeric', 'min:10']
     */
    private function sortRules(array $rules) {
        // flip -> ['required' => 0, 'string' => 1]
        $rank = array_flip($this->primary_rules);

        return insertSort($rules, fn($a, $b) =>
            ($rank[$a] ?? PHP_INT_MAX) <=> ($rank[$b] ?? PHP_INT_MAX)
        );
    }


    /**
     * Splits a rule string into its name and comma-separated arguments.
     *
     * @param string $rule The rule string, e.g. 'between:3,10'.
     * @return array A [name, args] pair, e.g. ['between', ['3', '10']].
     */
    private function parseRule(string $rule) {
        [$rule, $args] = array_pad(explode(':', $rule), 2, []);

        if (is_string($args)) {
            $args = explode(',', $args);
        }

        return [$rule, $args];
    }


    /**
     * Checks if a rule is a size-related rule (e.g., 'min', 'max', 'between').
     *
     * @param string $rule The name of the rule to check.
     * @return bool True if the rule is size-related, false otherwise.
     */
    private function sizeRule(string $rule): bool {
        return in_array($rule, $this->size_rules);
    }


    /**
     * Builds the error message for a failed rule, choosing the numeric or string
     * variant for size rules (min, max, between) based on whether the field
     * also has a numeric rule.
     *
     * @param string $rule The name of the rule that failed.
     * @param string $name The field name.
     * @param string $type The type of the field value
     * @param array ...$args The rule's arguments, e.g. min/max values.
     *
     * @return string The formatted error message.
     */
    private function getMessage(string $rule, string $name, string $type, array $args): string {
        $messenger = $this->messengers[$rule] ?? null;

        // size rules hold a message per data type, e.g. ['numeric' => fn, 'string' => fn]
        if ($this->sizeRule($rule) && is_array($messenger)) {
            $messenger = $messenger[$type] ?? null;
        }

        return is_callable($messenger)
            ? $messenger($name, ...$args)
            : $this->messengers['default']($name);
    }


    /**
     * Determines the type of the field's value.
     * @param mixed $value The field value
     * @param array $rules The rules for that field
     * @return string The determined type ('string', 'numeric', etc.).
     */
    private function getType(mixed $value, array $rules): string {
        $type = getType($value);

        if ($type === 'string' && !empty($rules)) {
            return in_array('numeric', $rules) ? 'numeric' : 'string';
        }

        return $type;
    }


    /**
     * @return array The error messages from the last validate() call, keyed by field name.
     */
    public function getErrors(): array {
        return $this->errors;
    }


    /**
     * @return array The subset of data whose keys have a validation rule defined.
     */
    public function validated(): array {
        return array_intersect_key($this->data, $this->rules);
    }
}