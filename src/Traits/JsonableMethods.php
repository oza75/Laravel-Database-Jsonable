<?php

namespace Oza\DatabaseJsonable\Traits;


use Illuminate\Support\Collection;
use Oza\DatabaseJsonable\Exceptions\InvalidSchema;
use Oza\DatabaseJsonable\Exceptions\ValueMustBeAnArray;

trait JsonableMethods
{

    /**
     * @param $data
     * @return Collection|mixed
     * @throws ValueMustBeAnArray
     */
    private function bootData($data)
    {
        $this->registerMacro();
        $data = $this->castsData($data);
        if (!($data instanceof Collection) && !is_array($data)) throw new ValueMustBeAnArray();
        return $data;
    }

    private function castsData($data)
    {
        $data = is_array($data) ? $data : json_decode($data);
        if (is_null($data) || empty($data)) return collect();
        if (is_object($data)) return collect((array)$data)->recursiveCast();
        if (is_array($data)) return collect($data)->recursiveCast();
        return $data;
    }

    /**
     *
     */
    private function registerMacro()
    {
        Collection::macro('recursiveCast', function () {
            return $this->map(function ($value) {
                if (is_array($value) || is_object($value)) {
                    return collect($value)->recursiveCast();
                }

                return $value;
            });
        });
    }

    /**
     * @param array $args
     * @return mixed
     * @throws InvalidSchema
     */
    private function getNewItem(array $args)
    {
        $this->checkSchema($args);
        if ($this->schema && count($args) === 1) $newItem = $this->getNewItemWithSchemaAndArgs($args);
        elseif ($this->schema) $newItem = $this->getNewItemWithSchema($args);
        else $newItem = $args[0];
        if ($this->timestamps) $newItem = $this->addTimestamps($newItem);
        return $newItem;
    }

    /**
     * @param array $args
     * @throws InvalidSchema
     */
    private function checkSchema(array $args)
    {
        if (!$this->schema && count($args) > 1) {
            throw new InvalidSchema($this->getInvalidSchemaMessage(0, get_class($this->model)));
        }
    }

    private function getInvalidSchemaMessage(int $type, $data)
    {
        $messages = [
            "You pass more than 1 parameters while you have not defined a schema in model {$data}'s jsonnable field. 
          If no schema is defined you must pass an array."
        ];
        return $messages[$type];
    }

    /**
     * @param $args
     * @return array
     */
    private function getNewItemWithSchemaAndArgs($args)
    {
        $newItem = [];
        foreach ($this->schema as $index => $key) {
            $newItem[$key] = $args[0][$key] ?? null;
        }
        return $newItem;
    }

    /**
     * @param $args
     * @return array
     */
    private function getNewItemWithSchema($args)
    {
        $newItem = [];
        foreach ($this->schema as $index => $key) {
            $newItem[$key] = $args[$index] ?? null;
        }
        return $newItem;
    }
    private function addTimestamps(array $item) {
        $item['created_at'] = now();
        $item['updated_at'] = now();
        return $item;
    }
    private function updateTimestamp(array $item) {
        $item['updated_at'] = now();
        return $item;
    }
}