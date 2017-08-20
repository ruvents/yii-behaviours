yii-behaviors
==============

[![License](https://poser.pugx.org/happyproff/yii-easyimages/license.svg)](https://packagist.org/packages/happyproff/yii-easyimages)

Коллекция поведений для Yii 1.x ActiveRecord моделей.

## Использование

Добавить в `composer.json` зависимость:

```json
"vizh/yii-behaviors": "0.0.6"
```

## Миграции

#### Создание новой таблицы

```php
$tableSchema = [
	'Id' => 'serial primary key',
	'PostId' => 'integer not null',
	'UserId' => 'integer not null',
	'Name' => 'varchar(1000) not null',
	'Body' => 'text not null'
];

$tableSchema = array_merge($tableSchema, DeletableBehavior::getMigrationFields());
$tableSchema = array_merge($tableSchema, UpdatableBehavior::getMigrationFields());

$this->createTable('BlogPost', $tableSchema);
```

#### Добавление полей в существующую таблицу

```php
foreach (DeletableBehavior::getMigrationFields() as $column => $type) {
	$this->addColumn('BlogPost', $column, $type);
}
```

## Конфигурация модели

```php
public function behaviors()
{
	return [
		['class' => 'UpdatableBehavior'],
		['class' => 'DeletableBehavior'],
		['class' => 'AttributableBehavior', 'attribute' => 'Attributes']
	];
}
```

Для корректной работы AttributableBehavior необходимо завести в модели ActiveRecord публичное свойство, названное аналогично значению параметра 'attribute' настроек поведения. Удобства ради, можно "пробросить" следующие методы в модель:

```php
/*
 * Методы AttributableBehavior
 * @method Role byAttribute($attribute, $value)
 * @method Role byAttributeExists($attribute)
 * @method Role byAttributeSearch($attribute, $value)
 */
```