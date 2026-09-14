<?php
namespace Framework\Validation;

class Validator {
    protected array $primary_rules = ['sometimes','required','nullable','numeric'];
    protected array $size_rules = ['min', 'max', 'between'];
    protected array $errors = [];

    // Instance of ValidatorChecks to perform the actual validation logic.
    private ValidatorChecks $checks;

    /**
     * @param array $data The data to validate, keyed by attribute name.
     * @param array $rules The rules to validate each attribute against, keyed by attribute name.
     * @param array $messages The message templates used to build error messages, keyed by rule name.
     */
    public function __construct(
        private array $data,
        private array $rules,
        private array $messages
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
            if (isset($exclude[$key])) {
                continue;
            }
            $trimmed[$key] = trim($value);
        }

        return $trimmed;
    }

    /**
     * Validates the data against the rules, collecting an error for each failing attribute.
     *
     * @return bool True if every attribute passed validation, false otherwise.
     */
    public function validate(): bool {
        $data = $this->data;
        $this->errors = [];

        foreach ($this->rules as $attrib => $rules) {
            $value = $data[$attrib] ?? null;

            if ($error = $this->validateAttribute($attrib, $value, $rules)) {
                $this->errors[$attrib] = $error;
            }
        }
        return empty($this->errors);
    }

    /**
     * Runs an attribute's value through its rules in order, stopping at the first
     * skip or failure.
     *
     * @param string $attrib The attribute name.
     * @param string|null $value The attribute's value.
     * @param array $rules The rule strings to validate against, e.g. ['required', 'between:3,10'].
     * @return string|null The error message if a rule fails, otherwise null.
     * @throws \Exception If a rule has no matching check method.
     */
    private function validateAttribute(string $attrib, ?string $value, array $rules): ?string {
        $rules = $this->sortRules($rules);
        $checks = $this->checks;

        foreach ($rules as $rule) {
            [$rule, $args] = $this->parseRule($rule);

            if (!method_exists($checks, $rule)) {
                throw new \Exception("Validation rule '$rule' does not exist.");
            }

            $result = (in_array($rule, $this->size_rules))
                ? $checks->$rule($value, findOneOf(['numeric'], $rules, 'string'), ...$args)
                : $checks->$rule($value);

            if ($result === 'skip') {
                return null;
            }

            if ($result === 'fail') {
                return $this->getMessage($rule, $attrib, ...$args);
            }
        }

        return null;
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
     * Orders rules so the primary rules (e.g. 'required', 'nullable') run first,
     * in their declared order, followed by the remaining rules.
     *
     * @param array $rules The rule strings for an attribute.
     * @return array The reordered rule strings.
     */
    private function sortRules(array $rules) {
        $primary_rules = $this->primary_rules;
        $rules = array_flip($rules);
        $sorted = [];

        foreach($primary_rules as $key) {
            if (isset($rules[$key])) {
                $sorted[] = $key;
                unset($rules[$key]);
            }
        }

        // mop up
        foreach(array_keys($rules) as $key) {
            $sorted[] = $key;
        }

        return $sorted;
    }


    /**
     * Builds the error message for a failed rule, choosing the numeric or string
     * variant for size rules (min, max, between) based on whether the attribute
     * also has a numeric rule.
     *
     * @param string $rule The name of the rule that failed.
     * @param string $attrib The attribute name.
     * @param string ...$args The rule's arguments, e.g. min/max values.
     * @return string The formatted error message.
     */
    private function getMessage(string $rule, string $attrib, string ...$args): string {
        if (isset($this->messages[$rule])) {
            $message = $this->messages[$rule];

            if (in_array($rule, $this->size_rules)) {
                $size_type = findOneOf(['numeric'], $this->rules[$attrib] ?? [], 'string');
                return $message[$size_type]($attrib, ...$args);
            }
            return $message($attrib);
        }

        return $this->messages['default']($attrib);
    }


    /**
     * @return array The error messages from the last validate() call, keyed by attribute name.
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