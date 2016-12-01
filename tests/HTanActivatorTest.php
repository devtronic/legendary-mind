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


use Devtronic\LegendaryMind\Activator\HTanActivator;


class HTanActivatorTest extends \PHPUnit_Framework_TestCase
{
    public function testActivation()
    {
        $activator = new HTanActivator();

        $this->assertEquals(0.762, round($activator->activate(1.0), 3));
        $this->assertEquals(0.197, round($activator->activate(0.2), 3));
    }

    public function testDerivative()
    {
        $activator = new HTanActivator();

        $this->assertEquals(0.420, round($activator->activateDerivative(1.0), 3));
        $this->assertEquals(0.961, round($activator->activateDerivative(0.2), 3));
    }
}
