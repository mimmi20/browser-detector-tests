<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2017, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowserDetectorTest\UserAgentsTest;

use BrowserDetectorTest\UserAgentsTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Class T0000326Test
 *
 * has 979 tests
 * this file was created/edited automatically, please do not edit it
 *
 * @author     Thomas Mueller <mimmi20@live.de>
 * @group      0000326
 */
class T0000326Test extends TestCase
{
    use UserAgentsTestTrait;

    /**
     * @var string
     */
    private $sourceDirectory = [
        'tests/issues/0000564/',
        'tests/issues/0000633/',
        'tests/issues/0000639/',
        'tests/issues/0000425/',
        'tests/issues/0000565/',
        'tests/issues/0000572/',
        'tests/issues/0002208/',
        'tests/issues/0000027/',
    ];
}
