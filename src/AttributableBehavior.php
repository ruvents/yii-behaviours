<?php

class AttributableBehavior extends \CActiveRecordBehavior
{
    public function events()
    {
        return [
            'onBeforeSave' => 'beforeSave',
            'onAfterFind' => 'afterFind',
        ];
    }

    public function byAttributeExists($attribute)
    {
        $model = $this->owner;

        $criteria = $model->getDbCriteria();
        $criteria->addCondition("jsonb_exists({$model->getTableAlias(true)}.\"Attributes\", '{$attribute}')");

        return $model;
    }

    public function byAttributeSearch($attribute, $value)
    {
        $model = $this->owner;

        $criteria = $model->getDbCriteria();
        $criteria->addSearchCondition("{$model->getTableAlias(true)}.\"Attributes\"->>'{$attribute}'", "$value");

        return $model;
    }

    public function afterFind($event)
    {
        $model = $this->owner;
        $attrs = $model->getAttribute('Attributes');

        /** @var \AttributableTrait $model */
        $model->initCustomAttributes($attrs);

        parent::afterFind($event);
    }

    public function beforeSave($event)
    {
        $model = $this->owner;
        /** @var \AttributableTrait $model */
        $json = $model->dumpCustomAttributes();
        /** @var \application\components\ActiveRecord $model */
        $model->setAttribute('Attributes', $json);

        parent::beforeSave($event);
    }

    public static function getMigrationFields($attributeName)
    {
        return [
            $attributeName => "jsonb NOT NULL DEFAULT '{}'",
        ];
    }
}