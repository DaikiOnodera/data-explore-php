<?php
require __DIR__ . '/../vendor/autoload.php';

$combinationsFile = __DIR__ . '/../product_combinations.json';

if (!file_exists($combinationsFile)) {
    echo "Error: product_combinations.json not found. Please run quick_analyze.php first.\n";
    exit(1);
}

$combinationsData = json_decode(file_get_contents($combinationsFile), true);
$products = $combinationsData['products'];
$combinations = $combinationsData['combinations'];

$allData = [];
$allBackgroundColors = [];
$maxCount = 0;

foreach ($combinations as $combo) {
    if ($combo['count'] > $maxCount) {
        $maxCount = $combo['count'];
    }
}

foreach ($combinations as $combo) {
    $xIndex = array_search($combo['product1'], $products);
    $yIndex = array_search($combo['product2'], $products);
    
    if ($xIndex !== false && $yIndex !== false) {
        $allData[] = [
            'x' => $xIndex,
            'y' => $yIndex,
            'r' => max(5, min(30, ($combo['count'] / $maxCount) * 30)),
            'count' => $combo['count']
        ];
        $allBackgroundColors[] = sprintf('rgba(%d, %d, %d, 0.7)', 
            rand(50, 255), rand(50, 255), rand(50, 255));
    }
}

$chartConfig = [
    'type' => 'bubble',
    'data' => [
        'datasets' => [[
            'data' => $allData,
            'backgroundColor' => $allBackgroundColors
        ]]
    ],
    'options' => [
        'responsive' => false,
        'plugins' => [
            'datalabels' => [
                'display' => 'auto',
                'color' => 'white',
                'align' => 'center',
                'anchor' => 'center',
                'font' => [
                    'size' => 12,
                    'weight' => 'bold'
                ],
                'formatter' => 'function(value, context) { return value.count; }'
            ],
            'title' => [
                'display' => true,
                'text' => 'Product Combination Frequency'
            ],
            'legend' => [
                'display' => false
            ]
        ],
        'scales' => [
            'x' => [
                'type' => 'category',
                'labels' => $products,
                'position' => 'bottom',
                'ticks' => [
                    'autoSkip' => false,
                    'maxRotation' => 90,
                    'minRotation' => 45
                ]
            ],
            'y' => [
                'type' => 'category',
                'labels' => $products,
                'position' => 'left',
                'ticks' => [
                    'autoSkip' => false
                ]
            ]
        ],
        'layout' => [
            'padding' => 20
        ]
    ]
];

$chart = new QuickChart();
$chart->setConfig(json_encode($chartConfig));
$chart->setWidth(1200);
$chart->setHeight(1200);
$chart->setBackgroundColor('white');

echo $chart->getShortUrl() . PHP_EOL;