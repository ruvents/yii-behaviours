<?php

class AttributableBehavior extends \CActiveRecordBehavior
{
    /** @var string */
    public $attribute = 'Attributes';

    public function byAttribute($attribute, $value)
    {
        $model = $this->owner;

        $criteria = $model->getDbCriteria();
        $criteria->addColumnCondition(["{$model->getTableAlias(true)}.\"{$this->attribute}\"->>'{$attribute}'" => "$value"]);

        return $model;
    }

    public function byAttributeExists($attribute)
    {
        $model = $this->owner;

        $criteria = $model->getDbCriteria();
        $criteria->addCondition("jsonb_exists({$model->getTableAlias(true)}.\"{$this->attribute}\", '{$attribute}')");

        return $model;
    }

    public function byAttributeSearch($attribute, $value)
    {
        $model = $this->owner;

        $criteria = $model->getDbCriteria();
        $criteria->addSearchCondition("{$model->getTableAlias(true)}.\"{$this->attribute}\"->>'{$attribute}'", "$value");

        return $model;
    }

    public function afterFind($event)
    {
        $model = $this->owner;
        $attrs = $model->getAttribute($this->attribute);

        $model->setAttribute($this->attribute, '{}' !== $attrs
            ? json_decode($attrs, true)
            : []
        );

        parent::afterFind($event);
    }

    public function beforeSave($event)
    {
        $model = $this->owner;
        $attrs = $model->getAttribute($this->attribute);

        $model->setAttribute($this->attribute, false === empty($attrs)
            ? json_encode($attrs, JSON_UNESCAPED_UNICODE)
            : '{}'
        );

        parent::beforeSave($event);
    }

    public static function getMigrationFields($attributeName)
    {
        return [
            $attributeName => "jsonb NOT NULL DEFAULT '{}'",
        ];
    }
}