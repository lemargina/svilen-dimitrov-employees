<?php
namespace App;

class IntervalMerger
{
    public function merge(array $rows): array
    {
        usort($rows, fn($a, $b) =>
            [$a['emp'], $a['project'], $a['start']]
            <=>
            [$b['emp'], $b['project'], $b['start']]
        );

        $merged = [];
        foreach ($rows as $row) {
            $last = end($merged);

            $sameGroup = $last
                && $row['emp'] === $last['emp']
                && $row['project'] === $last['project'];

            $overlaps = $sameGroup && $row['start'] <= $last['end'];

            if (!$overlaps) {
                $merged[] = $row;
                continue;
            }

            $k = array_key_last($merged);
            $merged[$k]['start'] = min($last['start'], $row['start']);
            $merged[$k]['end']   = max($last['end'],   $row['end']);
            $merged[$k]['from']  = date('Y-m-d', $merged[$k]['start']);
            $merged[$k]['to']    = date('Y-m-d', $merged[$k]['end']);
        }
        return $merged;
    }
}