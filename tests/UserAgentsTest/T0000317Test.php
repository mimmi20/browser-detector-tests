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

use BrowserDetectorTest\UserAgentsTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Class T0000317Test
 *
 * has 877 tests
 * this file was created/edited automatically, please do not edit it
 *
 * @author     Thomas Mueller <mimmi20@live.de>
 * @group      0000317
 */
class T0000317Test extends TestCase
{
    use UserAgentsTestTrait;

    /**
     * @var string
     */
    private $sourceDirectory = [
        'tests/issues/0000869/',
        'tests/issues/0000474/',
        'tests/issues/0000479/',
        'tests/issues/0000558/',
        'tests/issues/0000252/',
    ];
}
