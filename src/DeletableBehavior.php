<?php

class DeletableBehavior extends \CActiveRecordBehavior
{
    public function events()
    {
        return [
            'onBeforeDelete' => 'beforeDelete',
            'onBeforeFind' => 'beforeFind',
        ];
    }

    public function beforeDelete($event)
    {
        $model = $this->owner;

        $model->setAttribute('Deleted', true);
        $model->setAttribute('DeletionTime', 'NOW()');
        $model->save();

        return false;
    }

    public function beforeFind($event)
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