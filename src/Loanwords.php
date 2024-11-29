<?php

namespace samuelreichor\loanwords;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Elements;
use craft\web\UrlManager;
use samuelreichor\loanwords\elements\Loanword;
use samuelreichor\loanwords\models\Settings;
use samuelreichor\loanwords\services\LoanwordService;
use samuelreichor\loanwords\services\TextReplacerService;
use samuelreichor\loanwords\twigextensions\TextReplacer;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
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
 *
 * @property LoanwordService $loanwords
 * @property TextReplacerService $textReplacerService
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
            'textReplacerService' => TextReplacerService::class,
        ]);

        $this->registerTwigExtensions();

        $this->attachEventHandlers();

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

        Event::on(Elements::class, Elements::EVENT_REGISTER_ELEMENT_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = Loanword::class;
        });

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules['loanwords'] = ['template' => 'loanwords/loanword/_index.twig'];
            $event->rules['loanwords/new'] = 'loanwords/base/edit';
            $event->rules['loanwords/<loanwordId:\d+>'] = 'loanwords/base/edit';
        });
    }

    private function registerTwigExtensions()
    {
        if (Craft::$app->request->getIsSiteRequest()) {
            $getReplacedText = new TextReplacer();
            Craft::$app->view->registerTwigExtension($getReplacedText);
        }
    }
}
