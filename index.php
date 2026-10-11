<?php

date_default_timezone_set('UTC');
require __DIR__ . '/vendor/autoload.php';

use App\CsvFileReader;
use App\DateParser;
use App\TimeCombiner;
use App\PairFinder;
use App\Dashboard;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

//ini_set('display_errors', '0');
//ini_set('log_errors', '1');

$report = new Dashboard();

$errors = [
    1 => 'The file is bigger than upload_max_filesize in php.ini.',
    2 => 'The file is too big.',
    3 => 'The file was only partially uploaded.',
    4 => 'No file was selected.',
    6 => 'The server has no temporary folder for uploads.',
    7 => 'The server could not save the uploaded file.',
];

$code = $_FILES['csv']['error'] ?? UPLOAD_ERR_NO_FILE;
if ($code !== UPLOAD_ERR_OK) {
    $report->uploadError($errors[$code] ?? 'Upload failed.');
    exit;
}

$tmpPath  = $_FILES['csv']['tmp_name'];
$origName = $_FILES['csv']['name'];

$parser = new DateParser();
$reader = new CsvFileReader($parser);
$rows   = $reader->read($tmpPath);

$rows = (new TimeCombiner())->merge($rows);

[$projects, $ids] = (new PairFinder())->find($rows);

$report->render($origName, count($rows), $projects, $ids);
