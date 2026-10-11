<?php

date_default_timezone_set('UTC');
require __DIR__ . '/vendor/autoload.php';

use App\CsvReader;
use App\DateParser;
use App\IntervalMerger;
use App\PairFinder;
use App\Dashboard;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

ini_set('display_errors', '0');
ini_set('log_errors', '1');

$report = new Dashboard();

$errors = [
    UPLOAD_ERR_INI_SIZE   => 'The file is bigger than upload_max_filesize in php.ini.',
    UPLOAD_ERR_FORM_SIZE  => 'The file is too big.',
    UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded.',
    UPLOAD_ERR_NO_FILE    => 'No file was selected.',
    UPLOAD_ERR_NO_TMP_DIR => 'The server has no temporary folder for uploads.',
    UPLOAD_ERR_CANT_WRITE => 'The server could not save the uploaded file.',
];
$code = $_FILES['csv']['error'] ?? UPLOAD_ERR_NO_FILE;
if ($code !== UPLOAD_ERR_OK) {
    $report->uploadError($errors[$code] ?? 'Upload failed.');
    exit;
}

$tmpPath  = $_FILES['csv']['tmp_name'];
$origName = $_FILES['csv']['name'];

$parser = new DateParser();
$reader = new CsvReader($parser);
$rows   = $reader->read($tmpPath);

$rows = (new IntervalMerger())->merge($rows);

[$projects, $ids] = (new PairFinder())->find($rows);

$report->render($origName, count($rows), $projects, $ids);