<?php

function &new_messages(): array {
    static $store = [];
    return $store;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['data'])) {
    $_SESSION['data'] = [];
}

if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = [];
}


function getData(string $key, mixed $default = null): mixed {
    if (array_key_exists($key, $_SESSION['data'])) {
        return $_SESSION['data'][$key];
    }
    return $default;
}

function setData(string $key, mixed $value): void {
    $_SESSION['data'][$key] = $value;
}

function removeData(string $key): void {
    unset($_SESSION['data'][$key]);
}

function getMessage(string $key): ?string {
    return $_SESSION['messages'][$key] ?? null;
}

function setMessage(string $key, string $value): void {
    // setting next request message
    $store = &new_messages();
    $store[$key] = $value;
}

function storeNewMessages(): void {
    // call at end of request to store new messages
    $store = &new_messages();
    $_SESSION['messages'] = $store;
    $store = []; // reset the store
}

function regenerateId(): void {
    session_regenerate_id(true);
}

function destroy(): void {
    $_SESSION = [];

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}