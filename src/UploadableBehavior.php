<?php

class UploadableBehavior extends CActiveRecordBehavior
{
    /**
     * Список полей, содержащий загружаемые объекты.
     *
     * Пример конфигурации:
     *
     * [
     *     'AttributeName' => ['fileDir' => 'somewhere/i/belong']
     * ]
     *
     * @var string[]
     */
    public $fileFields = [];

    public function events()
    {
        return [
            'onBeforeSave' => 'beforeSave',
            'onAfterDelete' => 'afterDelete',
        ];
    }

    public function beforeSave($event)
    {
        /** @var \application\components\ActiveRecord $model */
        $model = $this->owner;

        // Если конфигурации загружаемых полей нет, то генерируем её.
        if ($this->fileFields === null) {
            $this->fileFields = [];
            foreach ($model->getAttributes() as $attribute => $config) {
                $this->fileFields[$attribute] = ['fileDir' => '/files'];
            }
        }

        foreach ($this->fileFields as $attribute => $config) {
            $value = $model->getAttribute($attribute);
            if ($value instanceof CUploadedFile) {
                $publicPath = Yii::getPathOfAlias('webroot');
                $fileDir = $config['fileDir'] ?? '/files';
                $fileName = uniqid('', true).'.'.strtolower($value->getExtensionName());
                if (false === $value->saveAs("{$publicPath}/{$fileDir}/{$fileName}")) {
                    $errmsg = 'Неизвестная ошибка загрузки файла. Обратитесь к разработчику.';
                    // Ошибка сохранения. Может, просто не существует директория для загрузки файлов? Попробуем создать...
                    if (false === is_dir("{$publicPath}/{$fileDir}")) {
                        $errmsg = mkdir("{$publicPath}/{$fileDir}", 0770, true)
                            ? "Директория для загрузки файлов {$fileDir} не существовала, но была успешно создана. Попробуйте ещё раз."
                            : "Директория для загрузки файлов {$fileDir} не существует и не может быть автоматически создана. Обратитесь к разработчику.";
                    }
                    $model->addError($attribute, $errmsg);
                    $event->isValid = false;
                    continue;
                }
                // Удаляем старый файл, если он имеет место быть. Он более не нужен.
                if ($originalValue = $model->getAttributeBackup($attribute)) {
                    @unlink($publicPath.$originalValue);
                }
                // Сохраняем имя файла в модели
                $model->setAttribute($attribute, "/{$fileDir}/{$fileName}");
            }
        }

        parent::beforeSave($event);
    }

    public function afterDelete($event)
    {
        parent::afterDelete($event);

        /** @var \application\components\ActiveRecord $model */
        $model = $this->owner;

        foreach ($this->fileFields as $attribute => $config) {
            if (false === empty($value = $model->getAttributeBackup($attribute))) {
                unlink(Yii::getPathOfAlias('webroot')."/$value");
            }
        }
    }
}
