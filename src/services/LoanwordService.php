<?php

namespace samuelreichor\loanwords\services;

use Craft;
use craft\base\Component;
use craft\errors\ElementNotFoundException;
use Throwable;
use yii\base\Exception;

class LoanwordService extends Component
{
    /**
     * @throws ElementNotFoundException
     * @throws Exception
     * @throws Throwable
     */
    public function saveLoanword($loanword): bool
    {
        if (!Craft::$app->elements->saveElement($loanword)) {
            Craft::error('Failed to save Loanword: ' . json_encode($loanword->getErrors()), __METHOD__);
            return false;
        }

        return true;
    }
}
