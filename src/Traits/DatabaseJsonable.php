<?php


namespace Oza\DatabaseJsonable\Traits;


use Oza\DatabaseJsonable\Jsonable;

trait DatabaseJsonable
{
    /**
     * Transform jsonable field
     *
     * @param $name
     * @return Jsonable
     * @throws \Oza\DatabaseJsonable\Exceptions\ValueMustBeAnArray
     */
    public function __get($name)
    {
        $data = parent::__get($name);
        if ($this->hasJsonableKey($name))
            $data = new Jsonable($data, $name, $this, $this->getSchema($name), $this->jsonableTimestamps ?? false);
        return $data;
    }

    /**
     * Put jsonable field in json formats to db
     *
     * @param array $attributes
     * @return
     *
     */
    public static function create(array $attributes = [])
    {
        foreach ((new self)->jsonable as $key => $item) {
            $key = (new self)->getJsonableKey($key, $item);
            $attributes = (new self)->encodeJsonableField($key, $attributes);
        }
        return static::query()->create($attributes);
    }

    /**
     * Get jsonable schema
     *
     * @param string $key
     * @return null
     */
    private function getSchema(string $key): ?array
    {
        if (!isset($this->jsonable[$key])) return null;
        return is_array($this->jsonable[$key]) ? $this->jsonable[$key] : null;
    }

    /**
     * Determines if a jsonable field has key
     *
     * @param string $key
     * @return bool
     */
    private function hasJsonableKey(string $key): bool
    {
        return collect($this->jsonable)->contains($key) || collect($this->jsonable)->has($key);
    }

    /**
     * Get jsonable key
     *
     * @param $key
     * @param $item
     * @return mixed
     */
    private function getJsonableKey($key, $item)
    {
        return is_array($item) ? $key : $item;
    }

    /**
     * Remove fields that are not in schema field
     *
     * @param string $key
     * @param array $attributes
     * @return array|null
     */
    private function removeNotInSchema(string $key, array $attributes = []): ?array
    {
        if (($schema = $this->getSchema($key)) && (isset($this->strictJsonableSchema) && $this->strictJsonableSchema)) {
            $attributes[$key] = collect($attributes[$key])->reject(function ($value, $key) use ($schema) {
                return !collect($schema)->contains($key);
            });
        }
        return $attributes;
    }

    /**
     * Encode Jsonable fields to json format
     *
     * @param string $key
     * @param array $attributes
     * @return array|null
     */
    private function encodeJsonableField(string $key, array $attributes = [])
    {
        if (collect($attributes)->contains($key) || collect($attributes)->has($key)) {
            if (!is_string($attributes[$key])) {
                $attributes = $this->removeNotInSchema($key, $attributes);
                $attributes = $this->addAdditionalField($key, $attributes);
                $attributes[$key] = json_encode([$attributes[$key]]);
            }
        }
        return $attributes;
    }

    /**
     * Add additional field like timestamps
     *
     * @param string $key
     * @param array $attributes
     * @return array|null
     */
    private function addAdditionalField(string $key, array $attributes = []) : ?array
    {
        $attributes[$key]['id'] = 1;
        if (isset($this->jsonableTimestamps) && $this->jsonableTimestamps) {
            $attributes[$key]['created_at'] = now();
            $attributes[$key]['updated_at'] = now();
        }
        return $attributes;
    }
}