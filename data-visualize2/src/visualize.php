<?php

use CpChart\Data;
use CpChart\Chart\Bar;

require 'vendor/autoload.php';

// Prepare data
$data = new Data();
$data->addPoints([12.5, 9.3, 15.2], "Mean");
$data->setSerieDescription("Mean", "Average");
$data->addPoints(["A", "B", "C"], "Labels");
$data->setAbscissa("Labels");

// Create chart
$chart = new Bar(700, 400);
$chart->setFontProperties(["FontName" => "Forgotte.ttf", "FontSize" => 12]);
$chart->setGraphArea(60, 40, 650, 350);
$chart->drawScale($data);
$chart->drawBarChart($data, ["DisplayValues" => true]);

// Save image
$chart->render("output.png");