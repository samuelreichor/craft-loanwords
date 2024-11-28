<?php

namespace samuelreichor\loanwords\elements\db;

use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class LoanwordQuery extends ElementQuery
{
    public mixed $loanword = null;
    public mixed $lang = null;

    public function loanword($value): self
    {
        $this->loanword = $value;
        return $this;
    }

    public function lang($value): self
    {
        $this->lang = $value;
        return $this;
    }

    protected function beforePrepare(): bool
    {
        // JOIN our `products` table:
        $this->joinElementTable('loanwords');

        // SELECT the `price` and `currency` columns:
        $this->query->select([
            'loanwords.loanword',
            'loanwords.lang',
        ]);

        if ($this->loanword) {
            $this->subQuery->andWhere(Db::parseParam('loanwords.loanword', $this->loanword));
        }

        if ($this->lang) {
            $this->subQuery->andWhere(Db::parseParam('loanwords.lang', $this->lang));
        }

        return parent::beforePrepare();
    }

}
