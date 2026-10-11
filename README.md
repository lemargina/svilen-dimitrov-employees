# svilen-dimitrov-employees
The  task for Sirma for how long two coworkers works on the common projects for longest period

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

 Open http://localhost:8000/index.html in the browser, pick a CSV, and submit. Stop the server with Ctrl+C.

## Files

    index.html              upload form (posts to index.php)
    index.php               entry point: wires the classes together
    style.css               styling
    
        src/DateParser.php      normalizes any date string to Y-m-d
        src/CsvFileReader.php       reads the CSV into an array of rows
        src/TimeCombiner.php  merges overlapping periods per (emp, project)
        src/PairFinder.php    finds overlapping pairs per project
       src/Dashboard.php          picks the winner and prints the HTML

## CSV format

EmpID,ProjectID,DateFrom,DateTo

- Header row is skipped automatically (non-numeric first two columns).
- EmpID and ProjectID must be integers. Anything else is ignored.
- `DateTo` accepts several formats. `NULL` (case-insensitive) means today.

Sample input: `employeess.csv`, sample.csv

## How it works

1. `index.php` validates the upload and instantiates the classes.
2. `DateParser` normalizes every date string to `Y-m-d`.
3. `CsvFileReader` reads the file, skips invalid rows, and returns
   an array of clean rows: `['emp','project','from','to','start','end']`.
4. `TimeCombiner` sorts and merges overlapping periods for the
   same (employee, project), so they aren't double-counted.
5. `PairFinder` groups rows by project, finds all pairs whose
   periods overlap, and accumulates days per (pair, project).
6. `Dashboard` sums days per pair, finds the maximum, and prints
   the winners plus a table of their common projects.

## Output

 Shows the pair(s) with the highest total days across all their
   common projects, plus a table with each project and time


 Design notes:

  Six classes:

- `DateParser` — knows nothing about CSVs, only about date strings.
- `CsvFileReader` — knows nothing about projects or pairs, only how to
  turn a file into clean rows.
 - `TimeCombiner` — class calculating on an array of rows.
 - `PairFinder` — same function as previous, calculate arrays.
 - `Dashboard` — knows nothing about parsing or math, only HTML.
- `index.php` — the only place that knows the order.entry point

At the very end of repo,  there is a single file called zadacha_withoutFRONTEND.php, which is CLI procedural version of task.
You can start it by going to it by cmd, and write: php -q  zadacha_withoutFRONTEND.php
The first line in the file is called $filename where your put your sample.csv like this:  $filename="sample.csv"
It was must starting point before creating the OOP version, so I can understand the algorithm first.

 Some problems you could face, try starting the project: 

- `Class "App\..." not found` — run `composer dump-autoload`.
- `vendor/autoload.php not found` — run `composer install`.
- Port 8000 busy — use `php -S localhost:8080`.


  
