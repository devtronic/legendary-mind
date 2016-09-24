<?php

namespace Devtronic\LegendaryMind;

class Layer
{
    /**
     * @var Neuron[]
     */
    public $neurons;

    /**
     * @param Layer $previousLayer
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