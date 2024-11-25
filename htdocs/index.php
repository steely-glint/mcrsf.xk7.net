<?php

declare(strict_types=1);

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../vendor/autoload.php';

$dsn = 'sqlite:' . __DIR__ . '/books.db';
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
$db = new PDO($dsn, null, null, $options);

$availableSections = [
    'upcoming',
    'read',
    'suggestions',
    'rejected',
];

$section = $_GET['section'] ?? 'upcoming';

if (!in_array($section, $availableSections, true)) {
    $section = 'upcoming';
}

$sql = 'SELECT * FROM books WHERE section = :section';
$sth = $db->prepare($sql);
$sth->bindValue('section', $section, PDO::PARAM_STR);
$sth->execute();
$books = $sth->fetchAll();

$loader = new FilesystemLoader(__DIR__ . '/../templates/');
$twig = new Environment(
    $loader,
    [
        'cache' => false,
        'strict_variables' => true,
    ]
);
$twig->display(
    'index.twig.html',
    [
        'books' => $books,
        'availableSections' => $availableSections,
        'section' => $section,
    ]
);
