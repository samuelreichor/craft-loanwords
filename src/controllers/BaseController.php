<?php

namespace samuelreichor\loanwords\controllers;

use Craft;
use craft\web\Controller;
use samuelreichor\loanwords\elements\Loanword;
use samuelreichor\loanwords\Loanwords;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class BaseController extends Controller
{
    /**
     * @throws ForbiddenHttpException
     * @throws BadRequestHttpException
     */
    public function actionEdit(?int $loanwordId = null, ?Loanword $loanword = null): Response
    {
        $this->requirePermission('saveLoanword');
        $defaultLang = Loanwords::getInstance()->getSettings()->defaultLang;
        if (!$loanword) {
            // Are we editing an existing event?
            if ($loanwordId) {
                $loanword = Loanword::find()->id($loanwordId)->one();
                if (!$loanword) {
                    throw new BadRequestHttpException("Invalid loanword ID: $loanwordId");
                }
            } else {
                // We're creating a new event
                $loanword = new Loanword();
                $loanword->lang = $defaultLang;
            }
        }

        return $this->renderTemplate('loanwords/loanword/_edit', [
            'loanword' => $loanword,
        ]);
    }

    /**
     * @throws ForbiddenHttpException
     * @throws BadRequestHttpException
     */
    public function actionSave()
    {
        $this->requirePermission('saveLoanword');
        $loanwordId = $this->request->getBodyParam('id');

        if ($loanwordId) {
            $loanword = Loanword::find()->id($loanwordId)->one();
            if (!$loanword) {
                throw new BadRequestHttpException("Invalid loanword ID: $loanwordId");
            }
        } else {
            // Creating a new loanword
            $loanword = new Loanword();
        }

        // Populate the event with the form data
        $loanword->title = $this->request->getBodyParam('title');
        $loanword->lang = $this->request->getBodyParam('lang');

        // Try to save it
        if (!Loanwords::getInstance()->loanwords->saveLoanword($loanword)) {
            // @phpstan-ignore-next-line
            if ($this->request->acceptsJson) {
                return $this->asJson(['errors' => $loanword->getErrors()]);
            }

            $this->setFailFlash(Craft::t('loanwords', 'Couldn’t save loanword.'));

            // Send the event back to the edit action
            Craft::$app->urlManager->setRouteParams([
                'loanword' => $loanword,
            ]);
            return null;
        }

        // @phpstan-ignore-next-line
        if ($this->request->acceptsJson) {
            return $this->asJson(['success' => true]);
        }

        $this->setSuccessFlash(Craft::t('loanwords', 'Loanword saved.'));
        $this->redirectToPostedUrl($loanword);
    }
}
