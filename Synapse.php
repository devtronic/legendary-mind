<?php

namespace Devtronic\LegendaryMind;

class Synapse
{
    /**
     * @var float
     */
    public $weight = 0.0;

    /**
     * @var float
     */
    public $deltaOld = 0.0;

    public function __construct($weight = 0.0)
    {
        $this->weight = $weight;
    }
}