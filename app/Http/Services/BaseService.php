<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class BaseService
{
    protected $repository;

    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    protected function loadRelationship(array $relationships = [])
    {
        return $this->repository->loadRelationship($relationships);
    }

    protected function getQuery($conditions = [])
    {
        $relationships = Arr::get($conditions, 'with', []);
        if (is_string($relationships)) {
            $relationships = array_map('trim', explode(',', $relationships));
        }
        if (!is_array($relationships)) {
            $relationships = [];
        }
        $with_trashed = Arr::get($conditions, 'with_trashed', false);
        if (is_string($with_trashed)) {
            $with_trashed = filter_var($with_trashed, FILTER_VALIDATE_BOOLEAN);
        }
        if ($with_trashed && !empty($relationships)) {
            $query = $this->repository->loadRelationshipWithTrashed($relationships);
        } else {
            $query = $this->loadRelationship($relationships);
        }
        unset($conditions['with']);
        if ($with_trashed) {
            $query = $query->withTrashed();
        }
        unset($conditions['with_trashed'], $conditions['detail_with_trashed']);

        $select = Arr::get($conditions, 'select', []);
        if (!empty($select)) {
            $query->select($select);
        }
        unset($conditions['select']);

        $search = trim(Arr::get($conditions, 'search', ''));
        unset($conditions['search'], $conditions['page'], $conditions['per_page'], $conditions['limit']);

        $order_by_arr = Arr::get($conditions, 'order_by', []);
        unset($conditions['order_by']);

        $filter_arr = Arr::get($conditions, 'filter', []);
        unset($conditions['filter']);

        $group_by_arr = Arr::get($conditions, 'group_by', []);
        unset($conditions['group_by']);

        // 1. Search
        if ($search) {
            $searchableFields = $this->repository->getSearchableFields();
            $fulltextFields = $this->repository->getFulltextFields();
            if (!empty($searchableFields)) {
                if (!empty($fulltextFields)) {
                    $query->where(function ($q) use ($search, $fulltextFields) {
                        $q->whereRaw(
                            "MATCH(" . implode(',', $fulltextFields) . ") AGAINST(? IN BOOLEAN MODE)",
                            [$search]
                        );
                    });
                } else {
                    $query->where(function ($q) use ($search, $searchableFields) {
                        foreach ($searchableFields as $field) {
                            $q->orWhere($field, 'like', '%' . $search . '%');
                        }
                    });
                }
            }
        }

        // 2. Filter
        foreach ($filter_arr as $comparison => $filters) {
            $relationGroup = [];
            foreach ($filters as $attr => $value) {
                if (Str::contains($attr, '.')) {
                    $parts = explode('.', $attr);
                    $field = array_pop($parts);
                    $relation = implode('.', $parts);
                    $relationGroup[$relation][] = compact('field', 'value', 'comparison');
                } else {
                    $this->buildQuery($query, $attr, $comparison, $value);
                }
            }

            foreach ($relationGroup as $relation => $conds) {
                $query->whereHas($relation, function ($q) use ($conds, $with_trashed) {
                    if ($with_trashed) {
                        $q->withTrashed();
                    }
                    foreach ($conds as $c) {
                        $this->buildQuery($q, $c['field'], $c['comparison'], $c['value']);
                    }
                });
            }
        }

        // 3. Group By
        if (!empty($group_by_arr)) {
            if (is_array($group_by_arr)) {
                $query->groupBy($group_by_arr);
            } else {
                $query->groupBy([$group_by_arr]);
            }
        }

        // 4. Order
        foreach ($order_by_arr as $attr => $dir) {
            $is_relation = Str::contains($attr, '.');
            if ($is_relation) {
                $key_arr = explode('.', $attr);
                $attr = array_pop($key_arr);
                $relation = implode('.', $key_arr);
                $query->with([$relation => fn($q) => $q->orderBy($attr, $dir)]);
            } else {
                $query->orderBy($attr, $dir);
            }
        }

        return $query;
    }

    public function getByConditions($conditions = [])
    {
        $limit = Arr::get($conditions, 'limit');
        $per_page = Arr::get($conditions, 'per_page', 10);

        $query = $this->getQuery($conditions);
        if ($limit) {
            return $query->paginate($limit)->appends(request()->query());
        }
        if ((int)$per_page === -1) {
            $items = $query->get();
            $total = $items->count();

            return new LengthAwarePaginator(
                $items,
                $total,
                $total ?: 1,
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        }

        return $query->paginate($per_page)->appends(request()->query());
    }

    public function findByConditions($conditions = [])
    {
        $detail_with_trashed = Arr::get($conditions, 'detail_with_trashed', false);
        if ($detail_with_trashed) {
            $mainConditions = $conditions;
            unset($mainConditions['with']);
            $mainRecord = $this->getQuery($mainConditions)->first();
            if (!$mainRecord) return null;
            $relationships = Arr::get($conditions, 'with', []);
            if (!empty($relationships)) {
                if ($mainRecord->deleted_at) {
                    $relationshipsWithTrashed = [];
                    foreach ($relationships as $relation) {
                        if (strpos($relation, '.') !== false) {
                            $parts = explode('.', $relation);
                            $nestedRelation = $parts[0];
                            $childRelation = implode('.', array_slice($parts, 1));
                            $relationshipsWithTrashed[$nestedRelation] = function ($query) use ($childRelation) {
                                $query->withTrashed()->with([$childRelation => fn($q) => $q->withTrashed()]);
                            };
                        } else {
                            $relationshipsWithTrashed[$relation] = fn($q) => $q->withTrashed();
                        }
                    }
                    $mainRecord->load($relationshipsWithTrashed);
                } else {
                    $mainRecord->load($relationships);
                }
            }

            return $mainRecord;
        }

        return $this->getQuery($conditions)->first();
    }

    public function findAllByConditions($conditions = [])
    {
        return $this->getQuery($conditions)->get();
    }

    public function store($data)
    {
        $obj = $this->repository->create($data);
        return $this->loadRelationship()->where('id', $obj->id)->first();
    }

    public function update(array $data, $obj)
    {
        if (!$obj) {
            return null;
        }
        $this->repository->update($data, $obj->id);
        $obj->refresh(); // Refresh to get latest timestamps
        return $obj;
    }

    public function destroy($obj)
    {
        $this->repository->delete($obj->id);
        return $obj;
    }

    public function updateOrCreate($data)
    {
        return $this->repository->updateOrCreate(['id' => Arr::get($data, 'id')], $data);
    }

    private function buildQuery($query, $attr, $comparison, $value)
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

    public function getPagination($paginator): array
    {
        $from = ($paginator->currentPage() - 1) * $paginator->perPage() + 1;
        $to = $from + $paginator->count() - 1;
        $total = $paginator->total();

        return [
            'to' => $to,
            'from' => $from,
            'total' => $total,
            'per_page' => $paginator->perPage(),
            'links' => $paginator->linkCollection(),
            'current_page' => $paginator->currentPage(),
        ];
    }
}
