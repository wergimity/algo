<?php
namespace App\Commands;

use App\FileCrawler;
use App\FileStorage;
use App\Structures\ChainedHashTable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SearchChainedHashTable extends Command
{
    protected function configure()
    {
        $this->addArgument('storage', InputArgument::REQUIRED);
        $this->addOption('size', 's', InputOption::VALUE_OPTIONAL, '', 100);
        $this->addOption('length', 'l', InputOption::VALUE_OPTIONAL, '', 10);
        $this->addOption('path', 'd', InputOption::VALUE_OPTIONAL, '', '.');
        $this->addOption('recursive', 'r', InputOption::VALUE_NONE, '');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $outputFile = realpath($input->getArgument('storage'));

        $size = (int) $input->getOption('size');

        $length = (int) $input->getOption('length');

        $path = $input->getOption('path');

        $recursive = $input->getOption('recursive');

        $storage = new FileStorage($outputFile, 'r');

        $crawler = new FileCrawler($path, $recursive);

        $structure = new ChainedHashTable($storage, $size, $length);

        $start = microtime(true);

        $n = 0;

        $crawler->walk(function($file) use ($structure, $output, &$n) {

            $n++;

            step(); $node = $structure->search($file);

            step(); if ($node === null) {

                step(); return $output->writeln("<error>$file was not found!</error>");

            }

            step(); $count = $structure->value($node);

            step(); if ($count > 1) {

                step(); return $output->writeln("<info>$file occurs $count times</info>");

            }

            step(); return $output->writeln("$file occurs only once");

        });

        $time = microtime(true) - $start;

        $steps = step();

        $output->writeln(str_repeat('-', 15));
        $output->writeln(sprintf('Executed in %.3f µs', $time));
        $output->writeln("Steps executed $steps");
        $output->writeln("For $n files");
    }
}