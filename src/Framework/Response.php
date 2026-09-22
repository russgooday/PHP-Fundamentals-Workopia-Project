<?php
namespace Framework;

class Response {
    protected array $headers = [];
    protected int $status_code = 200;
    protected string $body = '';

    public function redirect(string $url, string $field = 'Location' ): self {
        $this->addHeader($field, $url);
        return $this;
    }

    public function addHeader(string $field, string $value): self {
        $this->headers[$field] = $value;
        return $this;
    }

    public function setStatusCode(int $status_code): self {
        $this->status_code = $status_code;
        return $this;
    }

    public function setBody(string $body): self {
        $this->body = $body;
        return $this;
    }

    public function send(): void {
        http_response_code($this->status_code);

        // TODO: look into clearing the cache on hitting back button;

        foreach ($this->headers as $field => $value) {
            header("{$field}: {$value}");
        }

        echo $this->body;
    }
}