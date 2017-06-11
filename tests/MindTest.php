<?php
/**
 * This file is part of the Devtronic Legendary Mind package.
 *
 * (c) Julian Finkler <julian@developer-heaven.de>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Devtronic\Tests\LegendaryMind;

use Devtronic\Layerless\Activator\ActivatorInterface;
use Devtronic\Layerless\Activator\SigmoidActivator;
use Devtronic\Layerless\Activator\SinusActivator;
use Devtronic\Layerless\Activator\TanHActivator;
use Devtronic\LegendaryMind\Layer;
use Devtronic\LegendaryMind\Mind;
use PHPUnit\Framework\TestCase;

/**
 * Test for Mind
 * @package Devtronic\Tests\LegendaryMind
 */
class MindTest extends TestCase
{
    public function testConstruct()
    {
        $this->assertTrue(class_exists(Mind::class));

        $topology = [2, 3, 1];
        $activator = new SigmoidActivator();
        $mind = new Mind($topology, $activator);

        $this->assertTrue($mind instanceof Mind);

        $this->assertSame($activator, $mind->getActivator());
        $this->assertCount(3, $mind->getLayers());
        $this->assertCount(2, $mind->getLayer(0)->getNeurons());
        $this->assertCount(3, $mind->getLayer(1)->getNeurons());
        $this->assertCount(1, $mind->getLayer(2)->getNeurons());
    }

    public function testConstructWithoutActivator()
    {
        $this->assertTrue(class_exists(Mind::class));

        $topology = [2, 3, 1];
        $mind = new Mind($topology);

        $this->assertTrue($mind instanceof Mind);
        $this->assertEquals(new TanHActivator(), $mind->getActivator());
    }

    public function testGetSetActivator()
    {
        $topology = [2, 3, 1];
        $mind = new Mind($topology);

        $this->assertTrue($mind->getActivator() instanceof ActivatorInterface);
        $this->assertEquals(new TanHActivator(), $mind->getActivator());

        $activator = new SigmoidActivator();
        $mind->setActivator($activator);

        $this->assertTrue($mind->getActivator() instanceof ActivatorInterface);
        $this->assertSame($activator, $mind->getActivator());
    }

    public function testGetSetLayers()
    {
        $topology = [2, 3, 4, 1];
        $mind = new Mind($topology);
        $this->assertCount(4, $mind->getLayers());
        $mind->setLayers([]);
        $this->assertCount(0, $mind->getLayers());
    }

    public function testGetAddLayer()
    {
        $topology = [2, 3, 4, 1];
        $mind = new Mind($topology);
        $layer = $this->createMock(Layer::class);
        $mind->addLayer($layer);
        $this->assertSame($layer, $mind->getLayer(4));
    }

    public function testGetLayerFails()
    {

        $topology = [2, 1];
        $mind = new Mind($topology);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Layer #2 is undefined');
        $mind->getLayer(2);
    }

    public function testGetError()
    {
        $topology = [2, 3, 1];
        $mind = new Mind($topology, new TanHActivator());
        $lesson = [1, 0];
        $expected = [0];

        $mind->predict($lesson);
        $mind->backPropagate($expected);
        $error = $mind->getError();

        $mind->predict($lesson);
        $mind->backPropagate($expected);
        $this->assertLessThan($error, $mind->getError());
    }

    public function testConnectNeuronsSimple()
    {
        $topology = [2, 3, 1];
        $mind = new Mind($topology, new TanHActivator());

        $this->assertTrue($mind instanceof Mind);

        foreach ($mind->getLayer(0)->getNeurons() as $neuron) {
            $this->assertCount(0, $neuron->getSynapsesIn());
            $this->assertCount(3, $neuron->getSynapsesOut());
        }

        foreach ($mind->getLayer(1)->getNeurons() as $neuron) {
            $this->assertCount(2, $neuron->getSynapsesIn());
            $this->assertCount(1, $neuron->getSynapsesOut());
        }

        foreach ($mind->getLayer(2)->getNeurons() as $neuron) {
            $this->assertCount(3, $neuron->getSynapsesIn());
            $this->assertCount(0, $neuron->getSynapsesOut());
        }
    }

