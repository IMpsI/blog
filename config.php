<?php

declare(strict_types=1);

const APP_NAME = 'O Blog';

function envValue(string $name, string $default = ''): string
{
    $value = getenv($name);
    return $value === false ? $default : $value;
}

function envFlag(string $name, bool $default = false): bool
{
    return filter_var(envValue($name, $default ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
}

const DB_HOST = '127.0.0.1';
const DB_PORT = '3306';
const DB_NAME = 'meublog';
const DB_USER = 'root';
const DB_PASS = '';
