<?php

namespace App\Traits;

use Illuminate\Pagination\LengthAwarePaginator;

trait PaginationTrait
{
    public array $data = [];

    public function setupPagination(LengthAwarePaginator $pagination, string|callable|null $resourceClass = null, array $append = []): self
    {
        $items = $pagination->items();

        if ($resourceClass) {
            if (is_callable($resourceClass)) {
                $items = call_user_func($resourceClass, $items);
            } elseif (class_exists($resourceClass)) {
                $items = new $resourceClass($items);
            } else {
                throw new \InvalidArgumentException("Invalid resource handler provided.");
            }
        }

        $this->data['data'] = $items;
        $this->data['current_page'] = $pagination->currentPage();
        $this->data['last_page']    = $pagination->lastPage();
        $this->data['total']        = $pagination->total();

        foreach ($append as $key => $value) {
            $this->data[$key] = $value;
        }

        return $this;
    }
}
