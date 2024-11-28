<?php

namespace samuelreichor\loanwords\controllers;

use Craft;
use craft\web\Controller;
use samuelreichor\loanwords\Constants;
use samuelreichor\loanwords\elements\Loanword;
use yii\db\Exception;
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
        $variables['loanwords'] = Loanword::find()
            ->siteId($currentSiteId)
            ->all();


        return $this->renderTemplate('loanwords/index', $variables);
    }

    public function actionNew(): Response
    {
        return $this->renderTemplate('loanwords/edit');
    }

    /**
     * @throws MethodNotAllowedHttpException
     * @throws Exception
     */
    public function actionSave(): Response
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $loanword = $request->getBodyParam('title');
        $lang = $request->getBodyParam('lang');
        $propagate = $request->getBodyParam('propagate', false);

        if ($propagate === "1") {
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

    public function saveLoanword($loanword, $lang, $siteIds): void
    {
        $newLoanword = new Loanword();
        $newLoanword->title = $loanword;
        $newLoanword->loanword = $loanword;
        $newLoanword->lang = $lang;

        foreach ($siteIds as $siteId) {
            $newLoanword->siteId = $siteId;

            try {
                Craft::$app->elements->saveElement($newLoanword);
            } catch (\Throwable $e) {
                Craft::error('Failed to save Loanword: ' . json_encode($loanword) . $e, __METHOD__);

            }
        }
    }
}
