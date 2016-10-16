# Doctrine CLI
The Doctrine CLI has been pre-configured for you and works as is without any special configuration
required for MongoODM ODM and ORM. It will use your application's configuration for
entities or documents.

Access the Doctrine command line through

```sh
./vendor/bin/doctrine-module
```

Each command provides a description of itself if called with a `--help` argument.

## Adding commands to the CLI

You may add your own CLI commands by just creating new
[Symfony commands](http://symfony.com/doc/current/cookbook/console/console_command.html)
and attaching them to the provided CLI application as following:


```php
namespace My;

use Zend\EventManager\EventInterface;
use Zend\ModuleManager\ModuleManagerInterface;

class Module
{
    public function init(ModuleManagerInterface $manager)
    {
        $events = $manager->getEventManager()->getSharedManager();

        // Attach to helper set event and load the entity manager helper.
        $events->attach('doctrine', 'loadCli.post', function (EventInterface $e) {
            /* @var $cli \Symfony\Component\Console\Application */
            $cli = $e->getTarget();

            $cli->add(new \My\Own\Cli\Command());
        });
    }
}
```

This is not the suggested way of attaching new commands to the CLI, since ZF2 comes
already with console support.
