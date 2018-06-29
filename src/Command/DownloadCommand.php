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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('downloader:download')
            ->addOption('avoid-download-database')
            ->addOption('avoid-download-directories');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('avoid-download-database')) {
            $handler = $this->container->get('desarrolla2_download.handler.database_handler');
            $handler->setLogger(new ConsoleLogger($output));
            $output->writeln(' - downloading database');
            $handler->download();
            $output->writeln(' - loading database');
            $handler->load();
        }
        if (!$input->getOption('avoid-download-directories')) {
            $handler = $this->container->get('desarrolla2_download.handler.directory_handler');
            $handler->setLogger(new ConsoleLogger($output));
            $output->writeln(' - downloading directories');
            $handler->download();
            $output->writeln(' - done');
        }

        $this->finalize($output);
    }
}
