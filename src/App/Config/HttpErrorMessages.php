<?php
namespace App\Config;

class HttpErrorMessages {

    protected array $messages = [
        400 => [
            'title' => 'Bad Request',
            'message' => 'The request could not be understood by the server due to malformed syntax.'
        ],
        403 => [
            'title' => 'Forbidden',
            'message' => 'You do not have permission to access this page.'
        ],
        404 => [
            'title' => 'Not Found',
            'message' => 'The page you are looking for does not exist.'
        ],
        500 => [
            'title' => 'Internal Server Error',
            'message' => 'An unexpected error occurred on the server.'
        ]
    ];

    /**
     * Fetch error
     *
     * @param int $status_code
     * @param ?string $custom_message
     * @return array<string,string|int>
     * Returns an array with keys 'status_code', 'title', and 'message'.
     */
    public function fetchError(
        int $status_code,
        ?string $custom_message,
        ?string $return_url
    ): array {
        $http_errors = $this->messages;

        if (!array_key_exists($status_code, $http_errors))
            $status_code = 500;

        $status_code_error = $http_errors[$status_code];

        return [
            'status_code' => $status_code,
            'title' => $status_code_error['title'],
            'message' => $custom_message ?: $status_code_error['message'],
            'return_url' => $return_url ?: '/'
        ];
    }
}