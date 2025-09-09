<?php

declare(strict_types = 1);

namespace ride\library\tokenizer;

use ride\library\tokenizer\symbol\Symbol;

/**
 * String tokenizer.
 */
class Tokenizer
{
    /**
     * Array with tokenize symbols.
     *
     * @var array
     */
    private $symbols = [];

    /**
     * Flag to set whether the tokens will be trimmed.
     *
     * @var bool
     */
    private $willTrimTokens = false;

    /**
     * Adds a tokenize symbol to this tokenizer.
     */
    public function addSymbol(Symbol $symbol): void
    {
        $this->symbols[] = $symbol;
    }

    /**
     * Tokenizes the provided string.
     *
     * @param string $string String to tokenize
     *
     * @return array Array with the tokens of this string as value
     */
    public function tokenize(string $string): array
    {
        if ('' === $string) {
            return [];
        }

        $tokens = [];

        $toProcess = $string;
        $countToProcess = strlen($toProcess);
        $process = '';

        while (0 !== $countToProcess && strlen($process) < $countToProcess) {
            $process .= $toProcess[strlen($process)];

            foreach ($this->symbols as $symbol) {
                $previousProcess = $process;

                $symbolTokens = $symbol->tokenize($process, $toProcess);
                if (null !== $symbolTokens) {
                    foreach ($symbolTokens as $symbolToken) {
                        $tokens[] = $symbolToken;
                    }

                    $toProcess = substr($toProcess, strlen($process));
                    $process = '';

                    break;
                }
                if ($process !== $previousProcess) {
                    break;
                }
            }

            $countToProcess = strlen($toProcess);
        }

        if ('' !== $toProcess) {
            $tokens[] = $toProcess;
        }

        if ($this->willTrimTokens) {
            $tokens = $this->trimTokens($tokens);
        }

        return $tokens;
    }

    /**
     * Sets whether this tokenizer will trim the resulting tokens. Tokens which
     * are empty after trimming will be removed. Nested tokens are untouched.
     *
     * @param bool $willTrimTokens True to trim the tokens, false otherwise
     */
    public function setWillTrimTokens(bool $willTrimTokens): void
    {
        $this->willTrimTokens = $willTrimTokens;
    }

    /**
     * Gets whether this tokenizer will trim tokens. Tokens which are empty
     * after trimming will be removed. Nested tokens are untouched.
     */
    public function willTrimTokens(): bool
    {
        return $this->willTrimTokens;
    }

    /**
     * Trims the provided tokens. Tokens which are empty after trimming will be
     * removed. Nested tokens are untouched.
     */
    private function trimTokens(array $tokens): array
    {
        $newTokens = [];

        foreach ($tokens as $token) {
            if (is_array($token)) {
                $newTokens[] = $token;
                continue;
            }

            $token = trim($token);
            if ('' !== $token) {
                $newTokens[] = $token;
            }
        }

        return $newTokens;
    }
}
