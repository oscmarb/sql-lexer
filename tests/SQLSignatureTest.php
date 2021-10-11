<?php

declare(strict_types=1);

namespace Oscmarb\SQLLexer\Tests;

use Oscmarb\SQLLexer\SQLSignature;
use PHPUnit\Framework\TestCase;

final class SQLSignatureTest extends TestCase
{
    /**
     * @test
     * @dataProvider sqlProvider
     */
    public function signature_test($query, $expected): void
    {
        $this->assertEquals($expected, SQLSignature::parseSql($query));
    }

    public function sqlProvider(): array
    {
        return [
            [
                'SELECT * FROM foo',
                'SELECT FROM foo',
            ],
            [
                'SELECT * FROM foo.bar',
                'SELECT FROM foo.bar',
            ],
            [
                '',
                '',
            ],
            [
                ' ',
                '',
            ],
            [
                'SELECT * FROM `foo.bar`',
                'SELECT FROM foo.bar',
            ],
            [
                'SELECT * FROM "foo.bar"',
                'SELECT FROM foo.bar',
            ],
            [
                'SELECT * FROM [foo.bar]',
                'SELECT FROM foo.bar',
            ],
            [
                'SELECT (x, y) FROM foo,bar,baz',
                'SELECT FROM foo',
            ],
            [
                'SELECT * FROM foo JOIN bar',
                'SELECT FROM foo',
            ],
            [
                'SELECT * FROM dollar$bill',
                'SELECT FROM dollar$bill',
            ],
            [
                'SELECT id FROM "myta\n-æøåble" WHERE id = 2323',
                'SELECT FROM myta\n-æøåble',
            ],
            [
                'SELECT * FROM foo-- abc
./*def*/bar',
                'SELECT FROM foo.bar',
            ],
            [
                'SELECT *,(SELECT COUNT(*) FROM table2 WHERE table2.field1 = table1.id) AS count FROM table1 WHERE table1.field1 = \'value\'',
                'SELECT FROM table1',
            ],
            [
                'SELECT * FROM (SELECT foo FROM bar) AS foo_bar',
                'SELECT',
            ],
            [
                'DELETE FROM foo.bar WHERE baz=1',
                'DELETE FROM foo.bar',
            ],
            [
                'UPDATE IGNORE foo.bar SET bar=1 WHERE baz=2',
                'UPDATE foo.bar',
            ],
            [
                'UPDATE ONLY foo AS bar SET baz=1',
                'UPDATE foo',
            ],
            [
                'INSERT INTO foo.bar (col) VALUES(?)',
                'INSERT INTO foo.bar',
            ],
            [
                'INSERT LOW_PRIORITY IGNORE INTO foo.bar (col) VALUES(?)',
                'INSERT INTO foo.bar',
            ],
            [
                'ALTER TABLE foo ADD ()',
                'ALTER',
            ],
            [
                'CREATE TABLE foo ...',
                'CREATE',
            ],
            [
                'DROP TABLE ...',
                'DROP',
            ],
            [
                'SAVEPOINT x_something',
                'SAVEPOINT',
            ],
            [
                'BEGIN',
                'BEGIN',
            ],
            [
                'COMMIT',
                'COMMIT',
            ],
            [
                'ROLLBACK',
                'ROLLBACK',
            ],
            [
                'SELECT * FROM (SELECT EOF',
                'SELECT',
            ],
            [
                'SELECT \'broken string FROM (SELECT * FROM ...',
                'SELECT',
            ],
            [
                'SELECT REPLACE(\'this\',\'is\',\'at\') FROM users',
                'SELECT FROM users',
            ],
            [
                'INSERT COIN TO PLAY',
                'INSERT',
            ],
            [
                'INSERT $2 INTO',
                'INSERT',
            ],
            [
                'UPDATE 99',
                'UPDATE',
            ],
            [
                'DELETE 99',
                'DELETE',
            ],
            [
                'DELETE FROM',
                'DELETE',
            ],
            [
                'SELECT * FROM _underscore_table',
                'SELECT FROM _underscore_table',
            ],
            [
                ' select * FROM (SELECT * FROM foo)',
                'SELECT',
            ],
            [
                'insert into',
                'INSERT',
            ],
            [
                '           
              select * FROM
                foo',
                'SELECT FROM foo',
            ],
        ];
    }
}