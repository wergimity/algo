<?php
namespace App\Commands;

use App\FileCrawler;
use App\FileStorage;
use App\Structures\ChainedHashTable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeChainedHashTable extends Command
{
    protected function configure()
    {
        $this->addOption('size', 's', InputOption::VALUE_OPTIONAL, '', 100);
        $this->addOption('length', 'l', InputOption::VALUE_OPTIONAL, '', 10);
        $this->addOption('path', 'd', InputOption::VALUE_OPTIONAL, '', '.');
        $this->addOption('recursive', 'r', InputOption::VALUE_NONE, '');
        $this->addOption('storage', 'o', InputOption::VALUE_OPTIONAL, '', 'results/.chained_hash');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = realpath($input->getOption('path'));

        $recursive = $input->getOption('recursive');

        $outputFile = $input->getOption('storage');

        $size = (int) $input->getOption('size');

        $length = (int) $input->getOption('length');

        $crawler = new FileCrawler($path, $recursive);

        $storage = new FileStorage($outputFile);

        $structure = new ChainedHashTable($storage, $size, $length);

        $crawler->walk([$structure, 'insert']);
    }

}