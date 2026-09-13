<?php

namespace Framework;

use Framework\Validation\Validator,
    Framework\Validation\MessageLoader;

abstract class FormRequest {
    protected Validator $validator;

    protected array $rules;

    public function __construct(Request $request, MessageLoader $messages) {
        $this->validator = new Validator(
            $request->post, $this->rules, $messages->load()
        );
    }

    public function validate(): bool {
        return $this->validator->validate();
    }

    public function getErrors(): array {
        return $this->validator->getErrors();
    }

    public function validated(): array {
        return $this->validator->validated();
    }
}