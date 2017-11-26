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
 * Class T0002470Test
 *
 * has 869 tests
 * this file was created/edited automatically, please do not edit it
 *
 * @author     Thomas Mueller <mimmi20@live.de>
 * @group      0002470
 */
class T0002470Test extends TestCase
{
    use UserAgentsTestTrait;

    /**
     * @var string
     */
    private $sourceDirectory = [
        'tests/issues/0002630/'
        'tests/issues/0000423/'
        'tests/issues/0000099/'
        'tests/issues/0000027/'
    ];
}
