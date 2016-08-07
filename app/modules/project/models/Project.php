<?php

namespace project\models;

use GitView\GitWrapper;
use VcsCommon\BaseRepository;
use VcsCommon\exception\CommonException;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;

/**
 * Project model
 *
 * @property integer $id
 * @property string $title
 * @property string $repo_type Repository type like hg, git or svn
 * @property string $repo_path Repository path
 */
class Project extends ActiveRecord
{
    /**
     * Maximum title string length
     */
    const MAX_TITLE_LENGTH = 100;

    /**
     * Repository type Mercurial
     */
    const REPO_HG = 'hg';

    /**
     * Repository type GIT
     */
    const REPO_GIT = 'git';

    /**
     * Repository type Subversion
     */
    const REPO_SVN = 'svn';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%project}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'repo_type', 'repo_path'], 'required'],
            ['repo_path', 'string'],
            ['title', 'string', 'max' => self::MAX_TITLE_LENGTH],
            ['repo_type', 'in', 'range' => [self::REPO_GIT, self::REPO_HG, self::REPO_SVN]],
            ['repo_path', 'validateRepoPath'],
            ['repo_type', 'validateRepoType'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('project', 'ID'),
            'title' => Yii::t('project', 'Project title'),
            'repo_type' => Yii::t('project', 'Repository type'),
            'repo_path' => Yii::t('project', 'Repository directory'),
        ];
    }

    public function attributeHints()
    {
        return [
            'repo_path' => Yii::t('project', 'Type absolute path to project where current system user can read.'),
        ];
    }

    /**
     * Validate repo type - detect repository at path.
     *
     * @param string $attribute repo_type attribute
     * @param array $params validation params
     */
    public function validateRepoType($attribute, $params)
    {
        if (!$this->hasErrors($attribute) && !$this->hasErrors('repo_path')) {
            try {
                $repostory = $this->getRepositoryObject();
                $repostory->checkStatus();
            }
            catch (CommonException $ex) {
                $this->addError($attribute, Yii::t('project', 'Detect repository error: {error}', [
                    'error' => $ex->getMessage(),
                ]));
            }
        }
    }

    /**
     * Validate and filter repository path
     *
     * @param string $attribute validation attribute
     * @param array $params validation params
     * @return void
     */
    public function validateRepoPath($attribute, $params)
    {
        if (!$this->hasErrors($attribute)) {
            // path
            $path = FileHelper::normalizePath($this->{$attribute});
            // windows flag
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN';
            if (
                ($isWindows && !preg_match('#^[A-Z]{1}\:\/', $path)) ||
                (!$isWindows && !StringHelper::startsWith($path, DIRECTORY_SEPARATOR))
            ) {
                // path must be absolute
                $this->addError($attribute, Yii::t('project', 'Please type absolute path to repository'));
                return;
            }
            if (!file_exists($path)) {
                $this->addError($attribute, Yii::t('project', 'Directory not found here'));
                return;
            }
            if (!is_dir($path)) {
                $this->addError($attribute, Yii::t('project', 'This not a directory'));
                return;
            }
            if (!is_readable($path) || !is_writeable($path)) {
                $this->addError($attribute, Yii::t('project', 'Current system user can\'t read at specified path.'));
                return;
            }
            $this->repo_path = $path;
        }
    }

    /**
     * @return array repository available types
     */
    public function getRepoTypeList()
    {
        return [
            self::REPO_GIT => Yii::t('project', 'Git'),
            self::REPO_HG => Yii::t('project', 'Mercurial'),
            self::REPO_SVN => Yii::t('project', 'Subversion'),
        ];
    }

    /**
     * @return string|null repository type name from getRepoTypeList()
     */
    public function getRepoTypeName()
    {
        $list = $this->getRepoTypeList();
        return is_scalar($this->repo_type) && isset($list[$this->repo_type]) ? $list[$this->repo_type] : null;
    }

    /**
     * Get project repostirory object using repo_path variable.
     *
     * @return BaseRepository
     * @throws CommonException
     */
    public function getRepositoryObject()
    {
        if ($this->repo_type == self::REPO_GIT) {
            /* @var $gitWrapper GitWrapper */
            $gitWrapper = Yii::$app->gitWrapper;
            return $gitWrapper->getRepository($this->repo_path);
        } elseif ($this->repo_type == self::REPO_HG) {
            /* @var $hgWrapper HgWrapper */
            $hgWrapper = Yii::$app->hgWrapper;
            return $hgWrapper->getRepository($this->repo_path);
        }

        throw new CommonException(Yii::t('project', 'Unknown repo type'));
    }

    /**
     * @return string repository CSS label class
     */
    public function getRepoLabelCss()
    {
        $ret = 'label ';
        if ($this->repo_type == self::REPO_GIT) {
            $ret .= 'label-warning label-git';
        }
        else if ($this->repo_type == self::REPO_HG) {
            $ret .= 'label-default label-hg';
        }
        else if ($this->repo_type == self::REPO_SVN) {
            $ret = 'label-info label-svn';
        }
        return $ret;
    }
}
