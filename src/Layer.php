<?php
/**
 * This file is part of the Devtronic Legendary Mind package.
 *
 * (c) Julian Finkler <julian@developer-heaven.de>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Devtronic\LegendaryMind;

use Devtronic\Layerless\Neuron;

/**
 * This class represents a layer in the neural network.
 * A layer contains neurons and a feed forward method for
 * calculating neuron outputs.
 *
 * @author Julian Finkler <julian@developer-heaven.de>
 * @package Devtronic\LegendaryMind
 */
class Layer
{
    /** @var \Devtronic\Layerless\Neuron[] */
    protected $neurons = [];

    /**
     * Activate the neurons
     */
    public function feedForward()
    {
        foreach ($this->neurons as $neuron) {
            $neuron->activate();
        }
    }

    /**
     * Adds a neuron to the layer
     * @param Neuron $neuron
     * @return $this
     */
    public function addNeuron(Neuron $neuron)
    {
        $this->neurons[] = $neuron;
        return $this;
    }

    /**
     * Adds multiple neurons to the layer
     * @param Neuron[] $neurons
     * @return $this
     * @throws \Exception
     */
    public function addNeurons(array $neurons)
    {
        foreach ($neurons as $neuron) {
            if ($neuron instanceof Neuron === false) {
                throw new \Exception(sprintf('Expect object of type Neuron, %s given', gettype($neuron)));
            }
            $this->addNeuron($neuron);
        }
        return $this;
    }

    /**
     * @return Neuron[]
     */
    public function getNeurons()
    {
        return $this->neurons;
    }

    /**
     * @param integer $index Index of the Neuron
     * @return Neuron
     * @throws \Exception
     */
    public function getNeuron($index)
    {
        if (!isset($this->neurons[$index])) {
            throw new \Exception(sprintf('Neuron #%s is undefined', $index));
        }
        return $this->neurons[$index];
    }
}