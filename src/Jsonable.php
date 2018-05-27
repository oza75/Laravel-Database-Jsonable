<?php


namespace Oza\DatabaseJsonable;


use Illuminate\Database\Eloquent\Model;
use Oza\DatabaseJsonable\Exceptions\InvalidSchema;
use Oza\DatabaseJsonable\Exceptions\ValueMustBeAnArray;
use Oza\DatabaseJsonable\Traits\JsonableMethods;

class Jsonable
{
    use JsonableMethods;

    public $items;
    /**
     * @var string
     */
    private $field;
    /**
     * @var Model
     */
    private $model;

    private $schema;
    /**
     * @var bool
     */
    private $timestamps;
	
    private $id;			
    
    /**
     * Jsonable constructor.
     *
     * @param $data
     * @param string $field
     * @param Model $model
     * @param array|null $schema
     * @param bool $timestamps
     * @throws ValueMustBeAnArray
     * @author Aboubacar Ouattara
     *
     * @license MIT
     */
    public function __construct($data, string $field, Model $model, ?array $schema = null, bool $timestamps = false, bool $id = true)
    {
        $this->items = $this->bootData($data);
        $this->field = $field;
        $this->model = $model;
        $this->schema = $schema;
        $this->timestamps = $timestamps;
	$this->id = $id;
    }


    /**
     * Value when class its call like a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Add an item
     *
     * @param array $args
     * @return int
     * @throws InvalidSchema
     */
    public function add(...$args)
    {
        $newItem = $this->getNewItem($args);
        if($this->id) $newItem['id'] = $this->getId();
        $this->items->push($newItem);
        $this->save();
        return $newItem['id'];
    }

    /**
     * Change a field of item
     *
     * @param int $id
     * @param string $field
     * @param $value
     * @return \Illuminate\Support\Collection|mixed
     */
    public function change(int $id, string $field, $value)
    {
        $this->items = $this->items->map(function ($item) use ($id, $field, $value) {
            if ($item['id'] === $id) {
                if($field !== 'id') $item[$field] = $value;
                if ($this->timestamps) $item = $this->updateTimestamp($item->all());
                return $item;
            }
        });
        $this->save();
        return $this->items->firstWhere('id', $id);
    }

    /**
     * Update an item
     *
     * @param int $id
     * @param array $newData
     * @return \Illuminate\Support\Collection|mixed
     */
    public function update(int $id, array $newData)
    {
        $this->items = $this->items->map(function ($item) use ($id, $newData) {
            if ($item['id'] === $id) {
                foreach ($newData as $key => $value) {
                    if ($key !== 'id') $item[$key] = $value;
                }
                if ($this->timestamps) $item = $this->updateTimestamp($item->all());
                return $item;
            }
        });

        $this->save();
        return $this->items->firstWhere('id', $id);
    }

    /**
     * Remove an item from items
     *
     * @param int $id
     * @return bool
     */
    public function remove(int $id)
    {
        $this->items = $this->items->reject(function ($item) use ($id) {
            return $item['id'] === $id;
        });
        $this->save();
        return true;
    }

    /**
     * Save to database
     */
    private function save()
    {
        $this->model->{$this->field} = $this->toJson();
        $this->model->save();
    }

    /**
     * Get an item by its id
     *
     * @param int $id
     * @return \Illuminate\Support\Collection|mixed
     */
    public function get(int $id)
    {
        return $this->items->firstWhere('id', $id);
    }

    /**
     * Get id for next item
     *
     * @return int
     */
    private function getId()
    {
        return empty($this->items->all()) ? 1 : $this->items->last()['id'] + 1;
    }

    /**
     * Get all items
     *
     * @return array
     */
    public function all()
    {
        return $this->items->all();
    }

    /**
     * Get first item
     *
     * @return mixed
     */
    public function first()
    {
        return $this->items->first();
    }

    /**
     * Get last item
     *
     * @return mixed
     */
    public function last()
    {
        return $this->items->last();
    }

    /**
     * Get items in json formats
     *
     * @return string
     */
    public function toJson() {
        return json_encode($this->items->all());
    }

}
