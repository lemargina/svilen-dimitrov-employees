<?php
namespace App;

class CsvReader
{
    /** @var DateParser */
    private $dates;

    public function __construct(DateParser $dates)
    {
        $this->dates = $dates;
    }

    public function read(string $path): array
    {
        $rows = [];
        $fh = fopen($path, 'r');
        while (($line = fgetcsv($fh)) !== false) {
            if (!is_numeric($line[0] ?? '') || !is_numeric($line[1] ?? '')) {
                continue;
            }
            $from = $this->dates->toYmd($line[2] ?? '');
            $to   = $this->dates->toYmd($line[3] ?? '');
            $rows[] = [
                'emp'     => $line[0],
                'project' => $line[1],
                'from'    => $from,
                'to'      => $to,
                'start'   => strtotime($from),
                'end'     => strtotime($to),
            ];
        }
        fclose($fh);
        return $rows;
    }
}