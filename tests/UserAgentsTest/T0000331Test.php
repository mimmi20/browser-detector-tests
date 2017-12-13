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
 * Class T0000331Test
 *
 * has 985 tests
 * this file was created/edited automatically, please do not edit it
 *
 * @author     Thomas Mueller <mimmi20@live.de>
 * @group      0000331
 */
class T0000331Test extends TestCase
{
    use UserAgentsTestTrait;

    /**
     * @var string
     */
    private $sourceDirectory = [
        'tests/issues/0000587/',
        'tests/issues/0000358/',
        'tests/issues/0000584/',
        'tests/issues/0000145/',
        'tests/issues/0000426/',
        'tests/issues/0000117/',
        'tests/issues/0000254/',
        'tests/issues/0000846/',
        'tests/issues/0000906/',
        'tests/issues/0000029/',
    ];
}
