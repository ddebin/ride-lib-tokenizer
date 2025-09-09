<?php

declare(strict_types = 1);

namespace ride\library\tokenizer\symbol;

use PHPUnit\Framework\TestCase;
use ride\library\tokenizer\exception\TokenizeException;

/**
 * @internal
 */
final class NestedSymbolTest extends TestCase
{
    /**
     * @dataProvider provideTokenizeCases
     *
     * @throws TokenizeException
     */
    public function testTokenize(?array $expected, string $expectedProcess, string $process, string $toProcess, bool $allowsSymbolsBeforeOpen = true, string $open = '(', string $close = ')'): void
    {
        $symbol = new NestedSymbol($open, $close, null, false, $allowsSymbolsBeforeOpen);

        $result = $symbol->tokenize($process, $toProcess);

        self::assertSame($expected, $result);
        self::assertSame($expectedProcess, $process);
    }

    public static function provideTokenizeCases(): iterable
    {
        return [
            [null, 'test', 'test', 'test and test'],
            [['yes ', 'test and test'], 'yes (test and test)', 'yes (', 'yes (test and test)'],
            [['yes ', 'test (and test)'], 'yes (test (and test))', 'yes (', 'yes (test (and test))'],
            [null, 'yes (', 'yes (', 'yes (test (and test))', false],
            [['yes ', 'test and test'], 'yes "test and test"', 'yes "', 'yes "test and test" and "test"', true, '"', '"'],
        ];
    }

    public function testConstructorOnEmptyOpenSymbol(): void
    {
        $this->expectException(TokenizeException::class);
        $this->expectExceptionMessage('Provided open symbol is empty or not a string');
        new NestedSymbol('', ']', null, true, true);
    }

    public function testConstructorOnEmptyCloseSymbol(): void
    {
        $this->expectException(TokenizeException::class);
        $this->expectExceptionMessage('Provided close symbol is empty or not a string');
        new NestedSymbol('[', '', null, true, true);
    }

    public function testAllowsSymbolsBeforeOpen(): void
    {
        $symbol = new NestedSymbol('[', ']', null, true, true);

        self::assertTrue($symbol->allowsSymbolsBeforeOpen());
    }

    /**
     * @dataProvider provideTokenizeWithIncludeSymbolsCases
     *
     * @throws TokenizeException
     */
    public function testTokenizeWithIncludeSymbols(?array $expected, string $expectedProcess, string $process, string $toProcess, bool $allowsSymbolsBeforeOpen = true, string $open = '(', string $close = ')'): void
    {
        $symbol = new NestedSymbol($open, $close, null, true, $allowsSymbolsBeforeOpen);

        $result = $symbol->tokenize($process, $toProcess);

        self::assertSame($expected, $result);
        self::assertSame($expectedProcess, $process);
    }

    public static function provideTokenizeWithIncludeSymbolsCases(): iterable
    {
        return [
            [null, 'test', 'test', 'test and test'],
            [['yes ', '(', 'test and test', ')'], 'yes (test and test)', 'yes (', 'yes (test and test)'],
            [['yes ', '(', 'test (and test)', ')'], 'yes (test (and test))', 'yes (', 'yes (test (and test))'],
            [null, 'yes (', 'yes (', 'yes (test (and test))', false],
            [['yes ', '"', 'test and test', '"'], 'yes "test and test"', 'yes "', 'yes "test and test" and "test"', true, '"', '"'],
        ];
    }
}
