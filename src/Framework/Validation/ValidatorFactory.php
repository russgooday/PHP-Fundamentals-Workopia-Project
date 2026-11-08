<?php

namespace Framework\Validation;

class ValidatorFactory {
    public function __construct(
        private ValidatorChecks $checks
    ){}

    public function make(array $data, array $rules, array $messages = []): Validator {
        return new Validator($data, $rules, $this->checks, $messages);
    }

}