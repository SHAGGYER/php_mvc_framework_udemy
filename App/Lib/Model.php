<?php

namespace App\Lib;

class Model extends \stdClass implements \JsonSerializable {
    private array $attributes = [];
    private ?string $query = null;
    public array $hidden = [];
    public string $table = "";
    public QueryBuilder $queryBuilder;

    public function __construct()
    {
        $this->queryBuilder = new QueryBuilder($this);
    }

    public function toArray() {
        $attrs = $this->getAttributes();

        foreach ($attrs as $key => $value) {
            if ($value instanceof Model) {
                $attrs[$key] = $value->toArray();
            }
        }

        return $attrs;
    }

    public function __set($name, $value) {
        $this->attributes[$name] = $value;
    }

    public function __get($name) {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    public function getAttributes(): array {
        return $this->attributes;
    }

    public function getQuery(): string {
        return $this->query;
    }

    public function jsonSerialize(): array
    {
        return array_filter($this->attributes, function($value, $key) {
            return !in_array($key, $this->hidden);
        }, ARRAY_FILTER_USE_BOTH);
    }

    public static function with(array $relations): QueryBuilder {
        $model = new static();
        return $model->queryBuilder->with($relations)->select();
    }

    public static function paginate(int $page = 1, int $limit = 10) {
        $obj = new static();

        return $obj->queryBuilder->paginate($page, $limit);
    }

    public static function where(array $conditions): QueryBuilder {
        $model = new static();
        $model->queryBuilder->where($conditions);
        return $model->queryBuilder;
    }

    public static function findById(int $id): ?Model {
        $model = new static();
        $result = $model->where([
            ["id", "=", $id]
        ])->first();
        return $result;
    }

    public static function all() {
        $obj = new static();
        return $obj->queryBuilder->select()->get();
    }

    public static function delete() {
        $obj = new static();
        return $obj->queryBuilder->delete();
    }

    public function save() {
        $this->queryBuilder->save();
    }

    public function belongsTo(string $model, string $foreignKey, string $localKey) {
        $model = new $model;
        return $model->queryBuilder->where([
            [$localKey, "=", $this->{$foreignKey}]
        ])->first();
    }

    public function hasOne(string $model, string $foreignKey, string $localKey = "id"): Model {
        $model = new $model;
        return $model->queryBuilder->where([
            [$foreignKey, "=", $this->{$localKey}]
        ])->first();
    }

    public function belongsToMany(string $model, string $pivot_table, string $pivot_one, string $pivot_two): Collection {
        $qb = new QueryBuilder(new static());

        $results = $qb->from($pivot_table)->where([
            [$pivot_one, "=", $this->attributes["id"]]
        ])->get();


        $collection = [];
        foreach ($results->toArray() as $result) {
            $obj = new $model();
            $collection[] = $obj->queryBuilder->where([
                ["id", "=", $result[$pivot_two]]
            ])->first();
        }

        return new Collection($collection);
    }
}