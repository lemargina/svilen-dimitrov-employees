<?php
namespace App;

class Dashboard
{
    private const MODE = 'total';

    public function render(string $fileName, int $validRows, array $projects, array $ids): void
    {
        $this->start();

        echo '<p class="file">File: <b>' . htmlspecialchars($fileName) . '</b> &middot; '
            . $validRows . ' valid rows read</p>';

        if (!$projects) {
            echo '<p class="notice">No two employees worked on a common project in this file.</p>';
            $this->end();
            return;
        }

        $score = [];
        foreach ($projects as $key => $byProject) {
            $score[$key] = self::MODE === 'single'
                ? max($byProject)
                : array_sum($byProject);
        }

        $best = max($score);
        $winners = array_keys($score, $best);
        usort($winners, fn($a, $b) => $ids[$a] <=> $ids[$b]);

        $this->summary($winners, $ids, $projects, $best);
        $this->table($winners, $ids, $projects);
        $this->end();
    }

    public function uploadError(string $message): void
    {
        $this->start();
        echo '<p class="error">' . htmlspecialchars($message) . '</p>';
        $this->end();
    }

    private function summary(array $winners, array $ids, array $projects, int $best): void
    {
        foreach ($winners as $key) {
            [$idA, $idB] = $ids[$key];
            $n = count($projects[$key]);

            if (self::MODE === 'single') {
                echo "<p class=\"summary\">Employees <b>#$idA</b> and <b>#$idB</b> "
                   . "worked together longest on one project: <b>$best days</b>.</p>";
            } else {
                echo "<p class=\"summary\">Employees <b>#$idA</b> and <b>#$idB</b> "
                   . "worked together on <b>$n</b> common project(s), <b>$best days</b> in total.</p>";
            }
        }
    }

    private function table(array $winners, array $ids, array $projects): void
    {
        echo '<table class="grid"><thead><tr>';
        echo '<th>Employee ID #1</th><th>Employee ID #2</th>'
           . '<th>Project ID</th><th class="num">Days worked</th>';
        echo '</tr></thead><tbody>';

        foreach ($winners as $key) {
            [$idA, $idB] = $ids[$key];
            $byProject = $projects[$key];

            uksort($byProject, fn($p1, $p2) =>
                $byProject[$p2] <=> $byProject[$p1] ?: $p1 <=> $p2
            );

            foreach ($byProject as $projectId => $days) {
                echo "<tr><td>$idA</td><td>$idB</td>"
                   . "<td>" . htmlspecialchars((string) $projectId) . "</td>"
                   . "<td class=\"num\">$days</td></tr>";
            }
        }
        echo '</tbody></table>';
    }

    private function start(): void
    {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'
           . '<meta name="viewport" content="width=device-width,initial-scale=1">'
           . '<title>Result</title>'
           . '<link rel="stylesheet" href="style.css"></head><body><div class="page">'
           . '<h1>Employees who worked together the longest</h1>';
    }

    private function end(): void
    {
        echo '<p><a class="back" href="index.html">&larr; Choose another file</a></p>'
           . '</div></body></html>';
    }
}
