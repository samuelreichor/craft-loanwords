<?php

namespace samuelreichor\loanwords\controllers;

use Craft;
use craft\db\Query;
use craft\errors\MissingComponentException;
use craft\web\Controller;
use samuelreichor\loanwords\Constants;
use yii\db\Exception;
use yii\db\Expression;
use yii\web\BadRequestHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\Response;

class BaseController extends Controller
{

    public function actionIndex(): Response
    {
        $request = Craft::$app->getRequest();
        $currentSiteHandle = $request->getQueryParam('site');
        $currentSiteId = Craft::$app->sites->getSiteByHandle($currentSiteHandle)->id;
        $variables = [];
        $variables['loanwords'] = (new Query())
            ->select(['id', 'loanword', 'lang'])
            ->from(Constants::TABLE_MAIN)
            ->where(['siteId' => $currentSiteId])
            ->all();

        return $this->renderTemplate('loanwords/index', $variables);
    }

    public function actionNew(): Response
    {
        return $this->renderTemplate('loanwords/edit');
    }

    /**
     * @throws Exception
     * @throws MethodNotAllowedHttpException
     */
    public function actionSave(): Response
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();

        $loanword = $request->getBodyParam('loanword');
        $lang = $request->getBodyParam('lang');
        $propagate = $request->getBodyParam('propagate', false);

        if ($propagate) {
            $allSiteIds = array_map(function ($site) {
                return $site->id;
            }, Craft::$app->sites->getEditableSites());
            $this->saveLoanword($loanword, $lang, $allSiteIds);
        } else {
            $siteHandle = $request->getQueryParam('site');
            $siteId = Craft::$app->sites->getSiteByHandle($siteHandle)->id;
            $this->saveLoanword($loanword, $lang, [$siteId]);
        }

        $this->setSuccessFlash(Craft::t('loanwords', 'Loanword "{type}" saved successfully.', [
            'type' => $loanword,
        ]));
        return $this->redirect('loanwords');
    }

    /**
     * @throws Exception
     * @throws MethodNotAllowedHttpException
     * @throws BadRequestHttpException
     */
    public function actionDelete(): Response
    {
        $this->requirePostRequest();
        $id = Craft::$app->getRequest()->getBodyParam('id');

        if ($id) {
            Craft::$app->db->createCommand()
                ->delete(Constants::TABLE_MAIN, ['id' => $id])
                ->execute();
        }

        return $this->redirectToPostedUrl();
    }

    /**
     * @throws Exception
     */
    public function saveLoanword($loanword, $lang, $siteIds): void
    {
        foreach ($siteIds as $siteId) {
            $exists = (new Query())
                ->from(Constants::TABLE_MAIN)
                ->where([
                    'siteId' => $siteId,
                    'loanword' => $loanword,
                ])
                ->exists();

            if (!$exists) {
                Craft::$app->db->createCommand()
                    ->insert(Constants::TABLE_MAIN, [
                        'siteId' => $siteId,
                        'loanword' => $loanword,
                        'lang' => $lang,
                        'dateCreated' => new Expression('NOW()'),
                        'dateUpdated' => new Expression('NOW()'),
                    ])
                    ->execute();
            }
        }
    }
}
