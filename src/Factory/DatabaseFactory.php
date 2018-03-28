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

namespace Desarrolla2\DownloadBundle\Factory;

use Desarrolla2\DownloadBundle\Model\Database;

class DatabaseFactory
{
    /** @var array */
    private $remote;

    /** @var array */
    private $local;

    /**
     * @param array $remote
     * @param array $local
     */
    public function __construct(array $remote, array $local)
    {
        $this->remote = $remote;
        $this->local = $local;
    }

    /**
     * @return Database
     */
    public function getLocal(): Database
    {
        return new Database(
            $this->local['host'],
            $this->local['name'],
            $this->local['user'],
            $this->local['password'],
            $this->local['port']
        );
    }

    /**
     * @return Database
     */
    public function getRemote(): Database
    {
        return new Database(
            $this->remote['host'],
            $this->remote['name'],
            $this->remote['user'],
            $this->remote['password'],
            $this->remote['port']
        );
    }
}
