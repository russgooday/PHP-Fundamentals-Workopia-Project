<?php

namespace Framework\Validation;

trait ValidatorChecks {

    /** Looks up a rule and its parameters for the given attribute, or null if not present. */
    // Note: this enforcement relies on the concrete class implementing the getRule method.
    // It also shuts up the IDE from complaining about getRule being undefined.
    abstract protected function getRule(string $attrib, string $rule_to_find): ?array;

    /**
     * Returns the value's size: its numeric value if the attribute has a numeric rule,
     * otherwise its string length.
     *
     * @param string $attrib The attribute name.
     * @param string|null $value The attribute value.
     * @return int|float The size of the value.
     */
    private function getSize(string $attrib, ?string $value): int|float {
        // Do a lookup to see if the attribute has a numeric rule.
        $has_numeric = !is_null($this->getRule($attrib, 'numeric'));

        if ($has_numeric) {
            $float_value = toFloat($value);

            if (is_numeric($float_value)) {
                return $float_value;
            }
        }

        return mb_strlen($value);
    }

    /** Checks whether a value is null or an empty string. */
    private function empty(?string $value): bool {
        return ($value === null || $value === '');
    }

    /** Skips remaining rules if the value is empty. */
    public function sometimes(string $attrib, ?string $value): ?string {
        return ($this->empty($value)) ? 'skip' : null;
    }

    /** Fails if the value is empty. */
    public function required(string $attrib, ?string $value): ?string {
        return ($this->empty($value)) ? 'fail' : null;
    }

    /** Skips remaining rules if the value is empty. */
    public function nullable(string $attrib, ?string $value): ?string {
        return ($this->empty($value)) ? 'skip' : null;
    }

    /** Fails if the value isn't numeric. */
    public function numeric(string $attrib, ?string $value): ?string {
        return (is_null(toFloat($value))) ? 'fail' : null;
    }

    /** Fails if the value's size is less than the minimum. */
    public function min(string $attrib, ?string $value, int|float $min): ?string {
        return ($this->getSize($attrib, $value) < $min) ? 'fail' : null;
    }

    /** Fails if the value's size is greater than the maximum. */
    public function max(string $attrib, ?string $value, int|float $max): ?string {
        return ($this->getSize($attrib, $value) > $max) ? 'fail' : null;
    }

    /** Fails if the value's size falls outside the min/max range. */
    public function between(
        string $attrib, ?string $value, int|float $min, int|float $max
    ): ?string {
        $size = $this->getSize($attrib, $value);

        return ($size < $min || $size > $max) ? 'fail' : null;
    }

    /** Fails if the value isn't a valid email address. */
    public function email(string $attrib, ?string $value): ?string {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return 'fail';
        }
        return null;
    }
}