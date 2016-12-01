<?php
/*
 * This file is part of the Devtronic Legendary Mind package.
 *
 * (c) Julian Finkler <admin@developer-heaven.de>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Devtronic\Tests\LegendaryMind;

use Devtronic\LegendaryMind\Wrapper;
use PHPUnit\Framework\TestCase;

class WrapperTest extends TestCase
{

    public function testArchiveRestore()
    {
        $wrapperFile = 'wrapper.txt';

        $wrapper1 = new Wrapper(3, 1);

        $properties = ['first', 'second'];

        $outputs = ['result' => ['win', 'lose']];

        $wrapper1->initialize($properties, $outputs);

        $wrapper1->archive($wrapperFile);

        $wrapper2 = new Wrapper(3, 1);

        $wrapper2->restore($wrapperFile);

        $this->assertEquals($wrapper1, $wrapper2);

        unlink($wrapperFile);
    }
}