# DownloadBundle

Download database and directories from production to local through ssh connection

## Installation

Download the Bundle.

```bash 
composer require --dev "desarrolla2/download-bundle"
```

Enable the Bundle

```php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        // enable it only for dev environment
        if (in_array($this->getEnvironment(), ['dev'], true)) {
            $bundles[] = new Desarrolla2\DownloadBundle\DownloadBundle();
        }

        // ...
    }

    // ...
}
```

## Usage

You need put something like this in your config_dev.yml

```yml
download:
    databases:
        # local directory to save databases
        directory: '%kernel.root_dir%/../var/data/databases'

        remote:
            host: '127.0.0.1'
            name: 'production_database_name'
            user: 'production_database_user'
            password: 'production_database_password'
            port: 3308

        local:
            host: '%database_host%'
            name: '%database_name%'
            user: '%database_user%'
            password: '%database_password%'

    # some directories that you want download.
    directories:
        web_uploads:
            remote: 'root@air.interlang.es:/var/www/air.interlang.es/shared/web/uploads'
            local: '%kernel.root_dir%/../web'

        var_data:
            remote: 'root@air.interlang.es:/var/www/air.interlang.es/shared/var/data'
            local: '%kernel.root_dir%/../var'
            
```

## Download

First you need to generate a ssh tunnel to download remote database. You can create one as follow.

```bash
ssh -N -L 3308:127.0.0.1:3306 root@remote_host_ip_here &
```

Now you can download your database and directories,

```bash
php bin/console downloader:download
```

## Load

Maybe you want to load a previously downloaded database.

```bash
php bin/console downloader:load
```

## Contact

You can contact with me on [@desarrolla2](https://twitter.com/desarrolla2).
