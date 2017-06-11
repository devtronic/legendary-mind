[![GitHub tag](https://img.shields.io/packagist/v/Devtronic/legendary-mind.svg)](https://github.com/Devtronic/legendary-mind)
[![Packagist](https://img.shields.io/packagist/l/Devtronic/legendary-mind.svg)](https://github.com/Devtronic/legendary-mind/blob/master/LICENSE)
[![Travis](https://img.shields.io/travis/Devtronic/legendary-mind.svg)](https://travis-ci.org/Devtronic/legendary-mind/)
[![Packagist](https://img.shields.io/packagist/dt/Devtronic/legendary-mind.svg)](https://github.com/Devtronic/legendary-mind)

# Legendary Mind

Legendary Mind is an easy to use Neural Network written in PHP
  - Wrapper Class For Handling The Network
  - Archive And Restore Neural Networks Serialized In Text-Files

### Installation
```bash
composer require devtronic/legendary-mind
```

### Usage
#### Standalone Network
```php
<?php

use Devtronic\Layerless\Activator\TanHActivator;
use Devtronic\LegendaryMind\Mind;

require_once 'vendor/autoload.php';

// Create the topology for the net
$topology = [2, 3, 1];

// Set the activator
$activator = new TanHActivator();

// Instantiate the Mind
$mind = new Mind($topology, $activator);

// Setup XOR Lessons
$lessons = [
    [
        [0, 0], # Inputs
        [0]     # Outputs
    ],
    [
        [0, 1], # Inputs
        [1]     # Outputs
    ],
    [
        [1, 0], # Inputs
        [1]     # Outputs
    ],
    [
        [1, 1], # Inputs
        [0]     # Outputs
    ],
];

// Train the lessons
$mind->train($lessons);

// Setup the check lesson
$test = [1, 0];
$expected = [1];

// Propagate the check lesson
$mind->predict($test);

// Print the Output
print_r($mind->getOutput());

// Backpropagate
$mind->backPropagate($expected);
```

#### With Wrapper (recommended)
```php
<?php

use Devtronic\Layerless\Activator\TanHActivator;
use Devtronic\LegendaryMind\Wrapper;

require_once 'vendor/autoload.php';

// Set the activator function
$activator = new TanHActivator();

$wrapper = new Wrapper($hiddenNeurons = 3, $hiddenLayers = 1);

// Possible input values
$properties = [
    'color' => ['red', 'pink', 'blue', 'green'],
    'hair_length' => ['short', 'long']
];

$outputs = [
    'gender' => ['male', 'female']
];

// Setup the wrapper
$wrapper->initialize($properties, $outputs, $activator);

// Setup the lessons
$lessons = [
    [
        'input' => [
            'color' => 'red',
            'hair_length' => 'long',
        ],
        'output' => [
            'gender' => 'female'
        ],
    ],
    [
        'input' => [
            'color' => 'blue',
            'hair_length' => 'short',
        ],
        'output' => [
            'gender' => 'male'
        ],
    ],
    [
        'input' => [
            'color' => 'red',
            'hair_length' => 'short',
        ],
        'output' => [
            'gender' => 'male'
        ],
    ],
];

// Train the lessons
$wrapper->train($lessons);

// Setup the check lesson
$test_lesson = [
    'input' => [
        'color' => ['pink', 'green'],
        'hair_length' => 'long',
    ],
    'output' => [
        'gender' => 'female'
    ]
];

// Propagate the check lesson
$wrapper->predict($test_lesson);

// Print the Output
print_r($wrapper->getResult());

// Backpropagate
$wrapper->backPropagate($test_lesson);
```

#### Archive and restore the network (only with wrapper, Line 16 and Line 67)
```php
<?php

use Devtronic\Layerless\Activator\TanHActivator;
use Devtronic\LegendaryMind\Wrapper;

require_once 'vendor/autoload.php';

// Set the activation function
$activator = new TanHActivator();
$wrapper = new Wrapper($hiddenNeurons = 3, $hiddenLayers = 1);

$network_file = 'network.txt';

if (is_file($network_file)) {
    // Restore the Network
    $wrapper->restore($network_file);
} else {

    // Possible input values
    $properties = [
        'color' => ['red', 'pink', 'blue', 'green'],
        'hair_length' => ['short', 'long']
    ];

    $outputs = [
        'gender' => ['male', 'female']
    ];

    // Setup the wrapper
    $wrapper->initialize($properties, $outputs, $activator);

    // Setup the lessons
    $lessons = [
        [
            'input' => [
                'color' => 'red',
                'hair_length' => 'long',
            ],
            'output' => [
                'gender' => 'female'
            ],
        ],
        [
            'input' => [
                'color' => 'blue',
                'hair_length' => 'short',
            ],
            'output' => [
                'gender' => 'male'
            ],
        ],
        [
            'input' => [
                'color' => 'red',
                'hair_length' => 'short',
            ],
            'output' => [
                'gender' => 'male'
            ],
        ],
    ];

    // Train the lessons
    $wrapper->train($lessons);

    // Archive the Network
    $wrapper->archive($network_file);
}

// Setup the check lesson
$test_lesson = [
    'input' => [
        'color' => ['pink', 'green'],
        'hair_length' => 'long',
    ],
    'output' => [
        'gender' => 'female'
    ]
];

// Propagate the check lesson
$wrapper->predict($test_lesson);

// Print the Output
print_r($wrapper->getResult());

// Backpropagate
$wrapper->backPropagate($test_lesson);
```
