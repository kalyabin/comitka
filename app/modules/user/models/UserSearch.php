<?php
namespace user\models;

use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * Model for users search form control
 */
class UserSearch extends User
{
    /**
     * Validation rules
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_BLOCKED, self::STATUS_UNACTIVE]],
            ['name', 'string', 'max' => self::MAX_NAME_LENGTH],
            ['email', 'string', 'max' => self::MAX_EMAIL_LENGTH],
        ];
    }

    /**
     * Search users with specific params like email, name or status.
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search(array $params)
    {
        /* @var $query ActiveQuery */
        $query = self::find();

        $this->load($params);

        if ($this->validate()) {
            $query->andFilterWhere(['status' => $this->status])
                ->andFilterWhere(['like', 'name', $this->name])
                ->andFilterWhere(['like', 'email', $this->email]);
        }

        return new ActiveDataProvider([
            'query' => $query,
        ]);
    }
}
