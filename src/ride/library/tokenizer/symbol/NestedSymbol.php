<?php

declare(strict_types = 1);

namespace ride\library\tokenizer\symbol;

use ride\library\tokenizer\exception\TokenizeException;
use ride\library\tokenizer\Tokenizer;

/**
 * Nested symbol for the tokenizer.
 */
class NestedSymbol extends AbstractSymbol
{
    /**
     * Tokenizer to tokenize the value between the open and close symbol.
     *
     * @var null|Tokenizer
     */
    protected $tokenizer;

    /**
     * Open symbol of the token.
     *
     * @var string
     */
    protected $symbolOpen;

    /**
     * Length of the open symbol.
     *
     * @var int
     */
    protected $symbolOpenLength;

    /**
     * Length of the open symbol multiplied with -1.
     *
     * @var int
     */
    protected $symbolOpenOffset;

    /**
     * Close symbol of the token.
     *
     * @var string
     */
    protected $symbolClose;

    /**
     * Length of the close symbol.
     *
     * @var int
     */
    protected $symbolCloseLength;

    /**
     * Flag to set whether to allow symbols before the open symbol.
     *
     * @var bool
     */
    protected $allowsSymbolsBeforeOpen;

    /**
     * Constructs a new nested tokenizer.
     *
     * @param string         $symbolOpen         Open symbol of the token
     * @param string         $symbolClose        Close symbol of the token
     * @param null|Tokenizer $tokenizer          When provided, the
     *                                           value between the open and close symbol will be tokenized using this
     *                                           tokenizer
     * @param bool           $willIncludeSymbols True to include the open and close
     *                                           symbol in the tokenize result, false otherwise
     *
     * @throws TokenizeException
     */
    public function __construct(string $symbolOpen, string $symbolClose, ?Tokenizer $tokenizer = null, bool $willIncludeSymbols = false, bool $allowsSymbolsBeforeOpen = true)
    {
        $this->setOpenSymbol($symbolOpen);
        $this->setCloseSymbol($symbolClose);
        $this->setWillIncludeSymbols($willIncludeSymbols);
        $this->setAllowsSymbolsBeforeOpen($allowsSymbolsBeforeOpen);

        $this->tokenizer = $tokenizer;
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
     *
     * @throws TokenizeException
     */
    public function tokenize(string &$inProcess, string $toProcess): ?array
    {
        $processLength = strlen($inProcess);
        if ($processLength < $this->symbolOpenLength || substr($inProcess, $this->symbolOpenOffset) !== $this->symbolOpen) {
            return null;
        }

        $positionOpen = $processLength - $this->symbolOpenLength;
        $positionClose = $this->getClosePosition($toProcess, $positionOpen);
        $lengthProcess = strlen($inProcess) + $positionOpen;

        $before = substr($inProcess, 0, $positionOpen);
        if (!$this->allowsSymbolsBeforeOpen && '' !== trim($before)) {
            return null;
        }

        $between = substr($toProcess, $positionOpen + $this->symbolOpenLength, $positionOpen + $positionClose - $lengthProcess);

        $inProcess .= $between.$this->symbolClose;

        if ('' !== $between && null !== $this->tokenizer) {
            $between = $this->tokenizer->tokenize($between);
        }

        $result = [];
        if ('' !== $before) {
            $result[] = $before;
        }
        if ($this->willIncludeSymbols) {
            $result[] = $this->symbolOpen;
            if ('' !== $between) {
                $result[] = $between;
            }
            $result[] = $this->symbolClose;
        } elseif ('' !== $between) {
            $result[] = $between;
        }

        return $result;
    }

    /**
     * Sets whether to allow symbols before the open symbol.
     */
    public function setAllowsSymbolsBeforeOpen(bool $flag): void
    {
        $this->allowsSymbolsBeforeOpen = $flag;
    }

    /**
     * Gets whether to allow symbols before the open symbol.
     */
    public function allowsSymbolsBeforeOpen(): bool
    {
        return $this->allowsSymbolsBeforeOpen;
    }

    /**
     * Gets the position of the close symbol in a string.
     *
     * @param string $string              String to look in
     * @param int    $initialOpenPosition The position of the open symbol for
     *                                    which to find the close symbol
     *
     * @return int The position of the close symbol
     *
     * @throws TokenizeException when the symbol is opened but not closed
     */
    protected function getClosePosition(string $string, int $initialOpenPosition): int
    {
        ++$initialOpenPosition;

        $closePosition = strpos($string, $this->symbolClose, $initialOpenPosition);
        if (false === $closePosition) {
            throw new TokenizeException($this->symbolOpen.' opened (at '.$initialOpenPosition.') but not closed for '.$string);
        }

        $openPosition = strpos($string, $this->symbolOpen, $initialOpenPosition);
        if (false === $openPosition || $openPosition > $closePosition || $this->symbolClose === $this->symbolOpen) {
            return $closePosition;
        }

        $openClosePosition = $this->getClosePosition($string, $openPosition);

        return $this->getClosePosition($string, $openClosePosition);
    }

    /**
     * Sets the open symbol.
     *
     * @throws TokenizeException when the provided symbol is empty or not a
     *                           string
     */
    private function setOpenSymbol(string $symbol): void
    {
        if ('' === $symbol) {
            throw new TokenizeException('Provided open symbol is empty or not a string');
        }

        $this->symbolOpen = $symbol;
        $this->symbolOpenLength = strlen($symbol);
        $this->symbolOpenOffset = $this->symbolOpenLength * -1;
    }

    /**
     * Sets the close symbol.
     *
     * @throws TokenizeException when the provided symbol is empty or not a
     *                           string
     */
    private function setCloseSymbol(string $symbol): void
    {
        if ('' === $symbol) {
            throw new TokenizeException('Provided close symbol is empty or not a string');
        }

        $this->symbolClose = $symbol;
        $this->symbolCloseLength = strlen($symbol);
    }
}
