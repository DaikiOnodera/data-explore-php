<?php

namespace Daikionodera\DataVisualize;

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class ChunkReadFilter implements IReadFilter
{
    private $startRow = 0;
    private $endRow = 0;

    public function setRows($startRow, $chunkSize) {
        $this->startRow = $startRow;
        $this->endRow = $startRow + $chunkSize;
    }

    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool {
        if (($row == 1) || ($row >= $this->startRow && $row < $this->endRow)) {
            return true;
        }
        return false;
    }
}

$excelFile = __DIR__ . '/../../Online Retail.xlsx';

echo "Reading Excel file info...\n";
$reader = IOFactory::createReader('Xlsx');
$reader->setReadDataOnly(true);
$reader->setReadEmptyCells(false);

$worksheetInfo = $reader->listWorksheetInfo($excelFile);
$totalRows = $worksheetInfo[0]['totalRows'];
$totalColumns = $worksheetInfo[0]['totalColumns'];

echo "Total rows: $totalRows\n";
echo "Total columns: $totalColumns\n";

$chunkFilter = new ChunkReadFilter();
$reader->setReadFilter($chunkFilter);

echo "Reading headers and first 10 rows...\n";
$chunkFilter->setRows(1, 11);
$spreadsheet = $reader->load($excelFile);
$worksheet = $spreadsheet->getActiveSheet();
$data = $worksheet->toArray();

$headers = array_shift($data);

echo "Writing head.csv...\n";
$headFile = fopen(__DIR__ . '/../head.csv', 'w');
fputcsv($headFile, $headers);
foreach ($data as $row) {
    if (!empty(array_filter($row))) {
        fputcsv($headFile, $row);
    }
}
fclose($headFile);

echo "Writing shape.csv...\n";
$shapeFile = fopen(__DIR__ . '/../shape.csv', 'w');
fputcsv($shapeFile, ['Metric', 'Value']);
fputcsv($shapeFile, ['Total Rows', $totalRows]);
fputcsv($shapeFile, ['Total Columns', $totalColumns]);
fputcsv($shapeFile, ['Column Names', implode(', ', $headers)]);
fclose($shapeFile);

echo "Analyzing product combinations...\n";
$invoiceProducts = [];
$chunkSize = 5000;

for ($startRow = 2; $startRow <= $totalRows; $startRow += $chunkSize) {
    echo "Processing rows $startRow to " . min($startRow + $chunkSize - 1, $totalRows) . "...\n";
    
    $chunkFilter->setRows($startRow, $chunkSize);
    $spreadsheet = $reader->load($excelFile);
    $worksheet = $spreadsheet->getActiveSheet();
    $chunkData = $worksheet->toArray();
    
    foreach ($chunkData as $row) {
        if (count($row) >= 8 && !empty($row[0])) {
            $invoiceNo = trim($row[0]);
            $description = trim($row[2] ?? '');
            
            if (!empty($invoiceNo) && !empty($description) && $invoiceNo !== 'InvoiceNo') {
                if (!isset($invoiceProducts[$invoiceNo])) {
                    $invoiceProducts[$invoiceNo] = [];
                }
                $invoiceProducts[$invoiceNo][] = $description;
            }
        }
    }
    
    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);
    
    if ($startRow > 50000) {
        echo "Limiting analysis to first 50,000 rows...\n";
        break;
    }
}

echo "Calculating product pairs...\n";
$productPairs = [];
foreach ($invoiceProducts as $invoice => $products) {
    $uniqueProducts = array_unique($products);
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

$topPairs = array_slice($productPairs, 0, 100, true);

$uniqueProducts = [];
foreach ($topPairs as $pairKey => $count) {
    $products = explode('|||', $pairKey);
    $uniqueProducts[$products[0]] = true;
    $uniqueProducts[$products[1]] = true;
}
$uniqueProducts = array_keys($uniqueProducts);

$chartData = [];
foreach ($topPairs as $pairKey => $count) {
    $products = explode('|||', $pairKey);
    $chartData[] = [
        'product1' => $products[0],
        'product2' => $products[1],
        'count' => $count
    ];
}

file_put_contents(__DIR__ . '/../product_combinations.json', json_encode([
    'products' => array_slice($uniqueProducts, 0, 20),
    'combinations' => array_slice($chartData, 0, 50)
], JSON_PRETTY_PRINT));

echo "Analysis complete!\n";
echo "- head.csv: First 10 rows of data\n";
echo "- shape.csv: Dataset dimensions\n";
echo "- product_combinations.json: Top product combinations\n";