<?php

declare(strict_types = 1);

namespace System\Http;

use Lwf\Http\Exception\LogicException;

/**
 * Represents a text/event-stream HTTP response
 */
class StreamedResponse extends Response
{
    /** @var callable */
    private $callback;
    /** @var bool  */
    private $streamed;
    
    /**
     * Constructor.
     *
     * @param Callable $callback A callback which will generate the stream of data
     * @param int      $code     The HTTP code of the response
     * @param array    $headers  The headers of the response. Content-Type and Cache-Control will be ignored
     */
    public function __construct(Callable $callback = null, int $code = 200, array $headers = [])
    {
        $headers['Content-Type'] = 'text/event-stream';
        $headers['Cache-Control'] = 'no-cache';
        parent::__construct('', $code, $headers);
        
        $this->streamed = false;
        if (null !== $callback) {
            $this->setCallback($callback);
        }
    }
    
    /**
     * 
     * Set the callback which will generate the stream
     * 
     * @param Callable $callback
     */
    public function setCallback(Callable $callback)
    {
        $this->callback = $callback;
    }
    
    
    /**
     * {@inheritdoc}
     *
     * @throws LogicException If no callback has been set
     */
    public function sendBody()
    {
        if ($this->streamed) {
            return;
        }

        if (null === $this->callback) {
            throw new LogicException("The response callback must be defined");
        }

        $this->streamed = true;
        call_user_func($this->callback);
    }
    
    /**
     * Should not be used
     *
     * @throws LogicException Everytime
     */
    public function setBody($body)
    {
        throw new LogicException("You can not define a body on a StreamedResponse object");
    }
    
    /**
     * Should not be used
     *
     * @throws LogicException Everytime
     */
    public function addBody($body)
    {
        throw new LogicException("You can not define a body on a StreamedResponse object");
    }
}
