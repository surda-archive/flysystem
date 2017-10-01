# Nette Flysystem

[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/surda/flysystem/blob/master/licence.md)

Integration of [Flysystem](https://flysystem.thephpleague.com) into Nette Framework including support Flysystem Plugins.

## Installation

Via [Composer](http://getcomposer.org/)

``` bash
$ composer require surda/flysystem
```
and add to `config.neon`

```yml
extensions:
    flysystem:  Surda\Flysystem\Bridges\NetteDI\FlysystemExtension
```

## Minimal configuration for using Filesystem 

Example with Local Adapter.

```yml
flysystem:
    filesystem:
        adapter: League\Flysystem\Adapter\Local('/path/to/folder')
```

### Usage

```php
use League\Flysystem\Filesystem;

class Foo
{
    /** @var Filesystem */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function save()
    {
        $this->filesystem->write('path/to/file.txt', 'contents');
    }
}
```

## Minimal configuration for using MountManager

Example with Local Adapters.

```yml
flysystem:
    mountmanager:
        filesystems:
            local:
                adapter: League\Flysystem\Adapter\Local('/path/to/folder')
            backup:
                adapter: League\Flysystem\Adapter\Local('/path/to/backup/folder')
            ...
```

### Usage

```php
use League\Flysystem\MountManager;

class Bar
{
    /** @var MountManager */
    private $manager;

    public function __construct(MountManager $manager)
    {
        $this->manager = $manager;
    }

    public function backup()
    {
        $this->manager->copy('local://path/to/file.txt', 'backup://storage/file.txt');
    }
}
```

## Adapter arguments

Configuration if the adapter constructor accepts a string.

```yml
flysystem:
    filesystem:
        adapter: League\Flysystem\Adapter\Local('/path/to/folder')

        # or
        adapter: League\Flysystem\Adapter\Local
        arguments: '/path/to/folder'
```

Configuration if the adapter constructor accepts a array.

```yml
flysystem:
    filesystem:
        adapter: League\Flysystem\Adapter\Ftp( { host: 'ftp.example.com', username: 'username', password: 'password' } )

        # or
        adapter: League\Flysystem\Adapter\Ftp
        arguments:
            host: 'ftp.example.com'
            username: 'username'
            password: 'password'
```

Configuration if the adapter constructor accepts a client object.

```yml
flysystem:
    filesystem:
        adapter: Srmklive\Dropbox\Adapter\DropboxAdapter( Srmklive\Dropbox\Client\DropboxClient('access_token'), '/' )
```

Adapter in Mountmanager.

```yml
flysystem:
    mountmanager:
        filesystems:
            local:
                adapter: League\Flysystem\Adapter\Local('/path/to/folder')

                # or
                adapter: League\Flysystem\Adapter\Local
                arguments: '/path/to/folder'
            backup:
                adapter: League\Flysystem\Adapter\Local('/path/to/backup/folder')

                # or
                adapter: League\Flysystem\Adapter\Local
                arguments: '/path/to/backup/folder'
```
 
## Plugins

### Global plugins

Adding plugins to all Filesystems and Mountmanager.

```yml
flysystem:
    plugins:
        - League\Flysystem\Plugin\ListFiles()
        - League\Flysystem\Plugin\ListPaths()
```

### Plugins for Filesystems

Adding plugins only to Filesystem.

```yml
flysystem:
    filesystem:
        adapter: League\Flysystem\Adapter\Local('/path/to/folder')
        plugins:
            - Namespace\MyPlugin1()
            - Namespace\MyPlugin2()
            - ...
```

Adding plugins only to specific Filesystem in Mountmanager.

```yml
flysystem:
    mountmanager:
        filesystems:
            local:
                adapter: League\Flysystem\Adapter\Local('/path/to/folder')
                plugins:
                    - Namespace\MyPlugin1()
                    - Namespace\MyPlugin2()
            backup:
                adapter: League\Flysystem\Adapter\Local('/path/to/backup/folder')
                plugins:
                    - Namespace\MyPlugin3()
                    - Namespace\MyPlugin4()
```

### Plugins for Mountmanager

Adding plugins only to Mountmanager.

```yml
flysystem:
    mountmanager:
        plugins:
            - Namespace\MyPlugin5()
            - Namespace\MyPlugin6()
```

## Config argument in Filesystem

```yml
flysystem:
    filesystem:
        adapter: League\Flysystem\Adapter\Local('/path/to/folder', { key: value, foo: bar } )

        # or
        adapter: League\Flysystem\Adapter\Local('/path/to/folder')
        config: { key: value, foo: bar }

        # or
        adapter: League\Flysystem\Adapter\Local('/path/to/folder')
        config: 
            key: value
            foo: bar
```

```php
// Generated services

/**
 * @return League\Flysystem\Filesystem
 */
public function createServiceFlysystem__filesystem()
{
    $service = new League\Flysystem\Filesystem($this->getService('flysystem.filesystem.adapter'),
	['key' => 'value', 'foo' => 'bar']);
    return $service;
}

/**
 * @return League\Flysystem\Adapter\Local
 */
public function createServiceFlysystem__filesystem__adapter()
{
    $service = new League\Flysystem\Adapter\Local('/var/www/nette/sandbox/www/files');
    return $service;
}
```

Config argument in Mountmanager.

```yml
flysystem:
    mountmanager:
        filesystems:
            local:
                adapter: League\Flysystem\Adapter\Local('/path/to/folder', { key: value, foo: bar } )

                # or
                adapter: League\Flysystem\Adapter\Local('/path/to/folder')
                config: { key: value, foo: bar }

                # or
                adapter: League\Flysystem\Adapter\Local('/path/to/folder')
                config: 
                    key: value
                    foo: bar
            ...
```

