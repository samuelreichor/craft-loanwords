<?php

namespace samuelreichor\loanwords\services;

use Craft;
use samuelreichor\loanwords\elements\Loanword;
use samuelreichor\loanwords\Loanwords;

class TextReplacerService
{
    private ?array $anglicismsData = null;

    public function getLoanwordsData(): array
    {
        if ($this->anglicismsData === null) {
            $this->anglicismsData = $this->queryDatabaseForLoanwords();
        }
        return $this->anglicismsData;

    }

    public function textReplacer(string $text): string
    {
        // Step 1: Isolate all links and replace them with placeholders.
        preg_match_all('/href="([^"]+)"/', $text, $matches);
        $links = $matches[1];
        $placeholders = [];
        foreach ($links as $index => $link) {
            $placeholder = "LINK_PLACEHOLDER_{$index}";
            $text = str_replace('href="' . $link . '"', 'href="' . $placeholder . '"', $text);
            $placeholders[$placeholder] = $link;
        }

        // Step 2: Replace anglicisms throughout the text.
        $text = $this->replaceLoanwords($text);

        // Step 3: Replace the placeholders with the original links.
        foreach ($placeholders as $placeholder => $link) {
            $text = str_replace('href="' . $placeholder . '"', 'href="' . $link . '"', $text);
        }

        return $text;
    }

    private function replaceLoanwords(string $text): string
    {
        $anglicisms = $this->getLoanwordsData();
        $isCaseSensitive = Loanwords::getInstance()->getSettings()->caseSensitive;

        $patterns = [];
        $replacements = [];

        foreach ($anglicisms as $word) {
            $modifier = $isCaseSensitive ? '' : 'i'; // Set it to case-sensitive based on settings
            $patterns[] = '/\b' . preg_quote($word['title'], '/') . '\b/' . $modifier;

            $replacements[] = function ($matches) use ($word) {
                return '<span lang="'
                    . htmlspecialchars($word['lang'], ENT_QUOTES, 'UTF-8') . '" '
                    . $this->getLoanwordCssClass()
                    . '>' . $matches[0] . '</span>';
            };
        }

        foreach ($patterns as $index => $pattern) {
            $text = preg_replace_callback($pattern, $replacements[$index], $text);
        }

        return $text;
    }


    private function queryDatabaseForLoanwords(): array
    {
        $cacheKey = 'loanwords_query';
        if ($result = Craft::$app->getCache()->get($cacheKey)) {
            return $result;
        }

        Craft::$app->getElements()->startCollectingCacheInfo();

        $loanwords = Loanword::find()->select(['loanwords.title', 'loanwords.lang'])->asArray()->all();

        $cacheInfo = Craft::$app->getElements()->stopCollectingCacheInfo();
        $craftDuration = Craft::$app->getConfig()->getGeneral()->cacheDuration;

        Craft::$app->getCache()->set(
            $cacheKey,
            $loanwords,
            $craftDuration,
            $cacheInfo[0]
        );

        return $loanwords;
    }

    private function getLoanwordCssClass(): string
    {
        $defaultStyle = 'style="display: inline;"';
        $settingsClass = Loanwords::getInstance()->getSettings()->cssClass;

        if ($settingsClass !== '') {
            return 'class="' . htmlspecialchars($settingsClass, ENT_QUOTES, 'UTF-8') . '" ';
        }

        return $defaultStyle;
    }
}
