<?php

namespace samuelreichor\loanwords\twigextensions;

use samuelreichor\loanwords\Loanwords;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TextReplacer extends AbstractExtension
{
    public function getName(): string
    {
        return 'Replace loanwords for Screenreader';
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('a11yTextReplacer', [$this, 'textReplacer']),
        ];
    }

    public function textReplacer(string $text = null): string
    {
        return $text ? Loanwords::getInstance()->textReplacerService->textReplacer($text) : '';
    }
}
