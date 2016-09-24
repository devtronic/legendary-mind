<?php
namespace Devtronic\LegendaryMind;

class Mind
{

    /**
     * @var Topology
     */
    public $topology;

    /**
     * @var string
     */
    public $activation;

    /**
     * @var string
     */
    public $activation_derivative;

    /**
     * @var Layer[]
     */
    public $layers;

    /**
     * @var Mind
     */
    public static $instance;

    /**
     * @var float
     */
    public $error = 0.0;

    /**
     * Network constructor.
     * @param Topology $topology
     * @param string $activation
     * @param string $activation_derivative
     */
    public function __construct(Topology $topology, $activation = 'sigmoid', $activation_derivative = 'sigmoid_prime')
    {
        $this->topology = $topology;
        $this->activation = $activation;
        $this->activation_derivative = $activation_derivative;


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

    public function reInit()
    {
        Mind::$instance = &$this;
    }

    public function connectNeurons()
    {
        for ($layerIndex = 0; $layerIndex < count($this->layers) - 2; $layerIndex++) {
            $synapseCount = count($this->layers[$layerIndex + 1]->neurons);

            for ($n = 0; $n < count($this->layers[$layerIndex]->neurons); $n++) {
                for ($s = 0; $s < $synapseCount; $s++) {
                    $this->layers[$layerIndex]->neurons[$n]->synapses[] = new Synapse($this->n_rand(-0.2, 0.2));
                }
            }
        }
        $layerIndex = count($this->layers) - 2;
        $synapseCount = count($this->layers[$layerIndex + 1]->neurons);

        for ($n = 0; $n < count($this->layers[$layerIndex]->neurons); $n++) {
            for ($s = 0; $s < $synapseCount; $s++) {
                $this->layers[$layerIndex]->neurons[$n]->synapses[] = new Synapse($this->n_rand(-2.0, 2.0));
            }
        }
    }

    public function propagate($inputValues)
    {
        if (count($inputValues) != $this->topology->neuronsInput) {
            throw new \Exception('Input values must equal input neurons');
        }

        for ($iNeuron = 0; $iNeuron < $this->topology->neuronsInput; $iNeuron++) {
            $this->layers[0]->neurons[$iNeuron]->outputVal = $inputValues[$iNeuron];
        }
        $this->feedForward();
    }

    private function feedForward()
    {
        for ($layerIndex = 1; $layerIndex < count($this->layers); $layerIndex++) {
            $previousLayer = $this->layers[$layerIndex - 1];
            $this->layers[$layerIndex]->feedForward($previousLayer);
        }
    }

    ## parts from http://pastebin.com/HRGVzR6L
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

        // Input layer
        for ($i = 0; $i < $this->topology->neuronsInput; $i++) {
            for ($j = 0; $j < $this->topology->neuronsHidden; $j++) {
                $change = $deltas[1][$j] * $this->layers[0]->neurons[$i]->outputVal;

                $deltaOld = $this->layers[0]->neurons[$i]->synapses[$j]->deltaOld;
                $this->layers[0]->neurons[$i]->synapses[$j]->weight += $learningRate * $change + $momentum * $deltaOld;
                $this->layers[0]->neurons[$i]->synapses[$j]->deltaOld = $change;
            }

        }

        $error = 0.0;
        for ($k = 0; $k < count($expected); $k++) {
            $error += 0.5 * pow($expected[$k] - $this->layers[$lastLayerIndex]->neurons[$k]->outputVal, 2);
        }
        $this->error = $error;
        return $error;
    }

    public function train($patterns, $iterations = 1000, $learningRate = 0.2, $momentum = 0.01)
    {
        for ($i = 0; $i < $iterations; $i++) {
            $error = 0.0;
            foreach ($patterns as $pat => $pattern) {
                list($inputs, $targets) = $pattern;
                $this->propagate($inputs);
                $error += $this->backPropagate($targets, $learningRate, $momentum);
            }
        }
    }

    public function getOutput()
    {
        $lastLayerIndex = count($this->layers) - 1;
        $outputValues = [];

        for ($nOutput = 0; $nOutput < $this->topology->neuronsOutput; $nOutput++) {
            $outputValues[$nOutput] = number_format($this->layers[$lastLayerIndex]->neurons[$nOutput]->outputVal, 5, '.', '');
        }

        return $outputValues;
    }

    public function activate($x)
    {
        $fn = $this->activation;
        return $fn($x);
    }

    public function activateDerivative($x)
    {
        $fn = $this->activation_derivative;
        return $fn($x);
    }

    public function n_rand($a, $b)
    {
        $random = ((float)mt_rand()) / (float)mt_getrandmax();
        $diff = $b - $a;
        $r = $random * $diff;
        return $a + $r;
    }
}