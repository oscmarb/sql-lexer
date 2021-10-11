<?php

declare(strict_types=1);

namespace Oscmarb\SQLLexer;

final class SQLSignature
{
    private static array $cache = [];

    public static function parseSql(string $sql): string
    {
        try {
            $sanetizedSql = \strtolower(ltrim($sql));

            if (true === empty($sanetizedSql)) {
                return '';
            }

            if (true === isset(self::$cache[$sanetizedSql])) {
                return self::$cache[$sanetizedSql];
            }

            $sanetizedSql = self::clearComments($sanetizedSql);

            if (true === str_starts_with($sanetizedSql, 'select')) {
                $result = self::parseSelect($sanetizedSql);
            } elseif (true === str_starts_with($sanetizedSql, 'update')) {
                $result = self::parseUpdate($sanetizedSql);
            } elseif (true === str_starts_with($sanetizedSql, 'insert')) {
                $result = self::parseInsert($sanetizedSql);
            } elseif (true === str_starts_with($sanetizedSql, 'delete')) {
                $result = self::parseDelete($sanetizedSql);
            } else {
                $result = self::parseFallback($sanetizedSql);
            }

            if (false === isset(self::$cache[$sanetizedSql])) {
                self::$cache[$sanetizedSql] = $result;
            }

            return $result;
        } catch (\Throwable) {
            return '';
        }
    }

    private static function parseSelect(string $sql): string
    {
        $table = self::parseTable($sql);

        return null === $table ? 'SELECT' : 'SELECT FROM '.$table;
    }

    private static function parseUpdate(string $sql): string
    {
        $table = self::parseTable($sql);

        return null === $table ? 'UPDATE' : 'UPDATE '.$table;
    }

    private static function parseInsert(string $sql): string
    {
        $table = self::parseTable($sql);

        return null === $table ? 'INSERT' : 'INSERT INTO '.$table;
    }

    private static function parseDelete(string $sql): string
    {
        $table = self::parseTable($sql);

        return null === $table ? 'DELETE' : 'DELETE FROM '.$table;
    }

    private static function parseFallback(string $sql): string
    {
        return \strtoupper(\explode(' ', \ltrim($sql))[0] ?? '');
    }

    private static function parseTable(string $sql): ?string
    {
        try {
            $sanetizedSql = self::clearSubQueries($sql);

            if (null === $sanetizedSql) {
                return null;
            }

            $table = Utils::pregMatchKey(
                '/(select|insert|delete|update)(.*)(from|into|ignore|only)[\s\n`\'\[\"\(]+(?<table>[^\s\n`\'\]\",\)]+)(.*)/',
                $sanetizedSql,
                'table'
            );

            return null === $table || \str_contains($table, ' ') ? null : $table;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function clearSubQueries(string $sql): ?string
    {
        $firstFrom = \strpos($sql, 'from');

        if (false === $firstFrom) {
            return $sql;
        }

        $partialSql = \substr($sql, $firstFrom + \strlen('from'));
        $partialSql = \str_replace(' ', '', $partialSql);

        return true === \str_starts_with($partialSql, '(select') ? null : $sql;
    }

    private static function clearComments(string $sql): string
    {
        $sql = self::removeComment($sql, '--', "\n");

        return self::removeComment($sql, '/*', '*/');
    }

    private static function removeComment(string $sql, string $startCommentString, string $endCommentString): string
    {
        $startPosition = \strpos($sql, $startCommentString);

        if (false === $startPosition) {
            return $sql;
        }

        $endPosition = \strpos($sql, $endCommentString, $startPosition);

        if (false === $endPosition) {
            $endPosition = \strlen($sql) - 1;
        }

        $comment = \substr($sql, $startPosition, $endPosition - $startPosition + \strlen($endCommentString));

        return \str_replace($comment, '', $sql);
    }
}