<?php

namespace Mobicms;

use Zend\Stdlib\ArrayObject;

/**
 * Class User
 *
 * @package Johncms
 */
class User extends ArrayObject implements Api\UserInterface
{
    private $userConfigObject;

    /**
     * User constructor.
     *
     * @param array $input
     */
    public function __construct(array $input)
    {
        parent::__construct($input, parent::ARRAY_AS_PROPS);
    }

    /**
     * User validation
     *
     * @return bool
     */
    public function isValid()
    {
        if ($this->offsetGet('id') > 0
            && $this->offsetGet('preg') == 1
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get User config
     *
     * @return UserConfig
     */
    public function getConfig()
    {
        if (null === $this->userConfigObject) {
            $this->userConfigObject = new UserConfig($this);
        }

        return $this->userConfigObject;
    }
}
