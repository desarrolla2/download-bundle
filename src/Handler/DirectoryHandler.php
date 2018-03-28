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

use Desarrolla2\DownloadBundle\Model\Directory;

class DirectoryHandler extends AbstractHandler
{
    /** @var Directory[] */
    private $directories;

    /**
     * @param array $directories
     */
    public function __construct(array $directories)
    {
        $this->directories = $directories;
    }

    public function download()
    {
        foreach ($this->directories as $directory) {
            $this->cmd(
                sprintf(
                    'rsync -rzd --exclude="*/cache/*" --exclude="*/spool/*" %s %s',
                    $directory->getRemote(),
                    $directory->getLocal()
                )
            );
        }
    }
}
