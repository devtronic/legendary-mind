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
 * Network Topology
 *
 * @deprecated 1.0.3 Use an array of int instead. Will be removed in 1.0.4
 * @author Julian Finkler <admin@developer-heaven.de>
 * @package Devtronic\LegendaryMind
 */
class Topology
{
    /** @var int */
    public $neuronsInput;

    /** @var int */
    public $neuronsHidden;

    /** @var int */
    public $hiddenLayers;

    /** @var int */
    public $neuronsOutput;

    /**
     * Topology constructor.
     * @param int $neuronsInput Number of input neurons
     * @param int $neuronsHidden Number of neurons per hidden layer
     * @param int $hiddenLayers Number of hidden layers
     * @param int $neuronsOutput number of output neurons
     */
    public function __construct($neuronsInput, $neuronsHidden, $hiddenLayers, $neuronsOutput)
    {
        $this->neuronsInput = $neuronsInput;
        $this->neuronsHidden = $neuronsHidden;
        $this->hiddenLayers = $hiddenLayers;
        $this->neuronsOutput = $neuronsOutput;
    }
}