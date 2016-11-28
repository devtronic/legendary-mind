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
 * Network Wrapper
 *
 * @see https://github.com/Devtronic/legendary-mind#with-wrapper-recommended
 *
 * @author Julian Finkler <admin@developer-heaven.de>
 * @package Devtronic\LegendaryMind
 */
class Wrapper
{
    /** @var array */
    public $inputs;

    /** @var array */
    public $input_mapping;

    /** @var array */
    public $outputs;

    /** @var array */
    public $output_mapping;

    /** @var Topology */
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
     * @param string $activation The activation method
     * @param string $activation_derivative The derivative of the activation method
     */
    public function initialize($inputs, $outputs, $activation, $activation_derivative)
    {
        foreach ($inputs as $name => $property) {
            if (is_array($property)) {
                foreach ($property as $prop) {
                    $this->inputs[] = 0;
                    $this->input_mapping[$name][$prop] = count($this->inputs) - 1;
                }
            } else {
                $this->inputs[] = 0;
                $this->input_mapping[$property] = count($this->inputs) - 1;
            }
        }

        foreach ($outputs as $name => $property) {

            if (is_array($property)) {
                foreach ($property as $prop) {
                    $this->outputs[] = 0;
                    $this->output_mapping[$name][$prop] = count($this->outputs) - 1;
                }
            } else {
                $this->outputs[] = 0;
                $this->output_mapping[$property] = count($this->outputs) - 1;
            }
        }

        $neuronsInput = count($this->inputs);
        $neuronsHidden = $this->hiddenNeurons;
        $hiddenLayers = $this->hiddenLayers;
        $neuronsOutput = count($this->outputs);

        $this->topology = new Topology($neuronsInput, $neuronsHidden, $hiddenLayers, $neuronsOutput);

        $this->mind = new Mind($this->topology, $activation, $activation_derivative);
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
        $this->mind->reInit();
    }

    /**
     * Trains the network
     *
     * @param array $training The training
     * @param int $iterations The iterations
     * @param float $learningRate The learning rate
     * @param float $momentum The momentum
     */
    public function train($training, $iterations = 1000, $learningRate = 0.2, $momentum = 0.01)
    {
        $lessons = [];
        foreach ($training as $lesson) {
            $lessons[] = $this->prepareLesson($lesson);
        }

        $this->mind->train($lessons, $iterations, $learningRate, $momentum);
    }

    /**
     * Predict the output for input values
     *
     * @param $lesson
     * @deprecated 1.0.3 Call predict() instead. Will be removed in 1.0.4
     */
    public function propagate($lesson)
    {
        $this->predict($lesson);
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
        $net_out = $this->mind->getOutput();
        $output = $this->output_mapping;
        foreach ($output as $name => $out) {
            if (is_array($out)) {
                foreach ($out as $k => $v) {
                    $output[$name][$k] = $net_out[$v];
                }
            } else {
                $output[$name] = $net_out[$out];
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
        $prepared_inputs = $this->inputs;
        $prepared_outputs = $this->outputs;

        if (isset($lesson['input'])) {
            foreach ($lesson['input'] as $name => $input) {
                if (is_array($input)) {
                    foreach ($input as $v) {
                        if (isset($this->input_mapping[$name][$v])) {
                            $prepared_inputs[$this->input_mapping[$name][$v]] = 1;
                        }
                    }
                } else {
                    if (is_array($this->input_mapping[$name])) {
                        if (isset($this->input_mapping[$name][$input])) {
                            $prepared_inputs[$this->input_mapping[$name][$input]] = 1;
                        }
                    } else {
                        if (isset($this->input_mapping[$name])) {
                            $prepared_inputs[$this->input_mapping[$name]] = $input === true || $input == 1 ? 1 : 0;
                        }
                    }
                }
            }
        }
        if (isset($lesson['output'])) {
            foreach ($lesson['output'] as $name => $output) {
                if (is_array($output)) {
                    foreach ($output as $v) {
                        if (isset($this->output_mapping[$name][$v])) {
                            $prepared_outputs[$this->output_mapping[$name][$v]] = 1;
                        }
                    }
                } else {
                    if (is_array($this->output_mapping[$name])) {
                        if (isset($this->output_mapping[$name][$output])) {
                            $prepared_outputs[$this->output_mapping[$name][$output]] = 1;
                        }
                    } else {
                        if (isset($this->output_mapping[$name])) {
                            $prepared_outputs[$this->output_mapping[$name]] = $output === true || $output == 1 ? 1 : 0;
                        }
                    }
                }
            }
        }
        return [$prepared_inputs, $prepared_outputs];
    }
}