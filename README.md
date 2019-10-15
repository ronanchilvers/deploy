# deploy

A tool for simple deployments to a single server (for now) from common source control providers.

* Github and Gitlab support (Bitbucket planned)
* Zero downtime deployments with rollbacks
* Fine grained control of deployments using a repository based configuration file
* Responsive UI - fully usable on any device
* Shared and writable path support
* Arbitrary hook support
* Slack notifications
* Simple user account management

## Installation

`deploy` has a couple of requirements to run.

* PHP 7.1.8+
* Beanstalkd work queue (available as standard in most linux distributions)
* A backend database supported by PDO and (Phinx)[https://github.com/cakephp/phinx]
* (Composer)[https://getcomposer.org/] for `deploy` dependency installation

In addition it is *strongly* recommended that you use a proper RDBMS like MySQL, MariaDB or PostgreSQL to host the database. The default SQLite database is suitable for development but you will almost certainly run into database contention locks if you use it in production.

`deploy` includes a queue runner that does the heavy lifting. You can run this via cron if you want to but I recommend using supervisord (again available in most linux distributions in the standard package catalogue).

Once you have the required software installed on the host you can then get on with the installation.

### Codebase setup

* Create a database and database user in your chosen DBMS. `deploy` needs CREATE, DROP, ALTER, SELECT, INSERT, UPDATE, DELETE, INDEX permissions. We will leave this step to you as it's implementation depends on your chosen RDBMS backend.

* Clone this repository into an appropriate place on your server
```bash
git clone https://github.com/ronanchilvers/deploy.git deploy
cd deploy
```

* Install dependencies
```bash
composer install
```

* Create the local configuration. Instructions are provided within the file.
```bash
cp local.yaml.dist local.yaml
```

* Run phinx database migrations
```bash
php vendor/bin/phinx migrate
```

### Queue worker setup

We assume here that you're using supervisord to run the queue worker. You'll find a sample supervisord program configuration file in the `docs/` subdirectory. One point to note - in order to run correctly composer requires that either the `HOME` or `COMPOSER_HOME` environment variables are set. You can (read more about it here)[https://getcomposer.org/doc/03-cli.md#composer-home].

* Copy the sample config file into supervisor's program directory (usually something like `/etc/supervisor/conf.d`) or include the contents in supervisor's main configuration file.
* Ask supervisor to update it's configuration
```bash
sudo supervisorctl update
```
* You should now see the queue worker running under supervisor control
```bash
sudo supervisorctl status
```

## Things to do

* [ ] Unit tests!
* [ ] Ability to trigger a deployment using a webhook
* [ ] Bitbucket support
* [ ] Implement re-activation rather than deployment for old releases (change of symlink)

## Things that are done

* [x] Block deployments for a project when one is queued or in progress
* [x] Better user account support
* [x] User accounts
* [x] Hooks
* [x] Notifications
* [x] Ability to deploy a specific branch
* [x] Associate deployments with users
* [x] Make sure project keys are unique

## Things to think about

* [ ] Environment variable support
* [ ] Ability to keep specific releases
* [ ] Multi-server support

## Example deploy.yaml

```yaml
---
notify:
  slack:
    webhook: https://hooks.slack.com/services/12345679/AKSJDHFGASJDHFG/ADLJFBWIAEJFBWIDJCDC
composer:
  install: install --no-dev
  after:
    - {php} scripts/myscript.php
shared:
  files:
    - ".env.config.ini"
  folders:
    - var/log
    - var/cache
    - var/db
writables:
  paths:
    - var/log
    - var/cache
clear_paths:
  paths:
    - README.md
    - package.json
    - deploy.yaml
```

## Useful things (for development)

* https://developer.github.com/v3/repos/contents/#get-contents
* https://mattstauffer.com/blog/introducing-envoyer.io/
* https://docs.gitlab.com/ee/api/repositories.html#get-file-archive
