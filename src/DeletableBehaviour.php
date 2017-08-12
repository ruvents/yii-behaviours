<?php

namespace ruvents\yii\behaviours;

class DeletableBehaviour extends \CActiveRecordBehavior
{
    protected function beforeDelete($event)
    {
        $model = $this->owner;

        $model->setAttribute('Deleted', true);
        $model->setAttribute('DeletionTime', 'NOW()');
        $model->save();

        return false;
    }

    protected function beforeFind($event)
    {
        $this->owner->getDbCriteria()->addCondition('not "Deleted"');
    }

    public static function getMigrationFields()
    {
        return [
            'Deleted' => 'BOOLEAN NOT NULL DEFAULT false',
            'DeleteTime' => 'TIMESTAMP WITHOUT TIME ZONE DEFAULT NULL',
        ];
    }
}