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
 * Class T0000323Test
 *
 * has 990 tests
 * this file was created/edited automatically, please do not edit it
 *
 * @author     Thomas Mueller <mimmi20@live.de>
 * @group      0000323
 */
class T0000323Test extends TestCase
{
    use UserAgentsTestTrait;

    /**
     * @var string
     */
    private $sourceDirectory = [
        'tests/issues/0000641/',
        'tests/issues/0000020/',
        'tests/issues/0000642/',
        'tests/issues/0000852/',
        'tests/issues/0000471/',
        'tests/issues/0000485/',
        'tests/issues/0000671/',
    ];
}
