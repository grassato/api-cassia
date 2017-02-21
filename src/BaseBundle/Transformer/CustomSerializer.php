<?php

namespace BaseBundle\Transformer;

use League\Fractal\Serializer\ArraySerializer;

class CustomSerializer extends ArraySerializer
{
    public function collection($resourceKey, array $data)
    {
        if ($resourceKey === null) {
            return $data;
        }

        return $resourceKey ? [$resourceKey => $data] : ['data' => $data];
    }

    public function item($resourceKey, array $data, $key = 'data')
    {
        if ($resourceKey === false) {
            return $data;
        }

        return array($resourceKey ?: $key => $data);
    }
}
