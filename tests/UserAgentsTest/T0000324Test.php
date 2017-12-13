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
 * Class T0000324Test
 *
 * has 958 tests
 * this file was created/edited automatically, please do not edit it
 *
 * @author     Thomas Mueller <mimmi20@live.de>
 * @group      0000324
 */
class T0000324Test extends TestCase
{
    use UserAgentsTestTrait;

    /**
     * @var string
     */
    private $sourceDirectory = [
        'tests/issues/0000108/',
        'tests/issues/0000139/',
        'tests/issues/0000627/',
        'tests/issues/0000481/',
        'tests/issues/0000487/',
        'tests/issues/0000102/',
        'tests/issues/0000575/',
    ];
}
