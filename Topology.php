<?php
namespace Devtronic\LegendaryMind;

class Topology
{
    public $neuronsInput;

    public $neuronsHidden;

    public $hiddenLayers;

    public $neuronsOutput;

    public function __construct($neuronsInput, $neuronsHidden, $hiddenLayers, $neuronsOutput)
    {
        $this->neuronsInput = $neuronsInput;
        $this->neuronsHidden = $neuronsHidden;
        $this->hiddenLayers = $hiddenLayers;
        $this->neuronsOutput = $neuronsOutput;
    }
}