<?php

namespace Framework;

use Framework\Validation\Validator,
    Framework\Validation\MessageLoader;

abstract class FormRequest {
    protected Validator $validator;

    protected array $rules = [];
    protected array $filter_overrides = [];

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

        $filters = array_merge(
            array_fill_keys(array_keys($data), FILTER_SANITIZE_SPECIAL_CHARS),
            $this->filter_overrides
        );

        return filter_var_array($data, $filters);
    }
}