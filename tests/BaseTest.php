<?php

namespace NotificationChannels\Discord\Tests;

use Mockery;

abstract class BaseTest extends \PHPUnit\Framework\TestCase
{
    public function tearDown()
    {
        parent::tearDown();

        if ($container = Mockery::getContainer()) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }

        Mockery::close();
    }
}
