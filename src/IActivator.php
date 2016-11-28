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
 * Interface for activator
 *
 * @author Julian Finkler <admin@developer-heaven.de>
 * @package Devtronic\LegendaryMind
 */
interface IActivator
{
    /**
     * Neuron Activation Function
     *
     * @param float $x Raw Neuron Output
     * @return float Activated Neuron Output
     */
    function activate($x);

    /**
     * Derivative of activation Function
     *
     * @param float $x Raw Neuron Output
     * @return float Activated Neuron Output
     */
    function activateDerivative($x);
}