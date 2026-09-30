<?php
namespace Framework;

class Session {

    private array $new_messages = [];

    public function start() {
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

    public function get(string $key, mixed $default = null): mixed {
        if (array_key_exists($key, $_SESSION['data'])) {
            return $_SESSION['data'][$key];
        }
        return $default;
    }

    public function set(string $key, mixed $value): void {
        $_SESSION['data'][$key] = $value;
    }

    public function remove(string $key): void {
        unset($_SESSION['data'][$key]);
    }

    public function getMessage(string $key): mixed {
        return $_SESSION['messages'][$key] ?? null;
    }

    public function getMessages(): ?array {
        return $_SESSION['messages'];
    }

    public function setMessage(string $key, mixed $value): void {
        // setting next request message
        $this->new_messages[$key] = $value;
    }

    public function storeNewMessages(): void {
        // call at end of request to store new messages
        $_SESSION['messages'] = $this->new_messages;
        $this->new_messages = []; // reset new_messages
    }

    public function regenerateId(): void {
        session_regenerate_id(true);
    }

    public function destroy(): void {
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
    public function success(string $message, string $key = 'message'): void {
        $this->setMessage($key, [
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
    public function error(string $message, string $key = 'message'): void {
        $this->setMessage($key, [
            'type' => 'error',
            'message' => $message
        ]);
    }
}

/**
 * A proxy class for the Session that exposes only the allowed methods.
 * Hiding potentially destructive methods of the Session like 'destroy'.
 */
class SharedSessionProxy {
    public function __construct(private Session $session) {}

    // Expose only the allowed methods

    public function getMessage(string $key): mixed {
        return $this->session->getMessage($key);
    }

    public function getMessages(): ?array {
        return $this->session->getMessages();
    }
}