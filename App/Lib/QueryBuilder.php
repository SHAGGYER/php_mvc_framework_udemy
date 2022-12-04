<?php

namespace App\Lib;

class QueryBuilder {
    public ?string $table;

    private bool $has_select = false;
    private \PDO $pdo;
    private array $values = [];
    private array $attributes = [];
    private array $relations = [];
    private ?Model $model;

    private ?string $paginationPreQuery = null;
    private ?string $paginationPostQuery = null;

    public function __construct(?Model $model = null)
    {
        $this->query = "";
        $this->pdo = Database::$pdo;
        $this->model = $model;
        if ($model) {
            $this->table = $model->table;
        }
    }

    public function from(string $table): QueryBuilder {
        $this->table = $table;
        return $this;
    }

    public function with(array $relations): QueryBuilder {
        foreach ($relations as $relation) {
            if (is_string($relation)) {
                $parts = explode(".", $relation);
                $this->relations[] = $parts;
            }
        }
        return $this;
    }

    public function save() {
        $this->attributes = $this->model->getAttributes();
        if ($this->model->timestamps) {
            $this->appendTimestamps();
        }
        
        if (isset($this->attributes["id"])) {
            $this->update()->where([
                ["id", "=", $this->attributes["id"]]
            ])->execute();
        } else {
            $this->insert()->execute();
            $this->model->id = $this->pdo->lastInsertId();
        }
    }

    public function insert(): QueryBuilder {
        $this->query = "INSERT INTO {$this->table} (";
        $this->query .= implode(", ", array_keys($this->attributes));
        $this->query .= ") VALUES (";
        $this->query .= implode(", ", array_fill(0, count($this->attributes), "?"));
        $this->query .= ")";
        $this->values = array_values($this->attributes);

        return $this;
    }

    public function update(): QueryBuilder {
        $this->attributes = $this->model->getAttributes();

        $this->query = "UPDATE {$this->table} SET ";
        $this->query .= implode(", ", array_map(function($key) {
            return "{$key} = ?";
        }, array_keys($this->attributes)));
        $this->values = array_values($this->attributes);
        return $this;
    }

    public function execute(): bool {
        try {
            $stmt = $this->pdo->prepare($this->query);
            $stmt->execute($this->values);
            return true;
        } catch (\PDOException $e) {
            Response::json([
                "error" => $e->getMessage()
            ], 500);
            return false;
        }
    }

    public function select(string $columns = "*") {
        $this->query = "SELECT $columns" . " FROM " . $this->table;
        $this->has_select = true;
        return $this;
    }

    public function paginate(int $page, int $limit) {
        if (!$this->has_select) {
            $this->select();
        }

        $this->paginationPreQuery = $this->query;

        $offset = ($page - 1) * $limit;
        $this->paginationPostQuery .= " LIMIT $limit OFFSET $offset";
        return $this;
    }

    public function orWhere(array $conditions): QueryBuilder {
        $has_where = false;
        $this->query .= " OR ";
       
        foreach ($conditions as $where) {
            if ($has_where) {
                $this->query .= " AND ";
            }
            $this->query .= "{$where[0]} {$where[1]} ?";
            $this->values[] = $where[2];
            $has_where = true;
        }


        if ($this->paginationPreQuery) {
            $this->paginationPreQuery = $this->query;
        }

        return $this;
    }

    public function where($data) {
        $has_where = false;

        foreach ($data as $where) {
            $column = $where[0];
            $operator = $where[1];
            $value = $where[2];

            $this->values[] = $value;

            $sql = "";

            if (!$this->has_select) {
                $sql .= "SELECT * FROM " . $this->table;
                $this->has_select = true;
            }


            if (!$has_where) {
                $sql .= " WHERE " . $column . " " . $operator . " ?";
                $has_where = true;
            } else {
                $sql .= " AND " . $column . " " . $operator . " ?";
            }

        }

        $this->query .= $sql;

        if ($this->paginationPreQuery) {
            $this->paginationPreQuery = $this->query;
        }
        return $this;
    }

    public function first(): ?Model {
        $this->query .= " LIMIT 1";

        $stmt = $this->pdo->prepare($this->query);
        $stmt->execute($this->values);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row) {
            foreach ($row as $column => $value) {
                $this->model->{$column} = $value;
            }

            $this->parseRelations();
            return $this->model;
        }

        return null;
    }

    public function get(): Collection {
        if ($this->paginationPostQuery) {
            $this->query .= $this->paginationPostQuery;
        }

        $stmt = $this->pdo->prepare($this->query);
        $stmt->execute($this->values);

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $models = [];

        foreach ($rows as $row) {
            $model = clone $this->model;
            foreach ($row as $column => $value) {
                $model->{$column} = $value;
            }

            $model->queryBuilder->model = $model;
            $model->queryBuilder->parseRelations();
            $models[] = $model;
        }

        if ($this->paginationPreQuery) {
            $stmt = $this->pdo->prepare($this->paginationPreQuery);
            $stmt->execute($this->values);
            $total = $stmt->rowCount();

            return new Collection(["data" => $models, "total" => $total]);
        }

        return new Collection($models);
    }

    public function orderBy($column, $order) {
        $this->query .= " ORDER BY " . $column . " " . $order;
        return $this;
    }

    private function appendTimestamps() {
        $this->attributes["created_at"] = date("Y-m-d H:i:s");
        $this->attributes["updated_at"] = date("Y-m-d H:i:s");
    }

    public function delete() {
        $this->query = "DELETE FROM " . $this->table;
        $this->has_select = true;
        return $this;
    }

    public function parseRelations() {
        foreach($this->relations as $relation => $parts) {
            $this->recursiveRelations($this->model, $parts);
        }
    }

    public function recursiveRelations($model, $parts) {
        $relation = array_shift($parts);
        if ($model) {
            $model->{$relation} = $model->{$relation}();
            if (count($parts) > 0 && !empty($model)) {
                $this->recursiveRelations($model->{$relation}, $parts);
            }
        }
    
    }

    public function getQuery(): string {
        return $this->query;
    }
}