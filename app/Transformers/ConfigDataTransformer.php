<?php

namespace App\Transformers;

use League\Fractal\Serializer\ArraySerializer ;

class ConfigDataTransformer extends ArraySerializer
{
    /**
     * Serialize a collection to a plain array.
     *
     * @param string $resourceKey
     * @param array  $data
     *
     * @return array
     */
    public function collection($resourceKey, array $data)
    {
        return [$resourceKey ?: 'data' => $data];
    }
}
