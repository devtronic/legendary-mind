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

use Devtronic\Layerless\Neuron;
use Devtronic\LegendaryMind\Layer;
use PHPUnit\Framework\TestCase;

/**
 * Test for Layer
 * @package Devtronic\Tests\LegendaryMind
 */
class LayerTest extends TestCase
{
    public function testConstruct()
    {
        $this->assertTrue(class_exists(Layer::class));

        $layer = new Layer();
        $this->assertTrue($layer instanceof Layer);
    }

    public function testGetAddNeuron()
    {
        $layer = new Layer();

        $this->assertEmpty($layer->getNeurons());

        $neuron = $this->createMock(Neuron::class);
        $layer->addNeuron($neuron);
        $this->assertSame($neuron, $layer->getNeuron(0));
    }

    public function testGetAddNeurons()
    {
        $layer = new Layer();

        $this->assertEmpty($layer->getNeurons());

        $neuron1 = $this->createMock(Neuron::class);
        $neuron2 = $this->createMock(Neuron::class);
        $layer->addNeurons([$neuron1, $neuron2]);
        $this->assertSame([$neuron1, $neuron2], $layer->getNeurons());
    }

    public function testGetNeuronFails()
    {
        $layer = new Layer();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Neuron #953 is undefined');
        $layer->getNeuron(953);
    }

    public function testAddNeuronsFails()
    {
        $layer = new Layer();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Expect object of type Neuron, object given');
        $layer->addNeurons([new \stdClass()]);
    }

    public function testActivateSimple()
    {
        $layer = new Layer();

        $neuron1 = $this->createMock(Neuron::class);
        $tmpOut1 = 0.00;
        $neuron1->method('activate')->willReturnCallback(function () use (&$tmpOut1) {
            $tmpOut1 = 1.132;
        });
        $neuron1->method('getOutput')->willReturnCallback(function () use (&$tmpOut1) {
            return $tmpOut1;
        });
        $layer->addNeuron($neuron1);

        $this->assertSame(0.00, $neuron1->getOutput());
        $layer->feedForward();
        $this->assertSame(1.132, $neuron1->getOutput());
    }
}