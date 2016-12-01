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

use Devtronic\LegendaryMind\Activator\HTanActivator;

/**
 * This "is" the neural network.
 *
 * @see https://github.com/Devtronic/legendary-mind#standalone-network
 *
 * @package Devtronic\LegendaryMind
 * @author Julian Finkler <admin@developer-heaven.de>
 */
class Mind
{
    /** @var Topology */
    public $topology;

    /** @var IActivator */
    public $activator;

    /** @var Layer[] */
    public $layers = [];

    /** @var Mind */
    public static $instance;

    /** @var float */
    public $error = 0.0;

    /**
     * Network constructor.
     *
     * @param Topology $topology
     * @param IActivator $activator
     */
    public function __construct(Topology $topology, $activator = null)
    {
        if ($activator === null) {
            $activator = new HTanActivator();
        }

        $this->topology = $topology;
        $this->activator = $activator;

        // Create Neurons

        // Input Layer
        $inputLayer = new Layer();
        for ($iNeuron = 0; $iNeuron < $this->topology->neuronsInput; $iNeuron++) {
            $inputLayer->neurons[] = new Neuron();
        }
        $this->layers[] = $inputLayer;

        // Hidden Layers
        for ($hiddenLayerIndex = 0; $hiddenLayerIndex < $this->topology->hiddenLayers; $hiddenLayerIndex++) {
            $hiddenLayer = new Layer();

            for ($hNeuron = 0; $hNeuron < $this->topology->neuronsHidden; $hNeuron++) {
                $hiddenLayer->neurons[] = new Neuron();
            }

            $this->layers[] = $hiddenLayer;
        }

        // Output Layer
        $outputLayer = new Layer();
        for ($oNeuron = 0; $oNeuron < $this->topology->neuronsOutput; $oNeuron++) {
            $outputLayer->neurons[] = new Neuron();
        }
        $this->layers[] = $outputLayer;

        $this->connectNeurons();
        Mind::$instance = &$this;
    }

    /**
     * Set the Instance after a Restore
     */
    public function reInit()
    {
        Mind::$instance = &$this;
    }

    /**
     * Create the synapses of each neuron
     */
    public function connectNeurons()
    {
        for ($layerIndex = 0; $layerIndex < count($this->layers) - 2; $layerIndex++) {
            $synapseCount = count($this->layers[$layerIndex + 1]->neurons);

            for ($n = 0; $n < count($this->layers[$layerIndex]->neurons); $n++) {
                for ($s = 0; $s < $synapseCount; $s++) {
                    $this->layers[$layerIndex]->neurons[$n]->synapses[] = new Synapse($this->randomBetween(-0.2, 0.2));
                }
            }
        }
        $layerIndex = count($this->layers) - 2;
        $synapseCount = count($this->layers[$layerIndex + 1]->neurons);

        for ($n = 0; $n < count($this->layers[$layerIndex]->neurons); $n++) {
            for ($s = 0; $s < $synapseCount; $s++) {
                $this->layers[$layerIndex]->neurons[$n]->synapses[] = new Synapse($this->randomBetween(-2.0, 2.0));
            }
        }
    }

    /**
     * Predict the output for input values
     *
     * @param float[] $inputValues
     * @throws \Exception
     */
    public function predict($inputValues)
    {
        if (count($inputValues) != $this->topology->neuronsInput) {
            throw new \Exception('Input values must equal input neurons');
        }

        for ($iNeuron = 0; $iNeuron < $this->topology->neuronsInput; $iNeuron++) {
            $this->layers[0]->neurons[$iNeuron]->outputVal = $inputValues[$iNeuron];
        }
        $this->feedForward();
    }

    /**
     * Forward the input to the next layer
     */
    private function feedForward()
    {
        for ($layerIndex = 1; $layerIndex < count($this->layers); $layerIndex++) {
            $previousLayer = $this->layers[$layerIndex - 1];
            $this->layers[$layerIndex]->feedForward($previousLayer);
        }
    }

