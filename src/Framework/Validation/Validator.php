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

    private array $messages = [];

    // private ValidatorChecks $checks;

    /**
     * @param array $data The data to validate, keyed by field name.
     * @param array $rules The rules to validate each field against, keyed by field name.
     * @param array $custom_messages Custom error messages, to add to the default messages. Optional.
     */
    public function __construct(
        private array $data,
        private array $rules,
        private ValidatorChecks $checks,
        private array $custom_messages = []
    ) {
        $this->messages = $this->loadMessages('messages.php', $custom_messages);
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
     * @param string $field_name The field name.
     * @param mixed $value The field's value, or null if it is missing.
     * @param array $rules The rule strings, e.g. ['required', 'between:3,10'].
     * @param bool $field_missing True if the field was not present in the data.
     *
     * @return string|null The error message if a rule fails, otherwise null.
     *
     * @throws \Exception If a rule has no matching check method.
     */
    private function validateField(string $field_name, mixed $value, array $rules, bool $field_missing): ?string {

        if ($field_missing) {
            // only include 'sometimes' and 'required' checks, if given.
            $rules = array_intersect($this->missing_rules, $rules);
        }

        $field_type = $this->getType($value, $rules);

        foreach ($this->sortRules($rules) as $rule_string) {

            $rule = new Rule($rule_string, $field_name, $field_type, $this->data);

            $rule_attribute = $rule->attribute;

            if ($rule_attribute === 'sometimes') {
                // if missing skip to next field check
                if ($field_missing) return null;
                // otherwise skip to next rule
                continue;
            }

            if (!is_callable([$this->checks, $rule_attribute])) {
                throw new \Exception("Validation rule '$rule_attribute' does not exist.");
            }

            $result = $this->checks->$rule_attribute($value, $rule);

            if ($result === 'skip') {
                return null;
            }

            if ($result === 'fail') {
                return $this->getMessage($rule);
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
        $max = PHP_INT_MAX;

        // fn($a, $b) => ($rank[strtok($a, ':')] ?? $max) <=> ($rank[strtok($b, ':')] ?? $max)
        return insertSort($rules, fn($a, $b) =>
            ($rank[strtok($a, ':')] ?? $max) <=> ($rank[strtok($b, ':')] ?? $max)
        );
    }


    /**
     * Loads the validation messages from the default file and merges them with any custom messages provided.
     *
     * @param array $custom_messages Custom error messages to merge with the default messages.
     * @return array The merged array of validation messages.
     */
    private function loadMessages(string $file, array $custom_messages): array {
        $file = __DIR__ . '/' . $file;

        if (!file_exists($file)) {
            throw new \RuntimeException("Messages file not found: {$file}");
        }

        return array_replace_recursive(require $file, $custom_messages);
    }


    /**
     * Builds the error message for a failed rule.
     *
     * @param Rule $rule The rule object.
     *
     * @return string The formatted error message.
     */
    private function getMessage(Rule $rule): string {

        $message = $this->messages["{$rule->field_name}.{$rule->attribute}"]
            ?? $this->messages[$rule->attribute]
            ?? null;

        if ($message !== null) {

            if (is_array($message) && isset($message[$rule->field_type])) {
                $message = $message[$rule->field_type];
            }

            if (is_callable($message)) {
                return $message($rule->field_name, ...$rule->values);
            }
        }

        return $this->messages['default']($rule->field_name);
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