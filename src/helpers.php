<?php

if (!function_exists('cookie_formatter')) {
    function cookie_formatter(array $cookies): array
    {
        return array_map(function ($cookie) {
            return [
                'name' => $cookie['Name'] ?? null,
                'value' => $cookie['Value'] ?? null,
                'domain' => $cookie['Domain'] ?? null,
                'path' => $cookie['Path'] ?? '/',
                'max-age' => $cookie['Max-age'] ?? null,
                'expires' => $cookie['Expires'] ?? null,
                'secure' => $cookie['Secure'] ?? false,
                'discard' => $cookie['Discard'] ?? false,
                'httponly' => $cookie['Httponly'] ?? false,
            ];
        }, $cookies);
    }
}
