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
     * @return array<string,string|int>
     * Returns an array with keys 'status_code', 'title', and 'message'.
     */
    public function fetchError(int $status_code = 500): array {
        $messages = $this->messages;

        // default to 500 if the status code is not defined in the messages array
        if (!array_key_exists($status_code, $messages))
            $status_code = 500;

        return array_merge(['status_code' => $status_code], $messages[$status_code]);
    }
}