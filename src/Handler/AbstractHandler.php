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

    /** @var int */
    private $defaultTimeout = 300;

    /**
     * @return int
     */
    public function getDefaultTimeout(): int
    {
        return $this->defaultTimeout;
    }

    /**
     * @param string $cmd
     * @param int    $timeout
     * @return bool|string
     */
    public function local(string $cmd, int $timeout = null)
    {
        $this->log($cmd);

        if (!$timeout) {
            $timeout = $this->getDefaultTimeout();
        }

        $process = Process::fromShellCommandline($cmd);
        $process->setTimeout($timeout);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    /**
     * @param int $defaultTimeout
     */
    public function setDefaultTimeout(int $defaultTimeout): void
    {
        $this->defaultTimeout = $defaultTimeout;
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
    protected function remote(string $cmd)
    {
        $cmd = sprintf('ssh %s@%s "%s"', $this->user, $this->host, $cmd);
        $this->local($cmd);
    }
}
