<?php

#: Before
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
use mysli\toolkit\type\arr;


#: Define Slovenia
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$data = [
    'name'       => 'Slovenia',
    'capital'    => 'Ljubljana',
    'area'       => 20273,
    'population' => 2061085,
    'hdi'        => 0.874
];


#: Define Slovenia+Russia
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
$data = [
    'Slovenia' => [
        'capital'    => 'Ljubljana',
        'area'       => 20273,
        'population' => 2061085,
        'hdi'        => 0.874
    ],
    'Russia' => [
        'capital'    => 'Moscow',
        'area'       => 17098242,
        'population' => 143975923,
        'hdi'        => 0.778
    ]
];


#: Test Basic
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Use Slovenia
#: Expect Output <<<READABLE
echo arr::readable($data);

<<<READABLE
name       : Slovenia
capital    : Ljubljana
area       : 20273
population : 2061085
hdi        : 0.874
READABLE;


#: Test Indent
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Use Slovenia
#: Expect Output <<<READABLE
echo arr::readable($data, 3);

<<<READABLE
   name       : Slovenia
   capital    : Ljubljana
   area       : 20273
   population : 2061085
   hdi        : 0.874
READABLE;


#: Test Multi Dimensional
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Use Slovenia+Russia
#: Expect Output <<<READABLE
echo arr::readable($data);

<<<READABLE
Slovenia
  capital    : Ljubljana
  area       : 20273
  population : 2061085
  hdi        : 0.874
Russia
  capital    : Moscow
  area       : 17098242
  population : 143975923
  hdi        : 0.778
READABLE;


#: Test Multi Dimensional, Indent, Step
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
#: Use Slovenia+Russia
#: Expect Output <<<READABLE
echo arr::readable($data, 3, 5);

<<<READABLE
   Slovenia
        capital    : Ljubljana
        area       : 20273
        population : 2061085
        hdi        : 0.874
   Russia
        capital    : Moscow
        area       : 17098242
        population : 143975923
        hdi        : 0.778
READABLE;
