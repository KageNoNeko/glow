<?php

namespace Glow\Http\Controllers;

use BadMethodCallException;
use Illuminate\Http\Request;

trait Paginatable
{

    use Listable;

    /**
     * @return array
     */
    protected function listDefaults() {

        return [
            'order_by' => $this->listOrderColumns()[ 0 ],
            'order_dir' => 'asc',
            'per_page' => 10,
            'page' => 1,
        ];
    }

    /**
     * @return array
     */
    protected function listRules() {

        return [
            'order_by' => 'nullable|string|in:' . implode(",", $this->listOrderColumns()),
            'order_dir' => 'nullable|string|in:asc,ASC,desc,DESC',
            'per_page' => 'nullable|integer|min:1',
            'page' => 'nullable|integer|min:1',
        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    protected function listSlice(Request $request) {

        $params = $this->listParams($request);
        $query = $this->listDefaultQuery()
                      ->orderBy($params[ 'order_by' ], $params[ 'order_dir' ]);

        return $query->paginate($params[ 'per_page' ], ['*'], 'page', $params[ 'page' ]);
    }
}
