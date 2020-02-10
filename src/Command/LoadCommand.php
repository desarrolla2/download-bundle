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
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Finder\Finder;

class LoadCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('downloader:load')
            ->addOption('current');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('current')) {
            $this->selectDataBase($input, $output);
        }

        /** @var DatabaseHandler $handler */
        $handler = $this->container->get('desarrolla2_download.handler.database_handler');
        $handler->setLogger(new ConsoleLogger($output));
        $output->writeln(' - loading database');
        $handler->load();
        $output->writeln(' - done');

        $output->writeln(' - deleting old databases');
        $handler->delete();
        $output->writeln(' - done');

        $this->finalize($output);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function selectDataBase(InputInterface $input, OutputInterface $output)
    {
        /** @var DatabaseHandler $handler */
        $handler = $this->container->get('desarrolla2_download.handler.database_handler');
        $files = $options = [];
        $directory = $handler->getDirectory();

        $finder = new Finder();
        $finder->files()->in($directory)->name('*.sql');
        $finder->sort(
            function (\SplFileInfo $a, \SplFileInfo $b) {
                return strcmp($a->getRealPath(), $b->getRealPath());
            }
        );
        foreach ($finder as $file) {
            if (!$file->getSize()) {
                continue;
            }
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
            $options[] = $formatted;
        }

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Please select database to load (defaults to current)',
            $options,
            count($options) - 1
        );
        $question->setErrorMessage('Date %s is invalid.');

        $selected = $helper->ask($input, $output, $question);
        foreach ($files as $file) {
            if ($file['formatted'] == $selected) {
                $handler->local(sprintf('cp %s %s', $file['path'], $handler->getFileName()));
                $output->writeln(sprintf(' - selected: %s', $selected));

                return;
            }
        }
    }
}