    /**
     * Back propagation
     *
     * @param float[] $expected Expected output
     * @param float $learningRate Learning Rate
     * @param float $momentum Multiplier for Delta
     * @return float The current error
     * @throws \Exception
     */
    public function backPropagate($expected, $learningRate = 0.2, $momentum = 0.01)
    {
        if (count($expected) != $this->topology->neuronsOutput) {
            throw new \Exception('wrong number of target values');
        }

        $deltas = [];

        // Output Layer
        $lastLayerIndex = count($this->layers) - 1;
        for ($nOutput = 0; $nOutput < $this->topology->neuronsOutput; $nOutput++) {
            $currentVal = $this->layers[$lastLayerIndex]->neurons[$nOutput]->outputVal;
            $error = $expected[$nOutput] - $currentVal;
            $deltas[$lastLayerIndex][$nOutput] = $this->activateDerivative($currentVal) * $error;
        }

        // Hidden Layers (reverse)
        for ($hiddenLayerIndex = $this->topology->hiddenLayers; $hiddenLayerIndex > 0; $hiddenLayerIndex--) {
            $nextLayerIndex = $hiddenLayerIndex + 1;
            for ($nHidden = 0; $nHidden < $this->topology->neuronsHidden; $nHidden++) {
                $currentVal = $this->layers[$hiddenLayerIndex]->neurons[$nHidden]->outputVal;

                $error = 0.0;
                for ($nextNeuron = 0; $nextNeuron < count($this->layers[$nextLayerIndex]->neurons); $nextNeuron++) {
                    $error += $deltas[$nextLayerIndex][$nextNeuron] * $this->layers[$hiddenLayerIndex]->neurons[$nHidden]->synapses[$nextNeuron]->weight;
                }
                $deltas[$hiddenLayerIndex][$nHidden] = $this->activateDerivative($currentVal) * $error;

            }
        }

        // Update Weights
        // Freaking complex don't touch it
        for ($layerIndex = $this->topology->hiddenLayers + 1; $layerIndex > 0; $layerIndex--) {
            $prevIndex = $layerIndex - 1;
            for ($j = 0; $j < count($this->layers[$prevIndex]->neurons); $j++) {
                for ($k = 0; $k < count($this->layers[$layerIndex]->neurons); $k++) {
                    $change = $deltas[$layerIndex][$k] * $this->layers[$prevIndex]->neurons[$j]->outputVal;

                    $deltaOld = $this->layers[$prevIndex]->neurons[$j]->synapses[$k]->deltaOld;
                    $this->layers[$prevIndex]->neurons[$j]->synapses[$k]->weight += $learningRate * $change + $momentum * $deltaOld;
                    $this->layers[$prevIndex]->neurons[$j]->synapses[$k]->deltaOld = $change;
                }
            }
        }

        $error = 0.0;
        for ($k = 0; $k < count($expected); $k++) {
            $error += 0.5 * pow($expected[$k] - $this->layers[$lastLayerIndex]->neurons[$k]->outputVal, 2);
        }
        $this->error = $error;
        return $error;
    }

    /**
     * Trains the network
     *
     * @param float[][][] $lessons The Lessons
     * $lessons = [
     *      [ # Lesson 1
     *          [0, 1], # Input
     *          [1], # Output
     *      ],
     *      [ # Lesson 2
     *          [1, 1], # Input
     *          [0], # Output
     *      ],
     * ];
     *
     *
     * @param int $iterations The iterations of each lesson
     * @param float $learningRate Learning Rate
     * @param float $momentum Multiplier for Delta
     */
    public function train($lessons, $iterations = 1000, $learningRate = 0.2, $momentum = 0.01)
    {
        for ($i = 0; $i < $iterations; $i++) {
            $error = 0.0;
            foreach ($lessons as $pat => $pattern) {
                list($inputs, $targets) = $pattern;
                $this->predict($inputs);
                $error += $this->backPropagate($targets, $learningRate, $momentum);
            }
        }
    }

    /**
     * Returns the predicted output
     *
     * @return float[]
     */
    public function getOutput()
    {
        $lastLayerIndex = count($this->layers) - 1;
        $outputValues = [];

        for ($nOutput = 0; $nOutput < $this->topology->neuronsOutput; $nOutput++) {
            $outputValues[$nOutput] = number_format($this->layers[$lastLayerIndex]->neurons[$nOutput]->outputVal, 5,
                '.', '');
        }

        return $outputValues;
    }

    /**
     * Calls the activation function
     *
     * @param float $x Input
     * @return float
     */
    public function activate($x)
    {
        return $this->activator->activate($x);
    }

    /**
     * Calls the derivative of the activation function
     *
     * @param float $x Input
     * @return float
     */
    public function activateDerivative($x)
    {
        return $this->activator->activateDerivative($x);
    }

    /**
     * Generates Random float between $min and $max
     *
     * @param float $min Minimum
     * @param float $max Maximum
     * @return float The random float
     */
    public function randomBetween($min, $max)
    {
        return ($min + lcg_value() * (abs($max - $min)));
    }

}