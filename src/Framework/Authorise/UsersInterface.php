<?php

namespace Framework\Authorise;

interface UsersInterface {
    public function findOneBy(string $column, mixed $value): object|false;
}