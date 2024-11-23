<?php

declare(strict_types=1);

use PhpOffice\PhpSpreadsheet\IOFactory;

error_reporting(E_ALL);

set_error_handler(
    function ($severity, $message, $file, $line) {
        throw new \ErrorException($message, $severity, $severity, $file, $line);
    }
);

require_once __DIR__ . '/vendor/autoload.php';

$dsn = 'sqlite:' . __DIR__ . '/htdocs/books.db';
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
];
$db = new PDO($dsn, null, null, $options);

$schema = file_get_contents(__DIR__ . '/books.sql');
$db->exec($schema);

$books = [];

$file = $argv[1];

$reader = IOFactory::createReaderForFile($file);
$reader->setReadDataOnly(false); // Must be false for formatted data to be returned
$reader->setReadEmptyCells(true);
$spreadsheet = $reader->load($file);

// Upcoming
$worksheet = $spreadsheet->getSheetByNameOrThrow('Upcoming');
$highestRow = $worksheet->getHighestDataRow();

for ($row = 2; $row <= $highestRow; $row++) {
    $title = $worksheet->getCell("A{$row}")->getValue();

    if ($title) {
        $authors = $worksheet->getCell("B{$row}")->getValue();
        $pages = $worksheet->getCell("C{$row}")->getValue();
        
        // Date read needs to be converted
        $dateRead = (DateTimeImmutable::createFromFormat(
            'd/m/Y',
            $worksheet->getCell("E{$row}")->getFormattedValue()
        ))->format('Y-m-d');
    
        $section = 'upcoming';
    
        $books[] = [
            'title' => $title,
            'authors' => $authors,
            'pages' => $pages,
            'score' => null,
            'score_type' => null,
            'date_read' => $dateRead,
            'section' => $section,
        ];
    }
}

// Read
$worksheet = $spreadsheet->getSheetByNameOrThrow('Read');
$highestRow = $worksheet->getHighestDataRow();

for ($row = 2; $row <= $highestRow; $row++) {
    $title = $worksheet->getCell("A{$row}")->getValue();

    if ($title) {
        $authors = $worksheet->getCell("B{$row}")->getValue();
        $pages = $worksheet->getCell("C{$row}")->getValue();
        
        // Date read needs to be converted
        $dateRead = (DateTimeImmutable::createFromFormat(
            'd/m/Y',
            $worksheet->getCell("E{$row}")->getFormattedValue()
        ))->format('Y-m-d');

        $score = $worksheet->getCell("F{$row}")->getValue();

        if (!$score) {
            $score = null;
        }

        $scoreType = $worksheet->getCell("G{$row}")->getValue();
    
        $section = 'read';
    
        $books[] = [
            'title' => $title,
            'authors' => $authors,
            'pages' => $pages,
            'score' => $score,
            'score_type' => $scoreType,
            'date_read' => $dateRead,
            'section' => $section,
        ];
    }
}

// Suggestions
$worksheet = $spreadsheet->getSheetByNameOrThrow('Suggestions');
$highestRow = $worksheet->getHighestDataRow();

for ($row = 2; $row <= $highestRow; $row++) {
    $title = $worksheet->getCell("A{$row}")->getValue();

    if ($title) {
        $authors = $worksheet->getCell("B{$row}")->getValue();
        $pages = $worksheet->getCell("C{$row}")->getValue();
    
        $section = 'suggestions';
    
        $books[] = [
            'title' => $title,
            'authors' => $authors,
            'pages' => $pages,
            'score' => null,
            'score_type' => null,
            'date_read' => null,
            'section' => $section,
        ];
    }
}

// Rejected
$worksheet = $spreadsheet->getSheetByNameOrThrow('Rejected');
$highestRow = $worksheet->getHighestDataRow();

for ($row = 2; $row <= $highestRow; $row++) {
    $title = $worksheet->getCell("A{$row}")->getValue();

    if ($title) {
        $authors = $worksheet->getCell("B{$row}")->getValue();
        $pages = $worksheet->getCell("C{$row}")->getValue();
    
        $section = 'rejected';
    
        $books[] = [
            'title' => $title,
            'authors' => $authors,
            'pages' => $pages,
            'score' => null,
            'score_type' => null,
            'date_read' => null,
            'section' => $section,
        ];
    }
}

// All sheets read, now insert books into database
$sql = <<<SQL
    INSERT INTO books (
        title,
        authors,
        pages,
        score,
        score_type,
        date_read,
        section
    ) VALUES (
        :title,
        :authors,
        :pages,
        :score,
        :score_type,
        :date_read,
        :section
    )
SQL;
$sth = $db->prepare($sql);

foreach ($books as $book) {
    $sth->execute($book);
}
