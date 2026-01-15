<?php

namespace App\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get the model instance.
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get all records with optional conditions.
     */
    public function all($conditions = [])
    {
        $query = $this->model->newQuery();

        foreach ($conditions as $field => $value) {
            if (is_array($value) && isset($value['comparison'])) {
                $this->buildQuery($query, $field, $value['comparison'], $value['value']);
            } elseif (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        return $query->get();
    }

    /**
     * Find a record by ID.
     */
    public function find($id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Find the first record that matches conditions.
     */
    public function findByConditions(array $conditions)
    {
        $query = $this->model->newQuery();

        foreach ($conditions as $field => $value) {
            if (is_array($value) && isset($value['comparison'])) {
                $this->buildQuery($query, $field, $value['comparison'], $value['value']);
            } elseif (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        return $query->first();
    }

    /**
     * Get records by conditions with comparison operators.
     */
    public function getByConditions(array $conditions = [], bool $asQuery = false)
    {
        $query = $this->model->newQuery();

        foreach ($conditions as $field => $value) {
            if (is_array($value) && isset($value['comparison'])) {
                $this->buildQuery($query, $field, $value['comparison'], $value['value']);
            } elseif (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        return $asQuery ? $query : $query->get();
    }

    /**
     * Get the first record from the model.
     */
    public function first()
    {
        return $this->model->first();
    }

    /**
     * Create a new record.
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing record by ID.
     */
    public function update(array $data, $id)
    {
        $record = $this->find($id);
        $record->update($data);
        $record->touch(); // Force update timestamps
        return $record;
    }

    /**
     * Delete a record by ID.
     */
    public function delete($id)
    {
        $record = $this->find($id);
        return $record->delete();
    }

    /**
     * Update an existing record or create a new one.
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    /**
     * Delete records by conditions.
     */
    public function deleteByConditions(array $conditions)
    {
        $query = $this->model->newQuery();

        if (Arr::isAssoc($conditions)) {
            foreach ($conditions as $column => $value) {
                if (is_array($value)) {
                    $query->whereIn($column, $value);
                } else {
                    $query->where($column, $value);
                }
            }
            return $query->delete();
        }

        foreach ($conditions as $condition) {
            if (!is_array($condition)) {
                continue;
            }

            $count = count($condition);

            if ($count === 2) {
                [$column, $value] = $condition;
                $query->where($column, $value);
            } elseif ($count === 3) {
                [$column, $operator, $value] = $condition;

                $op = strtolower(trim($operator));

                if ($op === 'in') {
                    if (!empty($value) && is_array($value)) {
                        $query->whereIn($column, $value);
                    }
                } elseif ($op === 'not in') {
                    if (!empty($value) && is_array($value)) {
                        $query->whereNotIn($column, $value);
                    }
                } else {
                    $query->where($column, $operator, $value);
                }
            }
        }

        return $query->delete();
    }

    /**
     * Return a query builder for relationships or custom queries.
     */
    public function loadRelationship(array $relationships = [])
    {
        return !empty($relationships) ? $this->model->with($relationships) : $this->model->query();
    }

    /**
     * Load relationships with withTrashed support.
     */
    public function loadRelationshipWithTrashed(array $relationships = [])
    {
        if (empty($relationships)) {
            return $this->model->query();
        }

        $relationshipsWithTrashed = [];
        $processedParents = [];

        foreach ($relationships as $relation) {
            if (strpos($relation, '.') !== false) {
                $parts = explode('.', $relation);
                $parentRelation = $parts[0];
                $childRelation = implode('.', array_slice($parts, 1));

                if (!isset($processedParents[$parentRelation])) {
                    $processedParents[$parentRelation] = [];
                }
                $processedParents[$parentRelation][] = $childRelation;
            } else {
                $relationshipsWithTrashed[$relation] = function ($query) {
                    $query->withTrashed();
                };
            }
        }

        foreach ($processedParents as $parent => $children) {
            $relationshipsWithTrashed[$parent] = function ($query) use ($children) {
                $query->withTrashed();
                foreach ($children as $child) {
                    $query->with([$child => function ($subQuery) {
                        $subQuery->withTrashed();
                    }]);
                }
            };
        }

        return $this->model->with($relationshipsWithTrashed);
    }

    /**
     * Get searchable fields from model if defined.
     */
    public function getSearchableFields(): array
    {
        return method_exists($this->model, 'getSearchableFields') ? $this->model::getSearchableFields() : [];
    }

    /**
     * Get fulltext searchable fields from model if defined.
     */
    public function getFulltextFields(): array
    {
        return method_exists($this->model, 'getFulltextFields') ? $this->model::getFulltextFields() : [];
    }

    /**
     * Upsert multiple records.
     */
    public function upsertMany(array $values, $uniqueBy, array $updateFields): int
    {
        return $this->model->upsert($values, $uniqueBy, $updateFields);
    }

    /**
     * Delete records where column not in values.
     */
    public function deleteWhereNotIn(string $column, array $values, array $extraConditions = []): int
    {
        $query = $this->model->newQuery();

        if (!empty($values)) {
            $query->whereNotIn($column, $values);
        }

        if (!empty($extraConditions)) {
            $query->where($extraConditions);
        }

        return $query->delete();
    }

    /**
     * Delete multiple records by IDs.
     */
    public function deleteMany(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        return $this->model->whereIn('id', $ids)->delete();
    }

    /**
     * Build query with comparison operators.
     */
    protected function buildQuery($query, $attr, $comparison, $value)
    {
        return match ($comparison) {
            'eq' => $query->where($attr, $value),
            'gt' => $query->where($attr, '>', $value),
            'gte' => $query->where($attr, '>=', $value),
            'lt' => $query->where($attr, '<', $value),
            'lte' => $query->where($attr, '<=', $value),
            'in' => $query->whereIn($attr, is_array($value) ? $value : explode(',', $value)),
            'not_in' => $query->whereNotIn($attr, is_array($value) ? $value : explode(',', $value)),
            'startswith' => $query->where($attr, 'like', "$value%"),
            'endswith' => $query->where($attr, 'like', "%$value"),
            'contains' => $query->where($attr, 'like', "%$value%"),
            'isnull' => $value ? $query->whereNull($attr) : $query->whereNotNull($attr),
            default => $query,
        };
    }
}
