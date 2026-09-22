<?php
// Note: Opting for static methods instead of instance methods for session management,
// because of the need for easily accessible session management throughout the application.

namespace Framework;

class Session {

    static private array $new_messages = [];

    static public function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['data'])) {
            $_SESSION['data'] = [];
        }

        if (!isset($_SESSION['messages'])) {
            $_SESSION['messages'] = [];
        }
    }

    static public function get(string $key, mixed $default = null): mixed {
        if (array_key_exists($key, $_SESSION['data'])) {
            return $_SESSION['data'][$key];
        }
        return $default;
    }

    static public function set(string $key, mixed $value): void {
        $_SESSION['data'][$key] = $value;
    }

    static public function remove(string $key): void {
        unset($_SESSION['data'][$key]);
    }


    static public function getMessage(string $key): mixed {
        return $_SESSION['messages'][$key] ?? null;
    }

    static public function setMessage(string $key, mixed $value): void {
        // setting next request message
        self::$new_messages[$key] = $value;
    }

    static public function storeNewMessages(): void {
        // call at end of request to store new messages
        $_SESSION['messages'] = self::$new_messages;
        self::$new_messages = []; // reset new_messages
    }

    static public function regenerateId(): void {
        session_regenerate_id(true);
    }

    static public function destroy(): void {
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    // Convenience methods for setting success and error messages

    /**
     * Set a success message to be displayed on the next request.
     *
     * @param string $message The success message.
     * @param string $key The key under which the message will be stored in the session. Default is 'message'.
     */
    static public function success(string $message, string $key = 'message'): void {
        self::setMessage($key, [
            'type' => 'success',
            'message' => $message
        ]);
    }

    /**
     * Set an error message to be displayed on the next request.
     *
     * @param string $message The error message.
     * @param string $key The key under which the message will be stored in the session. Default is 'message'.
     */
    static public function error(string $message, string $key = 'message'): void {
        self::setMessage($key, [
            'type' => 'error',
            'message' => $message
        ]);
    }
}
