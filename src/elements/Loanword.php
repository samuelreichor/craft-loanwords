<?php

namespace samuelreichor\loanwords\elements;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use craft\helpers\Db;
use craft\helpers\UrlHelper;
use samuelreichor\loanwords\Constants;
use samuelreichor\loanwords\elements\db\LoanwordQuery;
use yii\db\Exception;

/**
 * Loanword element type
 */
class Loanword extends Element
{
    public ?string $title = null;
    public ?string $lang = null;

    public static function displayName(): string
    {
        return Craft::t('loanwords', 'Loanword');
    }

    public static function lowerDisplayName(): string
    {
        return Craft::t('loanwords', 'loanword');
    }

    public static function pluralDisplayName(): string
    {
        return Craft::t('loanwords', 'Loanword');
    }

    public static function pluralLowerDisplayName(): string
    {
        return Craft::t('loanwords', 'loanword');
    }

    public static function refHandle(): ?string
    {
        return 'loanword';
    }

    public static function trackChanges(): bool
    {
        return false;
    }

    public static function hasTitles(): bool
    {
        return false;
    }

    public static function hasDrafts(): bool
    {
        return false;
    }

    public static function hasUris(): bool
    {
        return false;
    }

    public static function isLocalized(): bool
    {
        return false;
    }

    public static function hasStatuses(): bool
    {
        return false;
    }


    public static function find(): ElementQueryInterface
    {
        return Craft::createObject(LoanwordQuery::class, [static::class]);
    }

    protected static function defineSources(string $context): array
    {
        return [
            [
                'key' => '*',
                'label' => Craft::t('loanwords', 'Loanwords'),
            ],
        ];
    }

    protected static function includeSetStatusAction(): bool
    {
        return false;
    }

    protected static function defineTableAttributes(): array
    {
        return [
            'lang' => ['label' => Craft::t('app', 'Lang')],
            'dateCreated' => ['label' => Craft::t('app', 'Date Created')],
            'id' => ['label' => Craft::t('app', 'Id')],
            'dateUpdated' => ['label' => Craft::t('app', 'Date Updated')],
        ];
    }

    protected static function defineDefaultTableAttributes(string $source): array
    {
        return [
            'lang',
        ];
    }

    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            [['title', 'lang'], 'required'],
        ]);
    }

    protected function route(): array|string|null
    {
        // Define how loanword should be routed when their URLs are requested
        return [
            'templates/render',
            [
                'template' => 'site/template/path',
                'variables' => ['loanword' => $this],
            ],
        ];
    }

    public function canView(User $user): bool
    {
        if (parent::canView($user)) {
            return true;
        }
        // todo: implement user permissions
        return $user->can('viewLoanword');
    }

    public function canSave(User $user): bool
    {
        if (parent::canSave($user)) {
            return true;
        }
        // todo: implement user permissions
        return $user->can('saveLoanword');
    }

    public function canDuplicate(User $user): bool
    {
        if (parent::canDuplicate($user)) {
            return true;
        }
        // todo: implement user permissions
        return $user->can('saveLoanword');
    }

    public function canDelete(User $user): bool
    {
        if (parent::canSave($user)) {
            return true;
        }
        // todo: implement user permissions
        return $user->can('deleteLoanword');
    }

    protected function cpEditUrl(): ?string
    {
        return sprintf('loanwords/%s', $this->getCanonicalId());
    }

    public function getPostEditUrl(): ?string
    {
        return UrlHelper::cpUrl('loanword');
    }

    /**
     * @throws Exception
     */
    public function afterSave(bool $isNew): void
    {
        $isNewLoanword = Loanword::find()->id($this->id)->one() === null;
        if (!$this->propagating) {
            if ($isNewLoanword) {
                Db::upsert(Constants::TABLE_MAIN, [
                    'id' => $this->id,
                    'title' => $this->title,
                    'lang' => $this->lang,
                    'dateUpdated' => Db::prepareDateForDb(new \DateTime()),
                ]);
            } else {
                Db::upsert(Constants::TABLE_MAIN, [
                    'id' => $this->id,
                ], [
                    'title' => $this->title,
                    'lang' => $this->lang,
                    'dateUpdated' => Db::prepareDateForDb(new \DateTime()),
                ]);
            }
        }

        parent::afterSave($isNew);
    }
}
