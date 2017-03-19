<?php
/**
 * This file is part of the browser-detector-tests package.
 *
 * Copyright (c) 2015-2017, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowserDetectorTest\UserAgentsTest;

use BrowserDetectorTest\UserAgentsTest;
use UaResult\Result\Result;

/**
 * Class UserAgentsTest
 *
 * @category   CompareTest
 *
 * @author     Thomas Mueller <mimmi20@live.de>
 * @group      useragenttest
 */
class T00170Test extends UserAgentsTest
{
    /**
     * @var string
     */
    protected $sourceDirectory = 'tests/issues/00170/';

    /**
     * @dataProvider userAgentDataProvider
     *
     * @param string                  $userAgent
     * @param \UaResult\Result\Result $expectedResult
     *
     * @throws \Exception
     * @group  integration
     * @group  useragenttest
     * @group  00170
     */
    public function testUserAgents($userAgent, Result $expectedResult)
    {
        parent::testUserAgents($userAgent, $expectedResult);
    }
}
