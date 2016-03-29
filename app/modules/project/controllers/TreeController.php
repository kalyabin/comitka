<?php
namespace project\controllers;

use app\components\AuthControl;
use project\models\Project;
use VcsCommon\BaseRepository;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Controller to view project tree.
 */
class TreeController extends Controller
{
    /**
     * @var Project Project model. Required for all actions.
     */
    protected $project;

    /**
     * @var BaseRepository Repository object
     */
    protected $repository;

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
            ]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        // find a project using id request variable
        $projectId = Yii::$app->request->get('id', null);
        if (is_scalar($projectId)) {
            $this->project = Project::findOne((int) $projectId);
        }
        if (!$this->project instanceof Project) {
            throw new NotFoundHttpException();
        }
        $this->repository = $this->project->getRepositoryObject();
        return parent::init();
    }

    /**
     * View project path tree or raw project file
     *
     * @param string|null $path Relative project path (null if root)
     */
    public function actionRaw($path = null)
    {
        $absolutePath = FileHelper::normalizePath($this->repository->getProjectPath() . DIRECTORY_SEPARATOR . $path);

        if (!StringHelper::startsWith($absolutePath, $this->repository->getProjectPath()) || !file_exists($absolutePath)) {
            throw new NotFoundHttpException();
        }

        $breadcrumbs = $this->generateBreadcrumbs($path);

        if (is_dir($absolutePath)) {
            // render path tree
            $filesList = $this->repository->getFilesList($path);

            return $this->render('tree', [
                'project' => $this->project,
                'repository' => $this->repository,
                'filesList' => $filesList,
                'breadcrumbs' => $breadcrumbs,
            ]);
        }
        else if (is_file($absolutePath)) {
            // render raw file
            $fileContents = file_get_contents($absolutePath);

            return $this->render('raw', [
                'repository' => $this->repository,
                'project' => $this->project,
                'breadcrumbs' => $breadcrumbs,
                'fileContents' => $fileContents,
            ]);
        }

        // if else - 404
        throw new NotFoundHttpException();
    }

    /**
     * Generate breadcrumbs array using relative project path
     *
     * @param string $path Relative project path
     * @return array String array to use as breadcrumbs
     */
    protected function generateBreadcrumbs($path)
    {
        $path = trim($path, DIRECTORY_SEPARATOR);
        $previewPath = null;
        $breadcrumbs = array_map(function($value) use (&$previewPath) {
            $path = $value;
            if ($previewPath !== null) {
                $path = $previewPath . DIRECTORY_SEPARATOR . $value;
            }
            $previewPath = $path;
            return [
                'path' => $path,
                'value' => $value,
            ];
        }, $path === null ? [] : explode(DIRECTORY_SEPARATOR, FileHelper::normalizePath($path)));

        array_unshift($breadcrumbs, [
            'path' => null,
            'value' => $this->project->title,
        ]);

        return $breadcrumbs;
    }
}
