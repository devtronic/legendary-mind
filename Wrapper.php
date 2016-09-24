<?php

namespace Devtronic\LegendaryMind;

class Wrapper
{
    /**
     * @var array
     */
    public $inputs;

    /**
     * @var array
     */
    public $input_mapping;

    /**
     * @var array
     */
    public $outputs;

    /**
     * @var array
     */
    public $output_mapping;

    /**
     * @var Topology
     */
    private $topology;

    /**
     * @var Mind
     */
    private $mind;

    /**
     * @var integer
     */
    private $hiddenNeurons;

    /**
     * @var integer
     */
    private $hiddenLayers;

    public function __construct($hiddenNeurons = 3, $hiddenLayers = 1)
    {
        $this->hiddenNeurons = $hiddenNeurons;
        $this->hiddenLayers = $hiddenLayers;
    }

    public function initialize($properties, $outputs, $activation, $activation_derivative)
    {
        foreach ($properties as $name => $property) {
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

    public function archive($file)
    {
        file_put_contents($file, serialize($this));
    }

    public function restore($file)
    {
        $tmp = unserialize(file_get_contents($file));
        foreach ($tmp as $k => $v) {
            $this->{$k} = $v;
        }
        $this->mind->reInit();
    }

    public function train($training, $iterations = 1000, $learningRate = 0.2, $momentum = 0.01)
    {
        $lessons = [];
        foreach ($training as $lesson) {
            $lessons[] = $this->prepareLesson($lesson);
        }

        $this->mind->train($lessons, $iterations, $learningRate, $momentum);
    }

    public function propagate($lesson)
    {
        $prepared = $this->prepareLesson($lesson);
        $this->mind->propagate($prepared[0]);
    }

    public function backPropagate($lesson)
    {
        $prepared = $this->prepareLesson($lesson);
        $this->mind->backPropagate($prepared[1]);
    }

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