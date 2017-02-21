## In your controller

```php

   $qb = $this->getManager()->getRepository()->getCostCenterQueryBuilder();
        $paginatedCollection = $this->get('api_pagination_factory')
            ->createCollection($qb, $request, 'get_cost_center');
            
```            