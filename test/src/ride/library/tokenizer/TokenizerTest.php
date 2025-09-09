<?php

declare(strict_types = 1);

namespace ride\library\tokenizer;

use PHPUnit\Framework\TestCase;
use ride\library\tokenizer\exception\TokenizeException;
use ride\library\tokenizer\symbol\NestedSymbol;
use ride\library\tokenizer\symbol\SimpleSymbol;

/**
 * @internal
 */
final class TokenizerTest extends TestCase
{
    /** @var Tokenizer */
    private $tokenizer;

    /**
     * @throws TokenizeException
     */
    protected function setUp(): void
    {
        $this->tokenizer = new Tokenizer();
        $this->tokenizer->setWillTrimTokens(true);
        $this->tokenizer->addSymbol(new SimpleSymbol('AND'));
        $this->tokenizer->addSymbol(new SimpleSymbol('OR'));
        $this->tokenizer->addSymbol(new NestedSymbol('(', ')', $this->tokenizer));
    }

    public function testInterpret(): void
    {
        self::assertTrue($this->tokenizer->willTrimTokens());
        self::assertSame([], $this->tokenizer->tokenize(''));

        $condition = '{field} = %2%';
        $tokens = $this->tokenizer->tokenize($condition);
        self::assertNotNull($tokens);
        self::assertCount(1, $tokens, 'result has not expected number of tokens');
        self::assertSame(['{field} = %2%'], $tokens);
    }

    public function testInterpretWithConditionOperator(): void
    {
        $condition = '{field} = %2% AND {field2} <= %1%';
        $tokens = $this->tokenizer->tokenize($condition);
        self::assertNotNull($tokens);
        self::assertCount(3, $tokens, 'result has not expected number of tokens');
        self::assertSame(['{field} = %2%', 'AND', '{field2} <= %1%'], $tokens);
    }

    public function testInterpretWithBrackets(): void
    {
        $condition = '{field} = %2% AND ({field2} <= %1% OR {field2} <= %2%)';
        $tokens = $this->tokenizer->tokenize($condition);
        self::assertNotNull($tokens);
        self::assertCount(3, $tokens, 'result has not expected number of tokens');
        self::assertSame(['{field} = %2%', 'AND', ['{field2} <= %1%', 'OR', '{field2} <= %2%']], $tokens);
    }

    public function testInterpretWithBracketsAtTheBeginning(): void
    {
        $condition = '({field2} <= %1% OR {field2} <= %2%) AND {field} = %2%';
        $tokens = $this->tokenizer->tokenize($condition);
        self::assertNotNull($tokens);
        self::assertSame([['{field2} <= %1%', 'OR', '{field2} <= %2%'], 'AND', '{field} = %2%'], $tokens);
    }

    public function testInterpretWithMultipleNestedBrackets(): void
    {
        $condition = '{field} = 5 AND (({field2} <= %1%) OR ({field2} >= %2%))';
        $tokens = $this->tokenizer->tokenize($condition);
        self::assertNotNull($tokens);
        self::assertCount(3, $tokens, 'result has not expected number of tokens');
        self::assertSame(['{field} = 5', 'AND', [['{field2} <= %1%'], 'OR', ['{field2} >= %2%']]], $tokens);
    }

    public function testUtf8(): void
    {
        $condition = '{field} = "ℕ⊆ℕ₀⊂ℤ⊂ℚ⊂ℝ⊂ℂ" AND (({field2} <= "τρομερή") OR ({field2} >= "მრავალენოვან"))';
        $tokens = $this->tokenizer->tokenize($condition);
        self::assertNotNull($tokens);
        self::assertCount(3, $tokens, 'result has not expected number of tokens');
        self::assertSame(['{field} = "ℕ⊆ℕ₀⊂ℤ⊂ℚ⊂ℝ⊂ℂ"', 'AND', [['{field2} <= "τρομερή"'], 'OR', ['{field2} >= "მრავალენოვან"']]], $tokens);
    }
}
