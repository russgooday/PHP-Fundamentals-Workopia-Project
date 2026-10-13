<?php

namespace Framework;

use Framework\Validation\Validator;

abstract class FormRequest {
    protected Validator $validator;

    // Array of validation rules for the form request.
    protected array $rules = [];

    // Array of custom error messages for the form request.
    protected array $messages = [];

    // Array of callbacks to override default sanitization for specific fields.
    protected array $sanitize_overrides = [];

    public function __construct(protected Request $request) {
        $this->validator = new Validator($request->post, $this->rules, $this->messages);
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

    public function getPost(): array {
        return $this->request->post;
    }

    public function validated(): array {
        return $this->validator->validated();
    }

    public function sanitized(): array {
        // TODO: currently only handles strings

        $data = $this->validated();
        $overrides = $this->sanitize_overrides;

        foreach ($data as $key => $value) {

            if (!is_string($value)) {
                continue;
            }

            $callback = $overrides[$key] ?? null;

            if (is_callable($callback)) {
                $data[$key] = $callback($value);
            } else {
                $data[$key] = stripAndNullify($value);
            }
        }

        return $data;
    }
}