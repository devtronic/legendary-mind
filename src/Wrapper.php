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

/**
 * Network Wrapper
 *
 * @see https://github.com/Devtronic/legendary-mind#with-wrapper-recommended
 *
 * @author Julian Finkler <julian@developer-heaven.de>
 * @package Devtronic\LegendaryMind
 */
class Wrapper
{
    /** @var array */
    public $inputs;

    /** @var array */
    public $inputMapping;

    /** @var array */
    public $outputs;

    /** @var array */
    public $outputMapping;

    /** @var int[] */
    private $topology;

    /** @var Mind */
    private $mind;

    /** @var int */
    private $hiddenNeurons;

    /** @var int */
    private $hiddenLayers;

    /**
     * Wrapper constructor.
     *
     * @param int $hiddenNeurons Number of neurons per hidden layer
     * @param int $hiddenLayers number of hidden layers
     */
    public function __construct($hiddenNeurons = 3, $hiddenLayers = 1)
    {
        $this->hiddenNeurons = $hiddenNeurons;
        $this->hiddenLayers = $hiddenLayers;
    }

    /**
     * Initialize the wrapper
     *
     * @param array $inputs The input values
     * @param array $outputs The output values
     * @param null|ActivatorInterface $activator
     */
    public function initialize($inputs, $outputs, $activator = null)
    {
        foreach ($inputs as $name => $property) {
            if (is_array($property)) {
                foreach ($property as $prop) {
                    $this->inputs[] = 0;
                    $this->inputMapping[$name][$prop] = count($this->inputs) - 1;
                }
            } else {
                $this->inputs[] = 0;
                $this->inputMapping[$property] = count($this->inputs) - 1;
            }
        }

        foreach ($outputs as $name => $property) {

            if (is_array($property)) {
                foreach ($property as $prop) {
                    $this->outputs[] = 0;
                    $this->outputMapping[$name][$prop] = count($this->outputs) - 1;
                }
            } else {
                $this->outputs[] = 0;
                $this->outputMapping[$property] = count($this->outputs) - 1;
            }
        }

        $neuronsInput = count($this->inputs);
        $neuronsHidden = $this->hiddenNeurons;
        $hiddenLayers = $this->hiddenLayers;
        $neuronsOutput = count($this->outputs);

        $topology = [];
        $topology[] = $neuronsInput;

        for ($i = 0; $i < $hiddenLayers; $i++) {
            $topology[] = $neuronsHidden;
        }

        $topology[] = $neuronsOutput;

        $this->topology = $topology;

        $this->mind = new Mind($this->topology, $activator);
    }

    /**
     * Archives the network as a text file
     *
     * @param string $file Path to the file
     */
    public function archive($file)
    {
        file_put_contents($file, serialize($this));
    }

    /**
     * Restores the network from file
     *
     * @param string $file Path to the archived network
     */
    public function restore($file)
    {
        $tmp = unserialize(file_get_contents($file));
        foreach ($tmp as $k => $v) {
            $this->{$k} = $v;
        }
    }

    /**
     * Trains the network
     *
     * @param array $training The training
     * @param int $iterations The iterations
     * @param float $learningRate The learning rate
     */
    public function train($training, $iterations = 1000, $learningRate = 0.2)
    {
        $lessons = [];
        foreach ($training as $lesson) {
            $lessons[] = $this->prepareLesson($lesson);
        }

        $this->mind->train($lessons, $iterations, $learningRate);
    }

    /**
     * Predict the output for input values
     *
     * @param $lesson
     */
    public function predict($lesson)
    {
        $prepared = $this->prepareLesson($lesson);
        $this->mind->predict($prepared[0]);
    }

    /**
     * Back propagation
     *
     * @param array $lesson The lesson
     */
    public function backPropagate($lesson)
    {
        $prepared = $this->prepareLesson($lesson);
        $this->mind->backPropagate($prepared[1]);
    }

    /**
     * Get the predicted output
     *
     * @return array
     */
    public function getResult()
    {
        $netOut = $this->mind->getOutput();
        $output = $this->outputMapping;
        foreach ($output as $name => $out) {
            if (is_array($out)) {
                foreach ($out as $k => $v) {
                    $output[$name][$k] = $netOut[$v];
                }
            } else {
                $output[$name] = $netOut[$out];
            }
        }
        return $output;
    }

    /**
     * Prepares the lesson for the neural network
     *
     * @param array $lesson The lesson
     * @return array The prepared lesson
     */
    public function prepareLesson($lesson)
    {
        if (!isset($lesson['input'])) {
            $lesson['input'] = [];
        }
        $preparedInputs = $this->prepareLessonsInput($lesson);

        if (!isset($lesson['output'])) {
            $lesson['output'] = [];
        }
        $preparedOutputs = $this->prepareLessonsOutput($lesson);

        return [$preparedInputs, $preparedOutputs];
    }

    /**
     * Prepares the inputs for a lesson
     *
     * @param array $lesson The lesson
     * @return array The inputs
     */
    private function prepareLessonsInput($lesson)
    {
        $preparedInputs = $this->inputs;

        foreach ($lesson['input'] as $name => $input) {
            if (is_array($input)) {
                foreach ($input as $v) {
                    if (isset($this->inputMapping[$name][$v])) {
                        $preparedInputs[$this->inputMapping[$name][$v]] = 1;
                    }
                }
            } elseif (isset($this->inputMapping[$name])) {
                if (is_array($this->inputMapping[$name]) && isset($this->inputMapping[$name][$input])) {
                    $preparedInputs[$this->inputMapping[$name][$input]] = 1;
                } else {
                    $preparedInputs[$this->inputMapping[$name]] = intval($input === true);
                }
            }
        }

        return $preparedInputs;
    }

    /**
     * Prepares the outputs for a lesson
     *
     * @param array $lesson The lesson
     * @return array The outputs
     */
    private function prepareLessonsOutput($lesson)
    {
        $preparedOutputs = $this->outputs;
        foreach ($lesson['output'] as $name => $output) {
            if (is_array($output)) {
                foreach ($output as $v) {
                    if (isset($this->outputMapping[$name][$v])) {
                        $preparedOutputs[$this->outputMapping[$name][$v]] = 1;
                    }
                }
            } elseif (isset($this->outputMapping[$name])) {
                if (is_array($this->outputMapping[$name]) && isset($this->outputMapping[$name][$output])) {
                    $preparedOutputs[$this->outputMapping[$name][$output]] = 1;
                } else {
                    $preparedOutputs[$this->outputMapping[$name]] = intval($output === true);
                }
            }
        }
        return $preparedOutputs;
    }
}