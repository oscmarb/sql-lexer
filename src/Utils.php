<?php

declare(strict_types=1);

namespace Oscmarb\SQLLexer;

final class Utils
{
    public static function pregMatchKey(string $pattern, string $subject, string $key): mixed
    {
        return self::pregMatchKeys($pattern, $subject, [$key])[$key];
    }

    private static function pregMatchKeys(string $pattern, string $subject, array $keys = []): array
    {
        $results = [];
        \preg_match($pattern, $subject, $results);

        foreach ($keys as $key) {
            $keyValue = $results[$key] ?? null;
            $results[$key] = '' === $keyValue ? null : $keyValue;
        }

        return $results;
    }
}