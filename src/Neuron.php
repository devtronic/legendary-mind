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
 * This class represents a neuron in the Mind.
 *
 * @author Julian Finkler <admin@developer-heaven.de>
 * @package Devtronic\LegendaryMind
 */
class Neuron
{
    /** @var Synapse[] */
    public $synapses = [];

    /** @var float */
    public $outputVal = 0.0;
}