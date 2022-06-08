<?php

/*
 * This file is part of the desarrolla2 download bundle package
 *
 * Copyright (c) 2017-2018 Daniel González
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Daniel González <daniel@desarrolla2.com>
 */

namespace Desarrolla2\DownloadBundle\Command;

use Desarrolla2\DownloadBundle\Handler\DatabaseHandler;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class CleanCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('downloader:clean');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $handler = $this->container->get('desarrolla2_download.handler.database_handler');
        $handler->setLogger(new ConsoleLogger($output));
        /** @var DatabaseHandler $handler */
        $handler = $this->container->get('desarrolla2_download.handler.database_handler');
        $files = [];
        $directory = $handler->getDirectory();

        $finder = new Finder();
        $finder->files()->in($directory)->name('*.sql');
        $finder->sort(
            function (\SplFileInfo $a, \SplFileInfo $b) {
                return strcmp($a->getRealPath(), $b->getRealPath());
            }
        );
        foreach ($finder as $file) {
            $name = $file->getFilename();
            $date = \DateTime::createFromFormat($handler->getDateTimeFormat(), str_replace('.sql', '', $name));
            if (!$date) {
                continue;
            }

            $formatted = $date->format('d F \a\t h:iA, l');
            $files[] = [
                'file' => $name,
                'date' => $date,
                'formatted' => $formatted,
                'path' => $file->getRealPath(),
            ];
        }
        $filesToKeep = $this->container->getParameter('desarrolla2_download.database.max_local_db');

        $numberOfFiles = count($files);
        $output->writeln(sprintf(' - found %d databases', $numberOfFiles));
        if ($numberOfFiles <= $filesToKeep) {
            return;
        }
        $output->writeln(sprintf(' - removing %d databases', $numberOfFiles - $filesToKeep));
        $filesToDelete = array_slice($files, 0, $numberOfFiles - $filesToKeep);
        foreach ($filesToDelete as $file) {
            $output->writeln(sprintf(' - removing "%s"', $file['file']));
            $handler->local(sprintf('rm -rf %s', $file['path']));
        }
    }
}
