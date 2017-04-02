<?php

namespace Mobicms\Api;

/**
 * Interface EnvironmentInterface
 *
 * @package Mobicms\Api
 */
interface EnvironmentInterface
{
    public function getIp();

    public function getIpViaProxy();

    public function getUserAgent();

    public function getIpLog();
}