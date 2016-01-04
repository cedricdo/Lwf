<?php

declare(strict_types=1);

namespace Lwf\Config;

use Lwf\Config\Exception\OutOfBoundsException;

/**
 * Handle a configuration.
 */
class Config
{
    /** @var mixed[] */
    private $data = [];

    /**
     * Get an item of the configuration.
     *
     * @param string $key The key associated to the item
     *
     * @return mixed
     *
     * @throws OutOfBoundsException If $key doesn't exist in configuration
     */
    public function get(string $key)
    {
        if (!isset($this->data[$key])) {
            throw new OutOfBoundsException(sprintf("Key %s doesn't exist", $key));
        }

        return $this->data[$key];
    }
    
    /**
     * Get every items in the configuration.
     * 
     * @return array
     * 
     */
    public function getAll(): array
    {
        return $this->data;
    }
    
    /**
     * Define or modify an item of the configuration.
     *
     * @param string $key   The key to be defined or modified
     * @param mixed  $value The value you want to associate to the key.
     */
    public function set(string $key, $value)
    {
        $this->data[$key] = $value;
    }
    
    /**
     * Remove an item of the configuration.
     * 
     * @param string $key The key to be removed.
     * 
     * @throws OutOfBoundsException Si la clé n'existe pas
     */
    public function remove(string $key)
    {
        if (!isset($this->data[$key])) {
            throw new OutOfBoundsException(sprintf("Key %s doesn't exist", $key));
        }

        unset($this->data[$key]);
    }
    
    /**
     * Remove every items in the configurations.
     */
    public function clear()
    {
        $this->data = [];
    }
    
    /**
     * Check if a key exists in the configuration.
     * 
     * @param string $key The key to be tested
     * 
     * @return bool
     */
    public function has(string $key)
    {
        return isset($this->data[$key]);
    }
    
    /**
     * Merge two Config object.
     * 
     * @param Config $config The Config to be merged with.
     */
    public function merge(Config $config)
    {
        $this->data = array_merge($this->data, $config->data);
    }
}
