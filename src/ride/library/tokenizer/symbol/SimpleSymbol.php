<?php

declare(strict_types = 1);

namespace ride\library\tokenizer\symbol;

/**
 * Simple implementation of a tokenizer symbol.
 */
class SimpleSymbol extends AbstractSymbol
{
    /**
     * The symbol to tokenize on.
     *
     * @var string
     */
    protected $symbol;

    /**
     * Number of characters in the symbol.
     *
     * @var int
     */
    protected $symbolLength;

    /**
     * Length of the symbol multiplied with -1.
     *
     * @var int
     */
    protected $symbolOffset;

    /**
     * Constructs a new simple symbol.
     *
     * @param string $symbol             The symbol to tokenize on
     * @param bool   $willIncludeSymbols Flag to set whether to include the
     *                                   symbols in the tokenize result
     */
    public function __construct(string $symbol, bool $willIncludeSymbols = true)
    {
        $this->symbol = $symbol;
        $this->symbolLength = strlen($symbol);
        $this->symbolOffset = $this->symbolLength * -1;
        $this->setWillIncludeSymbols($willIncludeSymbols);
    }

    /**
     * Checks for this symbol in the string which is being tokenized.
     *
     * @param string $inProcess Current part of the string which is being
     *                          tokenized
     * @param string $toProcess Remaining part of the string which has not yet
     *                          been tokenized
     *
     * @return null|array null when the symbol was not found, an array with the
     *                    processed tokens if the symbol was found
     */
    public function tokenize(string &$inProcess, string $toProcess): ?array
    {
        $processLength = strlen($inProcess);
        if ($processLength < $this->symbolLength || substr($inProcess, $this->symbolOffset) !== $this->symbol) {
            return null;
        }

        $tokens = [];

        if ($processLength !== $this->symbolLength) {
            $tokens[] = substr($inProcess, 0, $this->symbolOffset);
        }

        if ($this->willIncludeSymbols) {
            $tokens[] = $this->symbol;
        }

        return $tokens;
    }
}
