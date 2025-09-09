<?php

declare(strict_types = 1);

namespace ride\library\tokenizer\symbol;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class SimpleSymbolTest extends TestCase
{
    /**
     * @dataProvider provideTokenizeCases
     */
    public function testTokenize(?array $expected, string $process, string $toProcess, bool $willIncludeSymbols): void
    {
        $symbol = new SimpleSymbol('AND', $willIncludeSymbols);

        $result = $symbol->tokenize($process, $toProcess);

        self::assertSame($expected, $result);
    }

    public static function provideTokenizeCases(): iterable
    {
        return [
            [['test', 'AND'], 'testAND', 'testANDtest', true],
            [null, 'test', 'testANDtest', true],
            [['AND'], 'AND', 'ANDtest', true],
            [['test'], 'testAND', 'testANDtest', false],
            [null, 'test', 'testANDtest', false],
            [[], 'AND', 'ANDtest', false],
        ];
    }
}
