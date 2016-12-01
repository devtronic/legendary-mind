<?php
/*
 * This file is part of the Devtronic Legendary Mind package.
 *
 * (c) Julian Finkler <admin@developer-heaven.de>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Devtronic\LegendaryMind\Activator;

use Devtronic\LegendaryMind\IActivator;

/**
 * Activator based on sigmoid function
 *
 * @package Devtronic\LegendaryMind\Activator
 */
class SigmoidActivator implements IActivator
{

    /**
     * Neuron Activation Function
     *
     * @param float $x Raw Neuron Output
     * @return float Activated Neuron Output
     */
    function activate($x)
    {
        return 1 / (1 + exp(-$x));
    }

    /**
     * Derivative of activation Function
     *
     * @param float $x Raw Neuron Output
     * @return float Activated Neuron Output
     */
    function activateDerivative($x)
    {
        $y = 1 / (1 + exp(-$x));
        return $y * (1 - $y);
    }
}