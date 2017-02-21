Symfony3 + Dotenv + Docker
==========================

#### Running application:

Copy distributive docker-composer.yml.dist to docker-composer.yml

```bash

  cp docs/docker-composer.yml docker-composer.yml


```

#### Run docker:

```bash

  docker-composer up -d

```

#### Access components command line:

```bash

  docker exec -it cli bash
  -> /var/www/html

 bin/console doctrine:database:create
 bin/console doctrine:schema:update --force 
 

```

#### Clear cache example:

```bash

  docker exec -it sf_cli bash
  bin/console cache:clear

  or

  docker exec -it sf_cli bin/console cache:clear
  docker exec -it sf_cli bin/console redis:flushall -n


```

#### Popule database schema:

```bash

  docker exec -it sf_cli bash
  bin/run.sh

  or

  docker exec -it sf_cli bin/run.sh

```

#### Load datafixtures

```bash

docker exec -it sf_cli bash
bin/console hautelook_alice:doctrine:fixtures:load -n

or

docker exec -it sf_cli bin/console hautelook_alice:doctrine:fixtures:load -n


```
 
### Generate JWT Token
 
```

mkdir -p var/jwt # For Symfony3+, no need of the -p option
openssl genrsa -out var/jwt/private.pem -aes256 4096
openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem
chmod 777 var/jwt

```

#### Services:

**PHP-FPM - Port 5000**

**PHP-XDEBUG - Port 9000**

**WebServer - Port 80/443**

**Mailler - Port 25**

**MariaDB - Port In 3306 - Out 3307**


### Components:
**Mail View - Port 1080**

**PHPMYADMIN - Admin mysql - 8080**

 
