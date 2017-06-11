<?php
/**
 * This file is part of the Devtronic Legendary Mind package.
 *
 * (c) Julian Finkler <julian@developer-heaven.de>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Devtronic\LegendaryMind;

use Devtronic\Layerless\Activator\ActivatorInterface;
use Devtronic\Layerless\Activator\TanHActivator;
use Devtronic\Layerless\InputNeuron;
use Devtronic\Layerless\Neuron;
use Devtronic\Layerless\Synapse;

/**
 * This "is" the neural network.
 *
 * @see https://github.com/Devtronic/legendary-mind#standalone-network
 *
 * @package Devtronic\LegendaryMind
 * @author Julian Finkler <julian@developer-heaven.de>
 */
class Mind
{
    /** @var */
    protected $activator;

    /** @var Layer[] */
    protected $layers = [];

    /** @var float */
    protected $error = 0.0;

    /**
     * Network constructor.
     *
     * @param int[] $topology
     * @param ActivatorInterface $activator
     */
    public function __construct(array $topology, ActivatorInterface $activator = null)
    {
        if ($activator === null) {
            $activator = new TanHActivator();
        }
        $this->activator = $activator;

        // Create Neurons
        // Input Layer
        $inputLayer = new Layer();
        for ($iNeuron = 0; $iNeuron < $topology[0]; $iNeuron++) {
            $inputLayer->addNeuron(new InputNeuron(0));
        }
        $this->layers[] = $inputLayer;

        // Hidden Layers
        for ($hiddenLayerIndex = 1; $hiddenLayerIndex < count($topology) - 1; $hiddenLayerIndex++) {
            $hiddenLayer = new Layer();

            for ($hNeuron = 0; $hNeuron < $topology[$hiddenLayerIndex]; $hNeuron++) {
                $hiddenLayer->addNeuron(new Neuron($activator));
            }

            $this->layers[] = $hiddenLayer;
        }

        // Output Layer
        $outputLayer = new Layer();
        for ($oNeuron = 0; $oNeuron < $topology[count($topology) - 1]; $oNeuron++) {
            $outputLayer->addNeuron(new Neuron($activator));
        }
        $this->layers[] = $outputLayer;

        $this->connectNeurons();
    }

    /**
     * Create the synapses of each neuron
     */
    public function connectNeurons()
    {
        for ($lIndex = 0; $lIndex < count($this->layers) - 1; $lIndex++) {
            for ($aIndex = 0; $aIndex < count($this->layers[$lIndex]->getNeurons()); $aIndex++) {
                $nextLayerSize = count($this->layers[$lIndex + 1]->getNeurons());
                for ($bIndex = 0; $bIndex < $nextLayerSize; $bIndex++) {
                    $random = $this->randomBetween(-0.2, 0.2);
                    new Synapse(
                        $random,
                        $this->layers[$lIndex]->getNeuron($aIndex),
                        $this->layers[$lIndex + 1]->getNeuron($bIndex)
                    );
                }
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
        $firstLayer = reset($this->layers);
        $firstLayerNeurons = $firstLayer->getNeurons();
        if (count($inputValues) != count($firstLayerNeurons)) {
            throw new \Exception('Input values must equal input neurons');
        }

        foreach ($firstLayerNeurons as $index => $neuron) {
            $neuron->setOutput($inputValues[$index]);
        }
        $this->feedForward();
    }

    /**
     * Forward the input to the next layer
     */
    public function feedForward()
    {
        foreach ($this->layers as $layer) {
            $layer->feedForward();
        }
    }

    /**
     * Back propagation
     *
     * @param float[] $expected Expected output
     * @param float $learningRate Learning Rate
     * @return float The current error
     * @throws \Exception
     */
    public function backPropagate($expected, $learningRate = 0.2)
    {
        $lastLayer = end($this->layers);
        if (count($expected) != count($lastLayer->getNeurons())) {
            throw new \Exception('Wrong number of target values');
        }

        // Output Layer
        foreach ($lastLayer->getNeurons() as $index => $neuron) {
            $neuron->calculateDelta($expected[$index]);
        }

        // Hidden Layers (reverse)
        for ($hiddenLayerIndex = count($this->layers) - 2; $hiddenLayerIndex > 0; $hiddenLayerIndex--) {
            foreach ($this->layers[$hiddenLayerIndex]->getNeurons() as $neuron) {
                $neuron->calculateDelta();
            }
        }

        foreach ($this->layers as $layer) {
            foreach ($layer->getNeurons() as $neuron) {
                $neuron->updateWeights($learningRate);
            }
        }

        $error = 0.0;
        for ($k = 0; $k < count($expected); $k++) {
            $error += 0.5 * pow($expected[$k] - $lastLayer->getNeuron($k)->getOutput(), 2);
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
     */
    public function train($lessons, $iterations = 1000, $learningRate = 0.2)
    {
        for ($i = 0; $i < $iterations; $i++) {
            $error = 0.0;
            foreach ($lessons as $pattern) {
                list($inputs, $targets) = $pattern;
                $this->predict($inputs);
                $error += $this->backPropagate($targets, $learningRate);
            }
        }
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


    /**
     * @return ActivatorInterface
     */
    public function getActivator()
    {
        return $this->activator;
    }

    /**
     * @param ActivatorInterface $activator
     * @return Mind
     */
    public function setActivator(ActivatorInterface $activator)
    {
        foreach ($this->layers as $layer) {
            foreach ($layer->getNeurons() as $neuron) {
                $neuron->setActivator($activator);
            }
        }
        $this->activator = $activator;
        return $this;
    }

    /**
     * @return Layer[]
     */
    public function getLayers()
    {
        return $this->layers;
    }

    /**
     * @param Layer[] $layers
     * @return Mind
     */
    public function setLayers($layers)
    {
        $this->layers = $layers;
        return $this;
    }

    /**
     *
     * @param $index
     * @return Layer
     * @throws \Exception
     */
    public function getLayer($index)
    {
        if (!isset($this->layers[$index])) {
            throw new \Exception(sprintf('Layer #%s is undefined', $index));
        }
        return $this->layers[$index];
    }

    /**
     * @param Layer $layer
     * @return Mind
     */
    public function addLayer(Layer $layer)
    {
        $this->layers[] = $layer;
        return $this;
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

        foreach ($this->layers[$lastLayerIndex]->getNeurons() as $index => $neuron) {
            $outputValues[$index] = $neuron->getOutput();
        }

        return $outputValues;
    }

    /**
     * @return float
     */
    public function getError()
    {
        return $this->error;
    }
}