    public function testPredict()
    {
        $topology = [2, 3, 1];
        $mind = new Mind($topology, new TanHActivator());
        $lesson = [1, 1];

        $mind->predict($lesson);
        $out = $mind->getOutput();
        $this->assertGreaterThanOrEqual(-1.00, $out[0]);
        $this->assertLessThanOrEqual(1.00, $out[0]);
        $this->assertFalse($out[0] == 0.000000);
    }

    public function testPredictFails()
    {
        $topology = [2, 3, 1];
        $mind = new Mind($topology, new TanHActivator());
        $lesson = [1, 1, 1];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Input values must equal input neurons');
        $mind->predict($lesson);
    }

    public function testFeedForward()
    {
        $topology = [2, 3, 1];
        $mind = new Mind($topology, new TanHActivator());

        $mind->getLayer(0)->getNeuron(0)->setOutput(1);
        $mind->getLayer(0)->getNeuron(1)->setOutput(1);

        $this->assertSame(0.000, $mind->getLayer(1)->getNeuron(0)->getOutput());
        $this->assertSame(0.000, $mind->getLayer(1)->getNeuron(1)->getOutput());
        $this->assertSame(0.000, $mind->getLayer(1)->getNeuron(2)->getOutput());

        $this->assertSame(0.000, $mind->getLayer(2)->getNeuron(0)->getOutput());

        $mind->feedForward();

        $this->assertNotSame(0.000, $mind->getLayer(1)->getNeuron(0)->getOutput());
        $this->assertNotSame(0.000, $mind->getLayer(1)->getNeuron(1)->getOutput());
        $this->assertNotSame(0.000, $mind->getLayer(1)->getNeuron(2)->getOutput());

        $this->assertNotSame(0.000, $mind->getLayer(2)->getNeuron(0)->getOutput());
    }

    public function testBackPropagate()
    {
        $topology = [2, 3, 1];
        $mind = new Mind($topology, new TanHActivator());
        $lesson = [1, 0];
        $expected = [0];

        $oldWeights = [];
        foreach ($mind->getLayers() as $lIndex => $layer) {
            foreach ($layer->getNeurons() as $nIndex => $neuron) {
                foreach ($neuron->getSynapsesOut() as $sIndex => $synapse) {
                    $oldWeights[$lIndex][$nIndex][$sIndex] = $synapse->getWeight();
                }
            }
        }

        $mind->predict($lesson);
        $mind->backPropagate($expected);

        $newWeights = [];
        foreach ($mind->getLayers() as $lIndex => $layer) {
            foreach ($layer->getNeurons() as $nIndex => $neuron) {
                foreach ($neuron->getSynapsesOut() as $sIndex => $synapse) {
                    $newWeights[$lIndex][$nIndex][$sIndex] = $synapse->getWeight();
                }
            }
        }
        $this->assertNotEquals($oldWeights, $newWeights);
    }

    public function testBackPropagateFails()
    {

        $topology = [2, 3, 1];
        $mind = new Mind($topology, new TanHActivator());
        $lesson = [1, 0];
        $expected = [0, 0];

        $mind->predict($lesson);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Wrong number of target values');
        $mind->backPropagate($expected);
    }

    public function testTrain()
    {
        $lessons = [
            [[0, 0], [0]],
            [[0, 1], [1]],
            [[1, 0], [1]],
            [[1, 1], [0]],
        ];

        $topology = [2, 3, 1];
        $mind = new Mind($topology, new SinusActivator());

        $mind->train($lessons, 500);

        $mind->predict([1, 0]);
        $out = $mind->getOutput();
        $this->assertGreaterThan(0.8, $out[0]);

        $mind->predict([0, 1]);
        $out = $mind->getOutput();
        $this->assertGreaterThan(0.8, $out[0]);

        $mind->predict([1, 1]);
        $out = $mind->getOutput();
        $this->assertLessThan(0.2, $out[0]);
    }
}