<?php

namespace Nkey\Caribu\Mvc\Util;

/**
 * This class contains to the Caribu MVC package.
 *
 * It provides an abstract access to session variables.
 *
 * @author Maik Greubel <greubel@nkey.de>
 */
class Session implements \IteratorAggregate
{
    /**
     * The session data
     *
     * @var array
     */
    private $sessionData = array();

    /**
     * The session namespace
     *
     * @var string
     */
    private $namespace = null;

    /**
     * The session identifier
     *
     * @var string
     */
    private $sessionId;

    /**
     * Create a new session instance
     *
     * @param string $namespace Optional name of session namespace
     */
    public function __construct($namespace = null)
    {
        session_start();
        $this->sessionId = session_id();

        if (null == $namespace) {
            $this->sessionData = $_SESSION;
        }
        else {
            if (!isset($_SESSION[$namespace])) {
                $_SESSION[$namespace] = array();
            }
            $this->sessionData = $_SESSION[$namespace];
        }
    }

    /**
     * Set a specific session key to arbitrary data
     *
     * @param string $key The session data key
     * @param mixed $value The value
     */
    public function set($key, $value)
    {
        $this->sessionData[$key] = $value;
        $this->update();
    }

    /**
     * Destroy the session
     */
    public function destroy()
    {
        $this->sessionData = array();
        session_destroy();
    }

    /**
     * Update internal session data
     */
    private function update()
    {
        if (null != $this->namespace) {
            $_SESSION[$this->namespace] = $this->sessionData;
        }
        else {
            $_SESSION = $this->sessionData;
        }
    }

    /**
     * Get specific session data
     *
     * @param string $key
     * @return NULL|mixed
     */
    public function get($key)
    {
        return $this->has($key) ? $this->sessionData[$key] : null;
    }

    /**
     * Checks whether a specific key exists in session data
     *
     * @param string $key
     */
    public function has($key)
    {
        return isset($this->sessionData[$key]);
    }

    /**
     * (non-PHPdoc)
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->sessionData);
    }
}