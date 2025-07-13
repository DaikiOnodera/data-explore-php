<?php

namespace Daikionodera\DataVisualize;

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class LimitedReadFilter implements IReadFilter
{
    private $maxRow = 0;

    public function __construct($maxRow) {
        $this->maxRow = $maxRow;
    }

    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool {
        return $row <= $this->maxRow;
    }
}

$excelFile = __DIR__ . '/../../Online Retail.xlsx';

echo "Reading first 20,000 rows of Excel file...\n";
$reader = IOFactory::createReader('Xlsx');
$reader->setReadDataOnly(true);
$reader->setReadEmptyCells(false);
$reader->setReadFilter(new LimitedReadFilter(20000));

$spreadsheet = $reader->load($excelFile);
$worksheet = $spreadsheet->getActiveSheet();

echo "Analyzing product combinations...\n";
$invoiceProducts = [];
$rowIndex = 0;

foreach ($worksheet->getRowIterator(2) as $row) {
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
    
    $data = [];
    foreach ($cellIterator as $cell) {
        $data[] = $cell->getValue();
    }
    
    if (count($data) >= 8 && !empty($data[0]) && !empty($data[2])) {
        $invoiceNo = trim($data[0]);
        $description = trim($data[2]);
        
        if (!isset($invoiceProducts[$invoiceNo])) {
            $invoiceProducts[$invoiceNo] = [];
        }
        $invoiceProducts[$invoiceNo][] = $description;
    }
    
    $rowIndex++;
    if ($rowIndex % 1000 === 0) {
        echo "Processed $rowIndex rows...\n";
    }
}

echo "Calculating product pairs...\n";
$productPairs = [];
$productCounts = [];

foreach ($invoiceProducts as $invoice => $products) {
    $uniqueProducts = array_unique($products);
    
    foreach ($uniqueProducts as $product) {
        if (!isset($productCounts[$product])) {
            $productCounts[$product] = 0;
        }
        $productCounts[$product]++;
    }
    
    $productList = array_values($uniqueProducts);
    
    for ($i = 0; $i < count($productList); $i++) {
        for ($j = $i + 1; $j < count($productList); $j++) {
            $pair = [$productList[$i], $productList[$j]];
            sort($pair);
            $pairKey = implode('|||', $pair);
            
            if (!isset($productPairs[$pairKey])) {
                $productPairs[$pairKey] = 0;
            }
            $productPairs[$pairKey]++;
        }
    }
}

arsort($productPairs);
arsort($productCounts);

$topProducts = array_slice($productCounts, 0, 15, true);
$topProductNames = array_keys($topProducts);

$relevantPairs = [];
foreach ($productPairs as $pairKey => $count) {
    $products = explode('|||', $pairKey);
    if (in_array($products[0], $topProductNames) && in_array($products[1], $topProductNames)) {
        $relevantPairs[$pairKey] = $count;
    }
}

$chartData = [];
foreach ($relevantPairs as $pairKey => $count) {
    $products = explode('|||', $pairKey);
    $chartData[] = [
        'product1' => substr($products[0], 0, 30),
        'product2' => substr($products[1], 0, 30),
        'count' => $count
    ];
}

file_put_contents(__DIR__ . '/../product_combinations.json', json_encode([
    'products' => array_map(function($p) { return substr($p, 0, 30); }, $topProductNames),
    'combinations' => $chartData
], JSON_PRETTY_PRINT));

echo "Analysis complete!\n";
echo "- product_combinations.json: Top product combinations\n";