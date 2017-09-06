<?php

trait AttributableTrait
{
    /** @var array */
    private $_attributes = [];

    /**
     * Проверяет наличие
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasCustomAttribute($name)
    {
        return array_key_exists($name, $this->_attributes);
    }

    /**
     * @param string $name
     *
     * @return bool|int|null|string
     */
    public function getCustomAttribute($name)
    {
        return isset($this->_attributes[$name])
            ? $this->_attributes[$name]
            : null;
    }

    public function setCustomAttribute($attribute, $value)
    {
        $this->_attributes[$attribute] = $value;
    }

    /**
     * @return array
     */
    public function getCustomAttributes()
    {
        return $this->_attributes;
    }

    public function setCustomAttributes(array $attributes)
    {
        $this->_attributes = $attributes;
    }

    /**
     * Декодирует и устанавливает значения настраиваемых атрибутов из json строки.
     *
     * @param string $json
     */
    public function initCustomAttributes($json)
    {
        $this->_attributes = '{}' !== $json
            ? json_decode($json, true)
            : [];
    }

    /**
     * Кодирует настраиваемые атрибуты в json строку
     *
     * @return string
     */
    public function dumpCustomAttributes()
    {
        return empty($this->_attributes)
            ? '{}'
            : json_encode($this->_attributes, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Очищает настраиваемые атрибуты модели
     */
    public function removeCustomAttributes()
    {
        $this->_attributes = [];
    }

    /**
     * Вынужденная мера, так как события не отрабатывают во время выполнения refresh() модели.
     * toDo: Подумываю что бы вообще запретить пользоваться данным методом во избежание непонятностей.
     *
     * @return bool
     */
    public function refresh()
    {
        if (parent::refresh()) {
            $this->initCustomAttributes($this->getAttribute('Attributes'));

            return true;
        }

        return false;
    }
}