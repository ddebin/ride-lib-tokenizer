<?php

declare(strict_types = 1);

namespace ride\library\tokenizer\symbol;

/**
 * Abstract symbol for the tokenizer.
 */
abstract class AbstractSymbol implements Symbol
{
    /**
     * Flag to set whether to include defined symbols in the tokenize result.
     *
     * @var bool
     */
    protected $willIncludeSymbols = false;

    /**
     * Sets whether to include defined symbols in the tokenize result.
     */
    public function setWillIncludeSymbols(bool $flag): void
    {
        $this->willIncludeSymbols = $flag;
    }

    /**
     * Gets whether to include defined symbols in the tokenize result.
     */
    public function willIncludeSymbols(): bool
    {
        return $this->willIncludeSymbols;
    }
}
