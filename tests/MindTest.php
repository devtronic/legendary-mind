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

use Devtronic\Layerless\Activator\TanHActivator;
use Devtronic\LegendaryMind\Layer;
use Devtronic\LegendaryMind\Mind;
use Devtronic\LegendaryMind\Neuron;
use Devtronic\LegendaryMind\Synapse;
use PHPUnit\Framework\TestCase;

/**
 * Test for Mind
 * @package Devtronic\Tests\LegendaryMind
 */
class MindTest extends TestCase
{

    public function testConstruct()
    {
        $topology = [2, 3, 1];
        $activator = new TanHActivator();

        $mind = new Mind($topology, $activator);

        $this->assertEquals($topology, $mind->topology);
        $this->assertEquals($activator, $mind->activator);
    }

    public function testLayers()
    {
        $topology = [2, 3, 1];
        $activator = new TanHActivator();

        $mind = new Mind($topology, $activator);

        $this->assertEquals(count($topology), count($mind->layers));
    }

    public function testNeurons()
    {
        $topology = [2, 3, 1];
        $activator = new TanHActivator();

        $mind = new Mind($topology, $activator);

        for ($layerIndex = 0; $layerIndex < count($topology); $layerIndex++) {
            $this->assertEquals($topology[$layerIndex], count($mind->layers[$layerIndex]->neurons));
        }
    }

    public function testActivation()
    {
        $topology = [2, 3, 1];
        $activator = new TanHActivator();

        $mind = new Mind($topology, $activator);

        $this->assertEquals(0.762, round($mind->activate(1.0), 3));
        $this->assertEquals(0.197, round($mind->activate(0.2), 3));
    }

    public function testDerivative()
    {
        $topology = [2, 3, 1];
        $activator = new TanHActivator();

        $mind = new Mind($topology, $activator);

        $this->assertEquals(0.420, round($mind->activateDerivative(1.0), 3));
        $this->assertEquals(0.961, round($mind->activateDerivative(0.2), 3));
    }

    public function testFeedForward()
    {
        $topology = [2, 3, 1];
        $activator = new TanHActivator();

        new Mind($topology, $activator);

        $n1_1 = new Neuron();
        $n1_1->outputVal = 1;
        $n1_1->synapses = [
            new Synapse(0.3),
            new Synapse(0.2),
        ];
        $n1_2 = new Neuron();
        $n1_2->outputVal = 1;
        $n1_2->synapses = [
            new Synapse(0.6),
            new Synapse(0.4),
        ];

        $layer1 = new Layer();
        $layer1->neurons = [$n1_1, $n1_2];

        $n2_1 = new Neuron();
        $n2_2 = new Neuron();

        $layer2 = new Layer();
        $layer2->neurons = [$n2_1, $n2_2];

        $layer2->feedForward($layer1);

        $this->assertEquals(0.716, round($layer2->neurons[0]->outputVal, 3));
        $this->assertEquals(0.537, round($layer2->neurons[1]->outputVal, 3));
    }

    public function testRandomize()
    {
        $topology = [2, 3, 1];
        $activator = new TanHActivator();

        $mind = new Mind($topology, $activator);

        $minimum = -2.0;
        $maximum = 2.0;

        $random = $mind->randomBetween($minimum, $maximum);

        $this->assertGreaterThanOrEqual($minimum, $random);
        $this->assertLessThanOrEqual($maximum, $random);

        $minimum2 = -0.2;
        $maximum2 = 0.2;

        $random2 = $mind->randomBetween($minimum2, $maximum2);

        $this->assertGreaterThanOrEqual($minimum2, $random2);
        $this->assertLessThanOrEqual($maximum2, $random2);
    }

    public function testSimpleXOR()
    {
        $topology = [2, 3, 1];
        $activator = new TanHActivator();

        $mind = new Mind($topology, $activator);

        $lesson = [
            'input' => [1, 0],
            'expected' => [1],
        ];

        $lastResult = null;
        for ($i = 0; $i < 10; $i++) {
            $mind->predict($lesson['input']);
            $output = $mind->getOutput();

            if ($lastResult !== null) {
                $this->assertGreaterThan($lastResult, $output[0]);
            }

            $lastResult = $output[0];

            $mind->backPropagate($lesson['expected']);
        }
    }
}