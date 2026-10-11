<?php
namespace App;

class DateParser
{
    private $formats  = [
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

    public function toYmd(?string $date): string
    {
        $date = trim((string) $date);
        if ($date === '') return date('Y-m-d');

        if (preg_match('/^\d{9,10}$/', $date)) {
            return date('Y-m-d', (int) $date);
        }

        foreach ($formats as $f) {
            $d = \DateTime::createFromFormat('!' . $f, $date);
            if ($d && $d->format($f) === $date) {
                return $d->format('Y-m-d');
            }
        }
        return date('Y-m-d');
    }
}
