#!/usr/bin/bash
<?php

use App\Commands\MakeChainedHashTable;
use App\Commands\MakeQuadraticHashTable;
use App\Commands\MakeRedBlackTree;
use App\Commands\SearchChainedHashTable;
use App\Commands\SearchQuadraticHash;
use App\Commands\SearchRedBlackTree;
use Symfony\Component\Console\Application;

require __DIR__ . '/vendor/autoload.php';

function local_file($file) {

    return __DIR__ . DIRECTORY_SEPARATOR . ltrim($file, DIRECTORY_SEPARATOR);

}

function step() {

    static $value = 0;

    return $value++;

}

ini_set('xdebug.max_nesting_level', 3000);

$app = new Application();

$app->add(new MakeRedBlackTree('make:red-black-tree'))->setDescription('Create red-black tree structure of files in given path');
$app->add(new MakeQuadraticHashTable('make:quadratic-hash'))->setDescription('Create quadratic hash table from files in given path');
$app->add(new MakeChainedHashTable('make:chained-hash'))->setDescription('Create chained hash table from files in given path');

$app->add(new SearchRedBlackTree('search:red-black-tree'))->setDescription('Search red-black tree for files in given path');
$app->add(new SearchQuadraticHash('search:quadratic-hash'))->setDescription('Search quadratic hash table for files in given path');
$app->add(new SearchChainedHashTable('search:chained-hash'))->setDescription('Search chained hash table for files in given path');

$app->run();