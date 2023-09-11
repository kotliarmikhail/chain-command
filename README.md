# chain-command-project

## Usage

```python
# setup containers
docker-compose up -d --build
# composer install
docker-compose exec php composer install
# run tests
docker-compose exec php bin/phpunit
```
##Configuration
src/ChainCommandBundle/Resource/config/services.yaml
```
parameters:
  chain_command.commands:
    foo:hello: //parent
      - bar:hi //array of children
```