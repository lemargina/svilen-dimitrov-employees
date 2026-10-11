<?php
namespace App;

class IntervalMerger
{
    
public function merge(array $rows): array
{
    // 1. Sort rows by employee, project, and start date.
    usort($rows, function ($a, $b) {
        if ($a['emp'] !== $b['emp']) {
            return $a['emp'] <=> $b['emp'];
        }

        if ($a['project'] !== $b['project']) {
            return $a['project'] <=> $b['project'];
        }

        return $a['start'] <=> $b['start'];
    });

    $merged = [];

    // 2. Process each row.
    foreach ($rows as $row) {

        // The first row cannot overlap with a previous row.
        if (empty($merged)) {
            $merged[] = $row;
            continue;
        }

        // Get the last row we added.
        $lastIndex = count($merged) - 1;
        $last = $merged[$lastIndex];

        // 3. Check whether both rows belong to the same employee and project.
        $sameEmployee = $row['emp'] === $last['emp'];
        $sameProject = $row['project'] === $last['project'];

        if (!$sameEmployee || !$sameProject) {
            $merged[] = $row;
            continue;
        }

        // 4. Check whether the date ranges overlap or touch.
        if ($row['start'] > $last['end']) {
            $merged[] = $row;
            continue;
        }

        // 5. Extend the existing period when necessary.
        if ($row['end'] > $last['end']) {
            $merged[$lastIndex]['end'] = $row['end'];
        }

        // 6. Update the readable date values.
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
