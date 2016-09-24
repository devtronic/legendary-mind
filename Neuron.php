<?php

namespace Devtronic\LegendaryMind;

class Neuron
{
    /**
     * @var Synapse[]
     */
    public $synapses = [];

    /**
     * @var float
     */
    public $outputVal = 0.0;
}