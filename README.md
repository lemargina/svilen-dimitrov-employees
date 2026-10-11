# svilen-dimitrov-employees
The  task for Sirma for how long two coworkers works on the common projects longest period

# Zadacha — Employees who worked together the longest (OOP)

Upload a CSV of employee/project work periods, get back the pair(s) of
employees who spent the most days together across their common projects.

This is the **OOP version** — small classes, each with one job.

## Requirements

- PHP 7.4+ (tested on 7.4.21)
- Composer

## Setup

    cd <project folder>
    composer install

If you don't have a `composer.json` yet, create one:

```json
{
    "require": { "nesbot/carbon": "^2.72" },
    "autoload": { "psr-4": { "App\\": "src/" } }
}
```

Then run:

    composer dump-autoload

## Run

    php -S localhost:8000

Open http://localhost:8000/index.html in the browser, pick a CSV,
and submit. Stop the server with Ctrl+C.

## Files

    index.html              upload form (posts to index.php)
    index.php               entry point: wires the classes together
    style.css               styling
    src/
        DateParser.php      normalizes any date string to Y-m-d
        CsvReader.php       reads the CSV into an array of rows
        IntervalMerger.php  merges overlapping periods per (emp, project)
        PairAnalyzer.php    finds overlapping pairs per project
        Report.php          picks the winner and prints the HTML

## CSV format

    EmpID,ProjectID,DateFrom,DateTo

- Header row is skipped automatically (non-numeric first two columns).
- EmpID and ProjectID must be integers. Anything else is ignored.
- `DateTo` accepts several formats. `NULL` (case-insensitive) means today.
- An **empty** `DateTo` is treated as invalid and the row is skipped.
- `DateFrom` and `DateTo` accept: Y-m-d, d/m/Y, d.m.Y, Ymd,
  Y-m-d H:i:s, "M j Y", "F j, Y", Unix timestamp, etc.
- Overlapping periods for the same (employee, project) are merged
  before counting.

Sample input: `employeess.csv`.

## How it works

1. `index.php` validates the upload and instantiates the classes.
2. `DateParser` normalizes every date string to `Y-m-d`.
3. `CsvReader` reads the file, skips invalid rows, and returns
   an array of clean rows: `['emp','project','from','to','start','end']`.
4. `IntervalMerger` sorts and merges overlapping periods for the
   same (employee, project), so they aren't double-counted.
5. `PairAnalyzer` groups rows by project, finds all pairs whose
   periods overlap, and accumulates days per (pair, project).
6. `Report` sums days per pair, finds the maximum, and prints
   the winners plus a table of their common projects.

## Output

Shows the pair(s) with the highest total days across all their
common projects, plus a table with each project and its contribution.

## Notes and gotchas

- **Days are counted inclusively.** A one-day period (from = to)
  counts as 1 day.
- **DST-safe day counting.** `PairAnalyzer::overlapDays()` uses
  calendar arithmetic, not `(end - start) / 86400`, because dividing
  by 86400 loses an hour across a DST transition and can round down
  by one day. Do not "simplify" it back.
- **String vs int IDs.** EmpID/ProjectID come from CSV as strings.
  They are cast to `(int)` before building the pair key so that
  `'7'` and `'15'` sort numerically.
- **Pair key is `minID-maxID`.** A pair is always stored with the
  smaller ID first, so the same two people are never counted twice.
- **Same pair on the same project twice.** Two separate non-overlapping
  periods for the same (employee, project) survive the merge and their
  days are added together in `PairAnalyzer`.
- **`Report::MODE`** is a constant: `'total'` (sum across all projects)
  or `'single'` (longest single project). Change it in `Report.php`.
- **`display_errors` is off** in `index.php`. If you see a blank page,
  flip it to `'1'` temporarily, or check the PHP error log.
- **No MIME/size validation** on the upload — it's an internal tool.
  Add `mime_content_type()` and a size check if you expose it.

## Design notes

Why these six classes:

- `DateParser` — knows nothing about CSVs, only about date strings.
- `CsvReader` — knows nothing about projects or pairs, only how to
  turn a file into clean rows.
- `IntervalMerger` — pure function on an array of rows.
- `PairAnalyzer` — pure function on an array of rows.
- `Report` — knows nothing about parsing or math, only HTML.
- `index.php` — the only place that knows the order.

Each class can be unit-tested in isolation. There are no interfaces,
no DI container, no repositories — none of them would earn their
keep for a script this size.

## Common issues

- `Class "App\..." not found` — run `composer dump-autoload`.
- `vendor/autoload.php not found` — run `composer install`.
- Blank page after upload — set `display_errors` to `'1'` in `index.php`.
- Port 8000 busy — use `php -S localhost:8080`.
- Result off by one day — check `PairAnalyzer::overlapDays()`. It must
  use calendar math, not seconds / 86400.
