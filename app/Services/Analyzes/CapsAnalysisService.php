<?php

namespace App\Services\Analyzes;

class CapsAnalysisService
{
    public const TOTAL_CAPS_THRESHOLD = 0.5;
    public const WORD_CAPS_THRESHOLD = 0.4;

    public const TOTAL_LENGTH_THRESHOLD = 5;
    public const WORD_LENGTH_THRESHOLD = 4;

    protected string $message;

    public function __invoke(string $message)
    {
        $this->message = $message;
    }

    public function isPunishable(): bool
    {
        $caps = preg_match_all("/[A-Z]/", $this->message);
        $total = strlen($this->message);
        $ratio = $caps / $total;

        if ($ratio > self::TOTAL_CAPS_THRESHOLD) {
            return true;
        }

        return false;
    }

    protected function hasEnoughCharacters(): bool
    {
        return strlen($this->message) > self::TOTAL_LENGTH_THRESHOLD;
    }

    protected function hasPunishableWord()
    {
        $words = explode(" ", $this->message);

        foreach ($words as $word) {
            if ($this->isWordPunishable($word)) {
                return true;
            }
        }

        return false;
    }

    protected function isWordPunishable(string $word): bool
    {
        if (!$this->isWordLongEnough($word)) {
            return false;
        }

        $caps = preg_match_all("/[A-Z]/", $word);
        $total = strlen($word);
        $ratio = $caps / $total;

        if ($ratio > self::WORD_CAPS_THRESHOLD) {
            return true;
        }

        return false;
    }

    protected function isWordLongEnough(string $word): bool
    {
        return strlen($word) > self::WORD_LENGTH_THRESHOLD;
    }
}
