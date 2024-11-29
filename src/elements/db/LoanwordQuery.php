<?php

namespace samuelreichor\loanwords\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

/**
 * Loanword query
 */
class LoanwordQuery extends ElementQuery
{
    public mixed $lang = null;

    public function lang($value): self
    {
        $this->lang = $value;
        return $this;
    }

    protected function beforePrepare(): bool
    {
        // JOIN our `products` table:
        $this->joinElementTable('loanwords');

        $this->query->select([
            'loanwords.title',
            'loanwords.lang',
        ]);

        if ($this->lang) {
            $this->subQuery->andWhere(Db::parseParam('loanwords.lang', $this->lang));
        }

        return parent::beforePrepare();
    }
}
