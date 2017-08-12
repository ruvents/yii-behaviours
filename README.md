yii-behaviours
==============

[![License](https://poser.pugx.org/happyproff/yii-easyimages/license.svg)](https://packagist.org/packages/happyproff/yii-easyimages)

Коллекция поведений для Yii 1.x ActiveRecord моделей.

## Использование

Добавить в `composer.json` зависимость:

```json
"vizh/yii-behaviours": "*@dev"
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
$tableSchema = array_merge($tableSchema, TimestampableBehavior::getMigrationFields());

$this->createTable('BlogPost', $tableSchema);
```

#### Добавление полей в существующую таблицу

```php
foreach (DeletableBehaviour::getMigrationFields() as $column => $type) {
	$this->addColumn('BlogPost', $column, $type);
}
```

## Конфигурация модели

```php
public function behaviors()
{
	return [
		['class' => '\ruvents\yii\behaviors\TimestampableBehavior'],
		['class' => '\ruvents\yii\behaviors\DeletableBehavior']
	];
}
```