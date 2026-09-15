<?php

namespace Framework;

use Framework\Validation\Validator,
    Framework\Validation\MessageLoader;

abstract class FormRequest {
    protected Validator $validator;

    // Array of validation rules for the form request.
    protected array $rules = [];

    // Array of callbacks to override default sanitization for specific fields.
    protected array $sanitize_overrides = [];

    public function __construct(
        protected Request $request,
        protected MessageLoader $messages
    ) {
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

    public function getRequest(): Request {
        return $this->request;
    }

    public function validated(): array {
        return $this->validator->validated();
    }

    public function sanitized(): array {
        $data = $this->validated();
        $overrides = $this->sanitize_overrides;

        foreach ($data as $key => $value) {
            $callback = $overrides[$key] ?? null;

            $data[$key] = is_callable($callback)
                ? $callback($value)
                : stripAndNullify($value);
        }

        return $data;
    }
}