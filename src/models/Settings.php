<?php

namespace samuelreichor\loanwords\models;

use Craft;
use craft\base\Model;

/**
 * craft-loanwords settings
 */
class Settings extends Model
{
    public string $defaultLang = 'en';
    public bool $caseSensitive = false;
    public string $cssClass = '';
}
