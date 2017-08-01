<?php

namespace Glow\Http\Controllers;

use BadMethodCallException;
use Illuminate\Http\Request;

trait Listable
{

    protected function listOrderColumns() {

        return [];
    }

    /**
     * @return array
     */
    protected function listDefaults() {

        return [
            'order_by' => $this->listOrderColumns()[ 0 ],
            'order_dir' => 'asc',
            'offset' => 0,
            'limit' => 10,
        ];
    }

    /**
     * @return array
     */
    protected function listRules() {

        return [
            'order_by' => 'nullable|string|in:' . implode(",", $this->listOrderColumns()),
            'order_dir' => 'nullable|string|in:asc,ASC,desc,DESC',
            'offset' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1',
        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    protected function listParams(Request $request) {

        $rules = $this->listRules();

        $this->validate($request, $rules);

        return array_merge($this->listDefaults(), $request->intersect(array_keys($rules)));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function listDefaultQuery() {

        throw new BadMethodCallException("Should be implemented in class");
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    protected function listSlice(Request $request) {

        $params = $this->listParams($request);
        $query = $this->listDefaultQuery()
                      ->orderBy($params['order_by'], $params['order_dir']);

        return [
            "total" => $query->count(),
            "list" => $query->skip($params[ 'offset' ])
                            ->take($params[ 'limit' ])
                            ->get(),
        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    protected function listPaginate(Request $request) {

        $params = $this->listParams($request);
        $query = $this->listDefaultQuery()
                      ->orderBy($params['order_by'], $params['order_dir']);

        return $query->paginate();
    }
}
