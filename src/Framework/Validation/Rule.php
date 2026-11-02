<?php
namespace Framework\Validation;

readonly class Rule {
    public string $attribute;
    public array $values;

    public function __construct(
        string $rule,
        public string $field_name,
        public string $field_type,
        public array $form_data = []
    ) {
        [$this->attribute, $this->values] = $this->parseRule($rule);
    }

    private function parseRule(string $rule) {
        [$attribute, $values] = array_pad(explode(':', $rule), 2, []);

        if (is_string($values)) $values = explode(',', $values);

        return [$attribute, $values];
    }
}