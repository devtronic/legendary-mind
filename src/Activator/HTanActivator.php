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

class HTanActivator implements IActivator
{
    /**
     * Neuron Activation Function
     *
     * @param float $x Raw Neuron Output
     * @return float Activated Neuron Output
     */
    function activate($x)
    {
        $y = exp(2 * $x);
        return ($y - 1) / ($y + 1);
    }

    /**
     * Derivative of activation Function
     *
     * @param float $x Raw Neuron Output
     * @return float Activated Neuron Output
     */
    function activateDerivative($x)
    {
        return 1 - pow((exp(2 * $x) - 1) / (exp(2 * $x) + 1), 2);
    }
}