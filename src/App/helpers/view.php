<?php

/**
 * Escape dynamic output for safe HTML rendering.
 * @param mixed $value
 * @return string
 */
function e(mixed $value): string {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}
