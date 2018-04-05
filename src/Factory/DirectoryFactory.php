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

use Desarrolla2\DownloadBundle\Model\Directory;

class DirectoryFactory
{
    /** @var array */
    private $directories;

    /**
     * @param array $directories
     */
    public function __construct(array $directories)
    {
        $this->directories = $directories;
    }

    public function getDirectories()
    {
        $directories = [];
        foreach ($this->directories as $directory) {
            $directories[] = new Directory($directory['remote'], $directory['local'], $directory['exclude']);
        }

        return $directories;
    }
}
