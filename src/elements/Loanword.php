<?php

namespace samuelreichor\loanwords\elements;

use Craft;
use craft\base\Element;
use craft\helpers\Db;
use samuelreichor\loanwords\Constants;
use samuelreichor\loanwords\elements\db\LoanwordQuery;
use yii\db\Exception;

class Loanword extends Element
{
    public ?string $loanword = null;
    public ?string $lang = null;

    public static function displayName(): string
    {
        return Craft::t('loanwords', 'Loanword');
    }

    public static function refHandle(): ?string
    {
        return 'loanword';
    }

    public static function trackChanges(): bool
    {
        return false;
    }

    public static function hasStatuses(): bool
    {
        return false;
    }

    public static function hasTitles(): bool
    {
        return true;
    }

    public static function isLocalized(): bool
    {
        return true;
    }

    public static function find(): LoanwordQuery
    {
        return new LoanwordQuery(static::class);
    }

    /**
     * @throws Exception
     */
    public function afterSave(bool $isNew): void
    {
        if (!$this->propagating) {
            Db::upsert(Constants::TABLE_MAIN, [
                'id' => $this->id,
                'siteId' => $this->siteId,
            ], [
                'title' => $this->loanword,
                'loanword' => $this->loanword,
                'lang' => $this->lang,
            ]);
        }

        parent::afterSave($isNew);
    }

    protected static function defineTableAttributes(): array
    {
        return [
            'loanword' => Craft::t('loanwords', 'Loanword'),
            'lang' => Craft::t('loanwords', 'Lang'),
        ];
    }

/*    protected static function defineSortOptions(): array
    {
        return [
            'title' => Craft::t('app', 'Title'),
            'price' => Craft::t('loanwords', 'Loanword'),
        ];
    }*/

/*    protected static function defineExporters(string $source): array
    {
        $exporters = parent::defineExporters($source);
        $exporters[] = MyExporter::class;
        return $exporters;
    }*/

    protected static function defineSearchableAttributes(): array
    {
        return [
            'loanword',
            'lang',
        ];
    }
}
