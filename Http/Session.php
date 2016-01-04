<?php

declare(strict_types=1);

namespace Lwf\Http;

use Lwf\Http\Exception\LogicException;
use Lwf\Http\Exception\RuntimeException;
use Lwf\Http\Exception\OutOfBoundsException;

/**
 * Represent a session.
 */
class Session
{
    const AUTO_START = true;
    const NOTIFICATION_ERROR = 'error';
    const NOTIFICATION_INFO = 'info';
    const NOTIFICATION_WARNING = 'warning';
    const NOTIFICATION_SUCCESS = 'success';
    const NOTIFICATION_LEVEL = [
        self::NOTIFICATION_INFO,
        self::NOTIFICATION_ERROR,
        self::NOTIFICATION_SUCCESS,
        self::NOTIFICATION_WARNING
    ];

    /** @var bool  */
    private $started;
    /** @var mixed  */
    private $default;


    /**
     * Constructor
     *
     * @param bool $autoStart True if the session has to start immediately
     */
    public function __construct(bool $autoStart = false)
    {
        $this->started = false;
        $this->setDefault(null);

        if ($autoStart) {
            $this->start();
        }
    }
    
    /**
     * Start the session
     *
     * @throws RuntimeException If the session fails to start
     */
    public function start()
    {
        if ($this->started) {
            return;
        }

        if (!session_start()) {
            throw new RuntimeException("Session start has failed");
        }

        $this->started = true;
    }
    
    /**
     * Set the default value which will be returned if a key does not exist
     * 
     * @param mixed $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }
    
    /**
     * Get the default value which will be returned if a key does not exists
     * 
     * @return mixed 
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Get every data in the session
     *
     * @return mixed[]
     *
     * @throws LogicException If the session isn't running
     */
    public function getAll(): array
    {
        if (!$this->started) {
            throw new LogicException("Session has to be started");
        }

        return $_SESSION;
    }

    /**
     * Get an item from the session
     * 
     * @param string $key The key of the item.
     * 
     * @return mixed The item or the default value if the item does not exist
     *
     * @throws LogicException If the session isn't running
     */
    public function get(string $key)
    {
        if (!$this->started) {
            throw new LogicException("Session has to be started");
        }
        
        return $_SESSION[$key] ?? $this->default;
    }
    
    /**
     * Set or modify an item in the session
     *
     * @param string $key   The key of the item
     * @param mixed  $value The value of the item
     *
     * @throws LogicException If the session isn't running
     */
    public function set(string $key, $value)
    {
        if (!$this->started) {
            throw new LogicException("Session has to be started");
        }

        $_SESSION[$key] = $value;
    }
    
    /**
     * Remove an item from the session
     * 
     * @param string $key The key of the item
     *
     * @throws LogicException If the session isn't running
     */
    public function remove(string $key)
    {
        if (!$this->started) {
            throw new LogicException("Session has to be started");
        }

        if(isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    /**
     * Remove every items in the session
     *
     * @throws LogicException If the session isn't running
     */
    public function clear()
    {
        if (!$this->started) {
            throw new LogicException("Session has to be started");
        }
        $_SESSION = [];
    }
    
    /**
     * test if a key is defined in the session
     * 
     * @param string $key The key to test
     * 
     * @return bool
     *
     * @throws LogicException If the session isn't running
     */
    public function has(string $key): bool
    {
        if (!$this->started) {
            throw new LogicException("Session has to be started");
        }

        return isset($_SESSION[$key]);
    }
    
    /**
     * Get the session id
     * 
     * @return string
     *
     * @throws LogicException If the session isn't running
     */
    public function getId(): string
    {
        if (!$this->started) {
            throw new LogicException("Session has to be started");
        }
        return session_id();
    }
    
    /**
     * Set the session id
     * 
     * @param string $sid The new session id
     *
     * @throws LogicException If the session is already running
     */
    public function setId(string $sid)
    {
        if ($this->started) {
            throw new LogicException(
                "Impossible de définir l'identifiant de session, la session a déjà démarrée"
            );
        }
        
        session_id($sid);
    }
    
    /**
     * Regenerate the session id
     *
     * @throws LogicException If the session isn't running
     */
    public function regenerateId()
    {
        if (!$this->started) {
            throw new LogicException("Session has to be started");
        }

        session_regenerate_id();
    }


    /**
     * Add a notification
     *
     * @param string $type    The type of notification
     * @param string $message The message of notification
     *
     * @throws LogicException If the session isn't running
     * @throws OutOfBoundsException If the type of notification is invalid
     */
    public function addNotification(string $type, string $message)
    {
        if (!$this->started) {
            throw new LogicException("Session has to be started");
        }
        if (!in_array($type, self::NOTIFICATION_LEVEL)) {
            throw new OutOfBoundsException(sprintf("type %s is invalid", $type));
        }

        $_SESSION['http.session.notification'][] = [$type, $message];
    }
    
    /**
     * Get all the notifications
     * 
     * @param bool $purge True if you want to delete notification from the session when getting them, false otherwise
     * 
     * @return array[]
     *
     * @throws LogicException If the session isn't running
     */
    public function getNotifications(bool $purge = true): array
    {
        if (!$this->started) {
            throw new LogicException("Session has to be started");
        }

        $notification = [];

        if (!empty($_SESSION['http.session.notification'])) {
            $notification = $_SESSION['http.session.notification'];
            if ($purge) {
                $_SESSION['http.session.notification'] = [];
            }
        }

        return $notification;
    }
    
    /**
     * Test if there's at least one notification
     * 
     * @return bool
     *
     * @throws LogicException If the session isn't running
     */
    public function hasNotification()
    {
        if (!$this->started) {
            throw new LogicException("Session has to be started");
        }

        return !empty($_SESSION['http.session.notification']);
    }
    
    /**
     * Add a "success" notification
     * 
     * @param string $message The message of the notification
     *
     * @throws LogicException If the session isn't running
     */    
    public function addSuccessNotification(string $message)
    {
        if (!$this->started) {
            throw new LogicException("Session has to be started");
        }

        $_SESSION['http.session.notification'][] = [self::NOTIFICATION_SUCCESS, $message];
    }
    
    /**
     * Add a "warning" notification
     *
     * @param string $message The message of the notification
     *
     * @throws LogicException If the session isn't running
     */    
    public function addWarningNotification(string $message)
    {
        if (!$this->started) {
            throw new LogicException("Session has to be started");
        }

        $_SESSION['http.session.notification'][] = [self::NOTIFICATION_WARNING, $message];
    }
    
    /**
     * Add an "error" notification
     *
     * @param string $message The message of the notification
     *
     * @throws LogicException If the session isn't running
     */
    public function addErrorNotification(string $message)
    {
        if (!$this->started) {
            throw new LogicException("Session has to be started");
        }

        $_SESSION['http.session.notification'][] = [self::NOTIFICATION_ERROR, $message];
    }
    
    /**
     * Add an "informatique" notification
     *
     * @param string $message The message of the notification
     *
     * @throws LogicException If the session isn't running
     */
    public function addInfoNotification(string $message)
    {
        if (!$this->started) {
            throw new LogicException("Session has to be started");
        }

        $_SESSION['http.session.notification'][] = [self::NOTIFICATION_INFO, $message];
    }
}
