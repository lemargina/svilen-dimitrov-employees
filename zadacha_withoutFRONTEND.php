<?php

// $filename = 'project_worktime.csv';
 $filename = 'employeess.csv';
//$filename = 'test.csv';

require __DIR__ . '/vendor/autoload.php';
use Carbon\Carbon;

// **************************************************************************************
//                                        BEGIN FUNCTIONS
// **************************************************************************************


function normalizeDate(?string $date): ?string
{
    if (empty($date)) {
        return Carbon::now()->format('Y-m-d');
    }
    $date = trim($date);
    if (ctype_digit($date) && strlen($date) >= 9 && strlen($date) <= 10) {
        try {
            return Carbon::createFromTimestamp((int) $date)
                ->format('Y-m-d');
        } catch (\Throwable $e) {
        }
    }
    $formats = [
        'Ymd',
        'Y-m-d',
        'Y/m/d',
        'Y.m.d',
        'd/m/Y',
        'd-m-Y',
        'd.m.Y',
        'j/n/Y',
        'j-n-Y',
        'j.n.Y',
        'd/m/y',
        'd-m-y',
        'd.m.y',
        'j/n/y',
        'j-n-y',
        'j.n.y',
        'M j Y',
        'M j, Y',
        'j M Y',
        'j M, Y',
        'F j Y',
        'F j, Y',
        'j F Y',
        'j F, Y',
        'Y-m-d H:i:s',
        'Y-m-d H:i',
        'd.m.Y H:i:s',
        'd.m.Y H:i',
        'd/m/Y H:i:s',
        'd/m/Y H:i',
        'Y-m-d\TH:i:s',
        'Y-m-d\TH:i:sP',
    ];
    foreach ($formats as $format) {
        try {
            $parsed = Carbon::createFromFormat('!' . $format, $date);
            $errors = Carbon::getLastErrors();
            if (        // $resultbyPairs[$key]['projects'][] = [
        //     'projectId' => $pair['projectId'],
        //     'days' => $overlapDays,
        // ];

                $parsed &&
                (
                    $errors === false ||
                    ($errors['warning_count'] === 0 && $errors['error_count'] === 0)
                )
            ) {
                return $parsed->format('Y-m-d');
            }
        } catch (\Throwable $e) {
            // не е този формат
        }
    }
    return Carbon::now()->format('Y-m-d');
}

function mergeDateRanges(array $data): array
{
    if (empty($data)) {
        return [];
    }
    // Първо сортираме по [0], [1] и startDate
    usort($data, function ($a, $b) {
        if ($a[0] != $b[0]) {        // $resultbyPairs[$key]['projects'][] = [
        //     'projectId' => $pair['projectId'],
        //     'days' => $overlapDays,
        // ];

            return $a[0] <=> $b[0];
        }
        if ($a[1] != $b[1]) {
            return $a[1] <=> $b[1];
        }
        return $a['startDate'] <=> $b['startDate'];
    });
    $result = [];
    foreach ($data as $item) {
        if (empty($result)) {
            $result[] = $item;
            continue;
        }
        $lastIndex = count($result) - 1;
        $last = $result[$lastIndex];
        // Трябва да са от една и съща група
        $sameGroup =
            $item[0] == $last[0] &&
            $item[1] == $last[1];
        // И периодите да се припокриват
        $overlap =
            $item['startDate'] <= $last['endDate'];
        if ($sameGroup && $overlap) {
            // Най-ранно начало
            $startDate = min(
                $last['startDate'],
                $item['startDate']
            );
            // Най-късен край
            $endDate = max(
                $last['endDate'],
                $item['endDate']
            );
            $result[$lastIndex]['startDate'] = $startDate;
            $result[$lastIndex]['endDate']   = $endDate;
            $result[$lastIndex][2] = date('Y-m-d', $startDate);
            $result[$lastIndex][3] = date('Y-m-d', $endDate);
            // $result[$lastIndex]['days'] =
            //     (int)(($endDate - $startDate) / 86400);
        } else {
            // Няма припокриване или е друга група
            // => запазваме елемента както си е
            $result[] = $item;
        }
    }
    return $result;
}




function getAllPairs($arr) {
    $pairs = [];
    $len = count($arr);
    for ($i = 0; $i < $len - 1; $i++) {
        for ($j = $i + 1; $j < $len; $j++) {        // $resultbyPairs[$key]['projects'][] = [
            $pairs[] = [$arr[$i], $arr[$j]];
        }
    }
    return $pairs;
}

function overlapsDays($startWorker1, $endWorker1, $startWorker2, $endWorker2) : int {
    $startW1 = Carbon::parse($startWorker1);
    $endW1   = Carbon::parse($endWorker1);
    $startW2 = Carbon::parse($startWorker2);
    $endW2   = Carbon::parse($endWorker2);

    $start = $startW1->greaterThan($startW2) ? $startW1 : $startW2;
    $end   = $endW1->lessThan($endW2) ? $endW1 : $endW2;

   

    if ($start->gt($end)) {
        return 0;
    }

    return $start->diffInDays($end) + 1;
}

