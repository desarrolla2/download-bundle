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

use Desarrolla2\DownloadBundle\Model\Database;

class DatabaseHandler extends AbstractHandler
{
    /** @var Database */
    private $remote;

    /** @var Database */
    private $local;

    /** @var string */
    private $directory;

    /**
     * DatabaseHandler constructor.
     * @param Database $remote
     * @param Database $local
     * @param string   $directory
     */
    public function __construct(Database $remote, Database $local, string $directory)
    {
        $this->remote = $remote;
        $this->local = $local;
        $this->directory = $directory;
    }

    public function download()
    {
        $databaseFile = $this->getFileName();
        $databaseFileWithTime = $this->getFileNameWithDateTime();

        $this->cmd(
            sprintf(
                'mysqldump -h %s -u %s -p\'%s\' --port %s --opt --databases %s > %s',
                $this->remote->getHost(),
                $this->remote->getUser(),
                $this->remote->getPassword(),
                $this->remote->getPort(),
                $this->remote->getName(),
                $databaseFileWithTime
            )
        );
        $this->cmd(
            sprintf(
                'cp %s %s',
                $databaseFileWithTime,
                $databaseFile
            )
        );

        if ($this->remote->getName() == $this->local->getName()) {
            return;
        }
        $this->cmd(sprintf('sed \'s/%s/%s/g\' %s', $this->remote->getName(), $this->local->getName(), $databaseFile));
    }

    /**
     * @return string
     */
    public function getDateTimeFormat(): string
    {
        return 'Ymd_His';
    }

    /**
     * @return string
     */
    public function getDirectory()
    {
        if (!is_dir($this->directory)) {
            $this->cmd(sprintf('mkdir -p %s', $this->directory));
        }

        return $this->directory;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        $directory = $this->getDirectory();

        return sprintf('%s/current.sql', $directory);
    }

    public function load()
    {
        $this->cmd(
            sprintf(
                'mysql -h %s -u %s -p\'%s\' --port %s -e \'DROP DATABASE IF EXISTS %s;\'',
                $this->local->getHost(),
                $this->local->getUser(),
                $this->local->getPassword(),
                $this->local->getPort(),
                $this->local->getName()
            )
        );
        $this->cmd(
            sprintf(
                'mysql -h %s -u %s -p\'%s\' --port %s < %s',
                $this->local->getHost(),
                $this->local->getUser(),
                $this->local->getPassword(),
                $this->local->getPort(),
                $this->getFileName()
            )
        );
    }

    /**
     * @return string
     */
    private function getFileNameWithDateTime(): string
    {
        $directory = $this->getDirectory();

        return sprintf('%s/%s.sql', $directory, (new \DateTime())->format($this->getDateTimeFormat()));
    }
}
