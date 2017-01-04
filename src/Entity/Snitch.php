<?php

namespace Zumba\Deadmanssnitch\Entity;

class Snitch
{
    private $data;

    /**
     * Constructor.
     *
     * @param string $name
     * @param \Zumba\Deadmanssnitch\Entity\Interval $interval
     * @param array $options Any additional API options available.
     */
    public function __construct($name, Interval $interval, array $options = [])
    {
        $this->data = [
            'name' => $name,
            'interval' => (string)$interval
        ] + $options;
    }

    /**
     * Get the data of this snitch
     *
     * @return array
     */
    public function data()
    {
        return $this->data;
    }

    /**
     * Set the token for this snitch.
     *
     * @param string $token
     * @return void
     */
    public function setToken($token)
    {
        $this->data['token'] = $token;
    }

    /**
     * Set the status for this snitch.
     *
     * @param string $status
     * @return void
     */
    public function setStatus($status)
    {
        $this->data['status'] = $status;
    }

    /**
     * Set the href for this snitch.
     *
     * This is useful for get requests of this snitch.
     *
     * @param string $token
     * @return void
     */
    public function setHref($href)
    {
        $this->data['href'] = $href;
    }

    /**
     * String representation (token).
     *
     * @return string
     */
    public function __toString()
    {
        return !empty($this->data['token']) ? $this->data['token'] : '';
    }
}
