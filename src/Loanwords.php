<?php

namespace samuelreichor\loanwords;

use Craft;
use samuelreichor\loanwords\services\LoanwordService;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Elements;
use craft\web\UrlManager;
use samuelreichor\loanwords\elements\Loanword;
use samuelreichor\loanwords\models\Settings;
use yii\base\Event;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * Loanwords plugin
 *
 * @method static Loanwords getInstance()
 * @method Settings getSettings()
 * @author Samuel Reichör <samuelreichor@gmail.com>
 * @copyright Samuel Reichör
 * @license MIT
 */
class Loanwords extends Plugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSection = true;
    public bool $hasCpSettings = true;
    public static ?Loanwords $plugin = null;

    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        $this->setComponents([
            'loanwords' => LoanwordService::class,
        ]);

        $this->attachEventHandlers();

        if (Craft::$app->getRequest()->getIsCpRequest()) {
            $this->registerCpRoutes();
        }

        // Any code that creates an element query or loads Twig should be deferred until
        // after Craft is fully initialized, to avoid conflicts with other plugins/modules
        Craft::$app->onInit(function() {
            // ...
        });
    }

    public function getPluginName(): string
    {
        return Craft::t('loanwords', 'Loanwords');
    }

    /**
     * @throws InvalidConfigException
     */
    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    /**
     * @throws SyntaxError
     * @throws Exception
     * @throws RuntimeError
     * @throws LoaderError
     */
    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('loanwords/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }

    private function attachEventHandlers(): void
    {
        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = Loanword::class;
            }
        );

        Event::on(Elements::class, Elements::EVENT_REGISTER_ELEMENT_TYPES, function (RegisterComponentTypesEvent $event) {
            $event->types[] = Loanword::class;
        });
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function (RegisterUrlRulesEvent $event) {
            $event->rules['loanwords'] = ['template' => 'loanwords/loanword/_index.twig'];
            $event->rules['loanwords/new'] = 'loanwords/base/edit';
            $event->rules['loanwords/<loanwordId:\d+>'] = 'loanwords/base/edit';
        });
    }

    private function registerCpRoutes(): void
    {
/*         Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, [
                'loanwords' => 'loanwords/base/index',
                'loanwords/new' => 'loanwords/base/new',
            ]);
        }); */
    }
}
