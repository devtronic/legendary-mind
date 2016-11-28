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
 * This class represents a synapse (connection between neurons)
 * in the neural network.
 *
 * @author Julian Finkler <admin@developer-heaven.de>
 * @package Devtronic\LegendaryMind
 */
class Synapse
{
    /** @var float */
    public $weight = 0.0;

    /** @var float */
    public $deltaOld = 0.0;

    /**
     * Synapse constructor.
     *
     * @param float $weight The weight of the synapse
     */
    public function __construct($weight = 0.0)
    {
        $this->weight = $weight;
    }
}