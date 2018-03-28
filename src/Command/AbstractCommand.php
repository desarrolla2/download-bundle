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

use Desarrolla2\Timer\Formatter\Human;
use Desarrolla2\Timer\Timer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var Timer */
    protected $timer;

    /** @var OutputInterface */
    protected $output;

    /**
     * @param string $cmd
     * @return string
     */
    protected function cmd(string $cmd)
    {
        $this->timer->mark();
        $this->log(sprintf('<info>cmd:</info> %s', $cmd));
        $process = new Process($cmd);
        $process->setTimeout(60 * 5);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $mark = $this->timer->mark();
        $this->logLn(sprintf(' <info>OK</info> %s', $mark['time']['from_previous']));

        return $process->getOutput();
    }


    /**
     * @param array $configuration
     */
    protected function downloadDirectories(array $configuration)
    {
        if (array_key_exists('directories', $configuration)) {
            foreach ($configuration['directories'] as $directory) {
                $this->cmd(
                    sprintf(
                        'rsync -rzd --exclude="*/cache/*" --exclude="*/spool/*" %s %s',
                        $directory['remote'],
                        $directory['local']
                    )
                );
            }
        }
    }

    /**
     * @param array $configuration
     */
    protected function finalize(string $site, array $configuration)
    {
        $mark = $this->timer->mark();
        $databaseFile = $this->getDataBaseFileName($site);
        $directories = [];
        if (array_key_exists('directories', $configuration)) {
            foreach ($configuration['directories'] as $directory) {
                $directoryName = sprintf('%s/%s', $directory['local'], basename($directory['remote']));
                $size = $this->getDirectorySize($directoryName);
                $directories[] = ['name' => $directoryName, 'size' => $this->formatSize($size)];
            }
        }

        foreach ($directories as $directory) {
            $this->logLn(sprintf('<info>Directory size</info>: "%s" "%s"', $directory['size'], $directory['name']));
        }

        $this->logLn(sprintf('<info>Database size</info>: "%s"', $this->formatSize(filesize($databaseFile))));
        $this->logLn(sprintf('<info>Total time</info>: "%s"', $mark['time']['from_start']));
    }

    /**
     * @param int $size
     * @return string
     */
    protected function formatSize($size)
    {
        $size = (int)$size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;

        return number_format($size / pow(1024, $power), 2, '.', ',').' '.$units[$power];
    }

    /**
     * @param string $site
     * @return array
     */
    protected function getConfiguration(string $site): array
    {
        $configurationFile = $this->getConfigurationFile();
        if (!is_file($configurationFile)) {
            throw new \RuntimeException('configuration file not found');
        }
        $configuration = Yaml::parse(file_get_contents($configurationFile));

        if (!array_key_exists($site, $configuration['config'])) {
            throw new \RuntimeException(sprintf('site "%s" not exist in configuration', $site));
        }

        return $configuration['config'][$site];
    }

    /**
     * @return string
     */
    protected function getConfigurationFile(): string
    {
        $configurationFile = __DIR__.'/../../config.yml';

        return $configurationFile;
    }


    /**
     * @return string
     */
    protected function getDirectorySize(string $directory): string
    {
        return $this->cmd(sprintf('du -s -B1 %s | awk \'{print $1}\'', $directory));
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->timer = new Timer(new Human());
        $this->output = $output;
    }

    /**
     * @param array $configuration
     */
    protected function loadDatabase(string $site, array $configuration)
    {
        $databaseFile = $this->getDataBaseFileName($site);
        $this->cmd(
            sprintf(
                'mysql -h %s -u %s -p\'%s\' --port %s -e \'DROP DATABASE IF EXISTS %s;\'',
                $configuration['databases']['local']['host'],
                $configuration['databases']['local']['user'],
                $configuration['databases']['local']['password'],
                $configuration['databases']['local']['port'],
                $configuration['databases']['local']['name']
            )
        );
        $this->cmd(
            sprintf(
                'mysql -h %s -u %s -p\'%s\' --port %s < %s',
                $configuration['databases']['local']['host'],
                $configuration['databases']['local']['user'],
                $configuration['databases']['local']['password'],
                $configuration['databases']['local']['port'],
                $databaseFile
            )
        );
    }

    /**
     * @param $message
     */
    protected function log($message)
    {
        $this->output->write($message);
    }

    /**
     * @param $message
     */
    protected function logLn($message)
    {
        $this->output->writeln($message);
    }
}
