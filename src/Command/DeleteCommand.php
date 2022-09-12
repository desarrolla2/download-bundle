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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Finder\Finder;

class DeleteCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('downloader:delete:old');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var DatabaseHandler $handler */
        $handler = $this->container->get('desarrolla2_download.handler.database_handler');
        $handler->setLogger(new ConsoleLogger($output));

        $output->writeln(' - deleting old databases');
        $totalDeleted = $handler->delete();
        $output->writeln(sprintf(' - done. %s databases deleted', $totalDeleted));

        $this->finalize($output);
        return Command::SUCCESS;
    }
}
