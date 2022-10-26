<?php

namespace Zumba\Deadmanssnitch\Entity;

use Cake\Datasource\EntityInterface;
use Cake\Datasource\EntityTrait;

class Snitch implements EntityInterface
{
    use EntityTrait {
        jsonSerialize as private entityTraitJsonSerialize;
        toArray as private entityTraitToArray;
        set as private entityTraitSet;
    }

    private $toBeUnaccessible = [
        'href',
        'status',
        'checked_in_at',
        'created_at',
        'check_in_url',
    ];

    /**
     * Constructor.
     *
     * @param string $name
     * @param \Zumba\Deadmanssnitch\Entity\Interval $interval
     * @param array $options Any additional API options available.
     */
    public function __construct($name, Interval $interval, array $options = [])
    {
        $this->_accessible = array_fill_keys($this->toBeUnaccessible, true) + [
            'token' => true,
            'name' => true,
            'interval' => true,
            'tags' => true,
            'alert_type' => true,
            'notes' => true,
        ];
        $this->_hidden = [
            'token',
            'href',
            'status',
            'checked_in_at',
            'created_at',
            'check_in_url',
        ];
        $this->set(compact('name', 'interval') + $options);
        $this->_accessible = array_merge(
            $this->_accessible,
            array_fill_keys($this->toBeUnaccessible, false)
        );
        $this->clean();
    }

    public function set($property, $value = null, array $options = [])
    {
        if (is_string($property) && $property !== '') {
            $this->entityTraitSet([$property => $value], ['guard' => true] + $options);
        } else {
            $this->entityTraitSet($property, $value, ['guard' => true] + $options);
        }
    }

    // @codingStandardsIgnoreStart
    protected function _setToken($token)
    {
        // @codingStandardsIgnoreEnd
        $this->setNew(false);
        $this->_accessible['token'] = false;
        return $token;
    }

    // @codingStandardsIgnoreStart
    protected function _setCheckedInAt($checkedInAt)
    {
        // @codingStandardsIgnoreEnd
        return new \DateTime($checkedInAt);
    }

    // @codingStandardsIgnoreStart
    protected function _setCreatedAt($createdAt)
    {
        // @codingStandardsIgnoreEnd
        return new \DateTime($createdAt);
    }

    // @codingStandardsIgnoreStart
    protected function _setTags($tags)
    {
        // @codingStandardsIgnoreEnd
        return (array)$tags;
    }

    // @codingStandardsIgnoreStart
    protected function _setInterval(Interval $interval)
    {
        // @codingStandardsIgnoreEnd
        return $interval;
    }

    public function toArray() : array
    {
        return [
            'interval' => (string)$this->interval
        ] + $this->entityTraitToArray();
    }

    public function jsonSerialize()
    {
        return [
            'interval' => (string)$this->interval
        ] + $this->entityTraitJsonSerialize();
    }

    /**
     * String representation (token).
     *
     * @return string
     */
    public function __toString()
    {
        return $this->token ?: '';
    }
}