// **************************************************************************************
//                                         END FUNCTIONS
// **************************************************************************************
// echo "<pre>";


$file = fopen($filename, 'r');

$employeArray = [];

while (($line = fgetcsv($file)) !== FALSE) {

    if (!is_numeric($line[0]) || !is_numeric($line[1])) {
        continue; 
    }
    $newline = [
        0 => $line[0],
        1 => $line[1],
        2 => normalizeDate($line[2]),
        3 => normalizeDate($line[3]),        // $resultbyPairs[$key]['projects'][] = [
      
        'startDate' => strtotime(normalizeDate($line[2])),
        'endDate' => strtotime(normalizeDate($line[3])),
        
    ];
    $employeArray[] = $newline;

}
fclose($file);

//  припокриващите се периоди за един и същи служител и проект се разкарват
$data = mergeDateRanges($employeArray);
// echo "\n\n".print_r($data,true)."\n\n";


// ------------------------------------------------------------
// $uniqueEmployees = [];
// foreach ($data as $employee) {
//     $uniqueEmployees[]= $employee[0];
// }
// $uniqueEmployees = array_unique($uniqueEmployees);
// $uniqueEmployees = array_values($uniqueEmployees);
// echo "\n\n".print_r($uniqueEmployees,true)."\n\n";

// $pairs = getAllPairs($uniqueEmployees);
// echo "\n\n".print_r($pairs,true)."\n\n";

$uniqueProjects = [];
foreach ($data as $employee) {
    $uniqueProjects[]= $employee[1];
}
$uniqueProjects = array_unique($uniqueProjects);
$uniqueProjects = array_values($uniqueProjects);
// echo "\n\n".print_r($uniqueProjects,true)."\n\n";


$workersByProject = [];
foreach ($uniqueProjects as $projectId) {
    $workersByProject[$projectId] = [];
    foreach ($data as $employee) {
        if ($employee[1] == $projectId) {   
            $workersByProject[$projectId][] = $employee;
        }
    }
}
// echo "\n\n".print_r($workersByProject,true)."\n\n";


$workdaysByProject = [];

foreach ($workersByProject as $projectId => $workers) {
    $pairs = getAllPairs($workers);
    // echo "\n\nProject ID: $projectId\n";
    foreach ($pairs as $pair) {
        $emp1 = $pair[0];
        $emp2 = $pair[1];
        $overlapDays = overlapsDays(
            $emp1[2], // startDate of empl1
            $emp1[3], // endDate of empl1
            $emp2[2], // startDate of empl2
            $emp2[3]  // endDate of empl2
        );
        if ($overlapDays > 0) {
            // echo "БАчкатУр {$emp1[0]} и {$emp2[0]} са се мелдвали за {$overlapDays} дни.\n";
            $workdaysByProject[$overlapDays][] = [
                'projectId' => $projectId,
                'emp1' => $emp1[0],
                'emp2' => $emp2[0],
                'days' => $overlapDays,
            ];
        }
    }
}


ksort($workdaysByProject);
// echo "\n\n".print_r($workdaysByProject,true)."\n\n";
$lastElement = end($workdaysByProject);
echo "\n\nНай-работните по един проект са :\n";
foreach ($lastElement as $pair) {
    echo "Проект ID: {$pair['projectId']}, служители: {$pair['emp1']} и {$pair['emp2']}, са консумирали заедно проекта за {$pair['days']} дни.\n";
}


$resultbyPairs = [];
$totaldaysMAX = 0;
$totalDaysPairKey = '';

foreach ($workdaysByProject as $overlapDays => $pairs) {
    foreach ($pairs as $pair) {
        $e1 = $pair['emp1'];
        $e2 = $pair['emp2'];

        $key = $e1 < $e2 ? "{$e1}-{$e2}" : "{$e2}-{$e1}";
        if (!isset($resultbyPairs[$key])) {
            $resultbyPairs[$key] = [
                'emp1' => $emp1,
                'emp2' => $emp2,
                'totalDays' => 0,
            ];
        }

        $resultbyPairs[$key]['totalDays'] += $overlapDays;

        if( $resultbyPairs[$key]['totalDays'] > $totaldaysMAX) {
            $totaldaysMAX = $resultbyPairs[$key]['totalDays'];
            $totalDaysPairKey = $key;
            // echo "X: ".$totalDaysPairKey." за ".$totaldaysMAX." дни.\n";
        }

    }
}
// echo "\n\n".print_r($resultbyPairs,true)."\n\n";

echo "\n\nНай-работните по всички проекти са :\n";
echo "Служители: ".$totalDaysPairKey." за ".$totaldaysMAX." дни.\n";







?>