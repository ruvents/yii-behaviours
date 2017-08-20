<?php

class UpdatableBehavior extends \CActiveRecordBehavior
{
    public function events()
    {
        return [
            'onBeforeSave' => 'beforeSave',
        ];
    }

    protected function beforeSave($event)
    {
        if ($this->owner->isNewRecord === false) {
            $this->owner->setAttribute('UpdateTime', 'NOW()');
        }
    }

    public static function getMigrationFields()
    {
        return [
            'CreateTime' => "TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT ('now'::text)::timestamp(0) without time zone",
            'UpdateTime' => "TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT ('now'::text)::timestamp(0) without time zone",
        ];
    }
}