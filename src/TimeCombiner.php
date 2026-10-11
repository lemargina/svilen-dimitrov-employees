<?php
namespace App;

class TimeCombiner
{
    
public function merge(array $rows): array
{
   
   usort($rows, function ($a, $b) {
    if ($a['emp'] !== $b['emp']) {
        return $a['emp'] < $b['emp'] ? -1 : 1;
    }

    if ($a['project'] !== $b['project']) {
        return $a['project'] < $b['project'] ? -1 : 1;
    }

    if ($a['start'] !== $b['start']) {
        return $a['start'] < $b['start'] ? -1 : 1;
    }

    return 0;
    });

    $merged = [];

  
    foreach ($rows as $row) {

     
        if (empty($merged)) {
            $merged[] = $row;
            continue;
        }

     
        $lastIndex = count($merged) - 1;
        $last = $merged[$lastIndex];

   
        $sameEmployee = $row['emp'] === $last['emp'];
        $sameProject = $row['project'] === $last['project'];

        if (!$sameEmployee || !$sameProject) {
            $merged[] = $row;
            continue;
        }

       
        if ($row['start'] > $last['end']) {
            $merged[] = $row;
            continue;
        }

 
        if ($row['end'] > $last['end']) {
            $merged[$lastIndex]['end'] = $row['end'];
        }

  
        $merged[$lastIndex]['from'] = date(
            'Y-m-d',
            $merged[$lastIndex]['start']
        );

        $merged[$lastIndex]['to'] = date(
            'Y-m-d',
            $merged[$lastIndex]['end']
        );
    }

    return $merged;
}

}
