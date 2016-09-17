<?php
namespace app\controllers;

use app\components\AuthControl;
use project\models\ContributionReview;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

/**
 * Main system controller
 */
class MainController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'accessControl' => [
                'class' => AuthControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Need to review contributions
     *
     * Type variable has states:
     * - my-reviews - reviews to current contributor;
     * - all-contributions - all contributions;
     * - my-contributions - current user contributions;
     * - no-reviewer - contributions without reviewer;
     *
     * @param string $type Contribution types
     *
     * @return mixed
     *
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionReviews($type)
    {
        $res = ContributionReview::find()->with('project')->orderBy([
            'date' => SORT_DESC,
        ]);

        if ($type == 'my-reviews') {
            // contribution reviews to current contributor
            $res->andWhere([
                'reviewer_id' => Yii::$app->user->getId(),
                'reviewed' => null,
            ]);
        } elseif ($type == 'all-contributions') {
            // all contributions reviews
            $res->andWhere([
                'reviewed' => null,
            ]);
        } elseif ($type == 'my-contributions') {
            // current contributor commits
            $res->andWhere([
                'contributor_id' => Yii::$app->user->getId(),
            ]);
        } elseif ($type == 'no-reviewer') {
            // without reviewer
            $res->andWhere([
                'reviewer_id' => null,
            ]);
        } else {
            throw new \yii\web\NotFoundHttpException();
        }

        $dataProvider = new ActiveDataProvider([
            'id' => $type,
            'query' => $res,
            'sort' => false,
        ]);

        return $this->render('reviews', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Application index
     */
    public function actionIndex()
    {
        return $this->redirect(['reviews', 'type' => 'my-reviews']);
    }
}
