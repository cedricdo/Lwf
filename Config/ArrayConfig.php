<?php

declare(strict_types=1);

namespace Lwf\Config;

/**
 * Implement a Config object which is constructed from an array.
 */
class ArrayConfig extends Config
{
    /**
     * Constructor
     * 
     * @param array $data The array which will be used to build the configuration
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }
}
