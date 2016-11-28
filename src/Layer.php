<?php
/*
 * This file is part of the Devtronic Legendary Mind package.
 *
 * (c) Julian Finkler <admin@developer-heaven.de>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Devtronic\LegendaryMind;

/**
 * This class represents a layer in the neural network.
 * A layer contains neurons and a feed forward method for
 * calculating neuron outputs.
 *
 * @author Julian Finkler <admin@developer-heaven.de>
 * @package Devtronic\LegendaryMind
 */
class Layer
{
    /** @var Neuron[] */
    public $neurons = [];

    /**
     * Calculate the neuron outputs
     *
     * @param Layer $previousLayer The previous layer
     */
    public function feedForward($previousLayer)
    {
        for ($n = 0; $n < count($this->neurons); $n++) {
            $sum = 0.0;
            for ($pNeuron = 0; $pNeuron < count($previousLayer->neurons); $pNeuron++) {
                $sum += $previousLayer->neurons[$pNeuron]->outputVal * $previousLayer->neurons[$pNeuron]->synapses[$n]->weight;
            }
            $sum = Mind::$instance->activate($sum);
            $this->neurons[$n]->outputVal = $sum;
        }
    }
}