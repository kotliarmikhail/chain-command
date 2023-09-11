# chain-command

## Set up and run tests

```python
# setup containers
docker-compose up -d --build
# composer install
docker-compose exec php composer install
# run tests
docker-compose exec php bin/phpunit
```
##Configuration and Usage
1. Create configuration section named "chain_command" and fill in it as in example:
#####{project}/app/config/packages/chain_command.yaml
```
chain_command:
  chain_commands:
    -
      root_command: 'foo:hello'
      member_commands: ['bar:hi']
```
2. Create new chains and add new members to chains at runtime using service container

3. Don't forget to add new bundles to "./config/bundles.php"
```
<?php

return [
       ...
       App\ChainCommandBundle\ChainCommandBundle::class => ['all' => true],
       App\FooBundle\FooBundle::class => ['all' => true],
       App\BarBundle\BarBundle::class => ['all' => true],
       ...
];
```