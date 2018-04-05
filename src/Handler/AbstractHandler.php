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

namespace Desarrolla2\DownloadBundle\Handler;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

abstract class AbstractHandler
{
    /** @var string */
    protected $user;

    /** @var string */
    protected $host;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param string $cmd
     * @param int    $timeout
     * @return bool|string
     */
    public function local(string $cmd, int $timeout = 300)
    {
        $this->log($cmd);

        $process = new Process($cmd);
        $process->setTimeout($timeout);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $string
     * @param string $level
     */
    protected function log(string $string, $level = LogLevel::INFO)
    {
        if (!$this->logger) {
            return;
        }
        $this->logger->log($level, $string);
    }

    /**
     * @param string $cmd
     * @param int    $timeout
     */
    protected function remote(string $cmd, int $timeout = 300)
    {
        $cmd = sprintf('ssh %s@%s "%s"', $this->user, $this->host, $cmd);
        $this->local($cmd, $timeout);
    }
}
