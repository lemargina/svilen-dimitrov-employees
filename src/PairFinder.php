<?php
namespace App;

class PairFinder
{
    public function find(array $rows): array
    {
        $byProject = [];
        foreach ($rows as $r) {
            $byProject[$r['project']][] = $r;
        }

        $projects = [];
        $ids      = [];

        foreach ($byProject as $project => $workers) {
            usort($workers, fn($a, $b) => $a['start'] <=> $b['start']);
            $n = count($workers);

            for ($i = 0; $i < $n - 1; $i++) {
                for ($j = $i + 1; $j < $n && $workers[$j]['start'] <= $workers[$i]['end']; $j++) {
                    $days = $this->overlapDays($workers[$i], $workers[$j]);
                    if ($days <= 0) continue;

                    $a = (int) $workers[$i]['emp'];
                    $b = (int) $workers[$j]['emp'];
                    [$idA, $idB] = $a < $b ? [$a, $b] : [$b, $a];

                    $key = "$idA-$idB";
                    $ids[$key] = [$idA, $idB];
                    $projects[$key][$project] = ($projects[$key][$project] ?? 0) + $days;
                }
            }
        }
        return [$projects, $ids];
    }

    private function overlapDays(array $a, array $b): int
    {
        $start = max($a['start'], $b['start']);
        $end   = min($a['end'],   $b['end']);
        return $start > $end ? 0 : (int)(($end - $start) / 86400) + 1;
    }
}
