<?php

use Jcupitt\Vips;

class UploadableBehavior extends CActiveRecordBehavior
{
    /**
     * Список полей, содержащий загружаемые объекты.
     *
     * Пример конфигурации без обработки изображения:
     *
     * [
     *     'AttributeName' => ['fileDir' => 'somewhere/i/belong']
     * ]
     *
     * Пример конфигурации с обработкой изображения:
     *
     * [
     *     'AttributeName' => [
     *        'fileDir' => 'somewhere/i/belong',
     *        'shrinkWidth' => 1500, // обязательный параметр, без него обработка
     *                               // не будет производится даже при определении
     *                               // ниже следующих параметров
     *        'shrinkHeight' => 1500,
     *        'autorotate' => true,  // включен по умолчанию
     *     ]
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

    /**
     * @param \CModelEvent $event
     */
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
                $filepath = "{$publicPath}/{$fileDir}/{$fileName}";

                if (!is_dir("{$publicPath}/{$fileDir}")) {
                    if (!mkdir("{$publicPath}/{$fileDir}", 0770, true)) {
                        $model->addError($attribute, "Не удалось создать директорию для загрузки файла {$fileDir}. Обратитесь к разработчику.");
                        $event->isValid = false;
                        continue;
                    }
                }

                if (isset($config['shrinkWidth'])) {
                    $tparam = [];
                    if (isset($config['shrinkHeight'])) $tparam['height'] = $config['shrinkHeight'];
                    if (isset($config['autorotate'])) $tparam['auto_rotate'] = $config['autorotate'];

                    $img = Vips\Image::thumbnail($value->tempName, $config['shrinkWidth'], $tparam);
                    $img->writeToFile($filepath);
                } else $value->saveAs($filepath);

                if (!is_file($filepath)) {
                    $model->addError($attribute, "Не удалось сохранить файл {$filepath}. Обратитесь к разработчику.");
                    $event->isValid = false;
                    continue;
                }

                // Удаляем старый файл, если он имеет место быть. Он более не нужен.
                if ($originalValue = $model->getAttributeBackup($attribute)) {
                    @unlink($publicPath.$originalValue);
                }

                // Сохраняем имя файла в модели
                $model->setAttribute($attribute, "/{$fileDir}/{$fileName}");
            } else throw new \CException('Значение должно быть экземпляром CUploadedFile');
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
