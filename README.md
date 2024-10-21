# Ticket App Test

## Requirements:
- mysql:8.0.33
- php:8.2
- nginx:1.21

## Features
- Symfony App 7.1
- 3 related Entities : Ticket, Organiser, Event


## How to run the project

- Open git terminal
- `git https://github.com/cosminmanciu/tickets_app_symfony.git`
- `cd .docker`
- `docker-compose up -d`
- Connect to Docker PHP container
- `composer install`
- `php bin/console doctrine:schema:update --force`
- `php bin/console lexik:jwt:generate-keypair`

  ## How Test the API
  - You can use Swagger `http://localhost/doc`
