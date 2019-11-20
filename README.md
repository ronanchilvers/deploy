# deploy

[![Actions Status](https://github.com/ronanchilvers/deploy/workflows/Unit%20Tests/badge.svg)](https://github.com/ronanchilvers/deploy/actions)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ronanchilvers/deploy/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ronanchilvers/deploy/?branch=master)

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
* A backend database supported by [PDO](https://www.php.net/pdo) and [phinx](https://github.com/cakephp/phinx)
* [composer](https://getcomposer.org/) for `deploy` dependency installation

In addition it is *strongly* recommended that you use a proper RDBMS like MySQL, MariaDB or PostgreSQL to host the database. The default SQLite database is suitable for development but you will almost certainly run into database contention locks if you use it in production.

`deploy` includes a queue runner that does the heavy lifting. You can run this via cron if you want to but I recommend using supervisord (again available in most linux distributions in the standard package catalogue).

Once you have the required software installed on the host you can then get on with the installation.

### Codebase setup

* Create a database and database user in your chosen DBMS. `deploy` needs CREATE, DROP, ALTER, SELECT, INSERT, UPDATE, DELETE, INDEX permissions. For MariaDB / MySQL it's likely to be something like this:
```sql
CREATE DATABASE `deploy`;
CREATE USER `deploy`@`localhost` IDENTIFIED BY `verystrongpassword`;
GRANT CREATE, DROP, ALTER, SELECT, INSERT, UPDATE, DELETE, INDEX ON `deploy`.* TO `deploy`@`localhost`;
```

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

* Create a user.
```bash
php bin/console user:create "Fred Bloggs" fred@foobar.com
```

* Make sure the log and twig directories are writable by the web server. I'm assuming here that your web server runs as www-data group and that you've checked out the codebase with that group set.
```bash
chmod g+w var/log var/twig
```

* You should now be able to navigate to the URL you've installed `deploy` under and login.

### Queue worker setup

We assume here that you're using supervisord to run the queue worker. You'll find a sample supervisord program configuration file in the `docs/` subdirectory. One point to note - in order to run correctly composer requires that either the `HOME` or `COMPOSER_HOME` environment variables are set. You can [read more about it here](https://getcomposer.org/doc/03-cli.md#composer-home).

* Copy the sample config file into supervisor's program directory (usually something like `/etc/supervisor/conf.d`) or include the contents in supervisor's main configuration file.
* Update the supervisor configuration appropriately for your environment.
* Ask supervisor to update it's configuration
```bash
sudo supervisorctl update
```
* You should now see the queue worker running under supervisor control
```bash
sudo supervisorctl status
```

## Controlling deployments

`deploy` can be customised per project by using directives in a file named `deploy.yaml` placed in the root of the project working copy. Using this file you can assign paths that should be writable (folders only), define shared paths (files or folders), assign hooks to run before or after specific stages, define specific paths that should be removed when deploying (files or folders) and several other things.

### Directives

- `notify` - This directive controls notifications when deploying code. Currently only slack is implemented but support is planned for other services.
```yaml
notify:
  slack:
    webhook: https://hooks.slack.com/services/12345679/ABCDE/FGHIJK
```

- `composer` - This directive allows you to control the behaviour of the composer dependency manager, assuming that it is used in your project. If `deploy` doesn't find a `composer.json` file in the root of your working copy, composer support is disabled and this directive has no effect.
  - `install` - Define the command composer will install dependencies with. The default is `install --no-interaction --prefer-dist --no-dev --optimize-autoloader`
```yaml
composer:
  install: install --no-dev -o
```

- `shared` - Define shared folders or files. These are locations that persist between deployments, for example a cache directory or configuration file. The `files` and `folders` subkeys can be used to define a list or files or folders that should be shared. Paths are always relative to the root of the deployment working copy.
```yaml
shared:
  files:
    - config.php
    - .env
  folders:
    - var/cache
    - var/uploads
```

- `writables` - Define writable folders. These locations will be configured to be writable by the using a `chmod` command. The default mode for writable folders is '0770' (user and group readable / writable). Note it is possible for a folder to be both shared *and* writable.
```yaml
writables:
  paths:
    - var/cache
    - var/uploads
```
NB: Changing the writable mode used cannot be done via `deploy.yaml` but can be done in your local.yaml file with the following keys. Note that using '0777' is *never* recommended - if you need it, you should that that as a sign that your permission structure is wrong.
```yaml
build:
  chmod:
    writable_folder: '0777'
```

* `clear_paths` - Define a list of files or folders that should be removed on deployment. This action happens right before activation (switching the new deployment live) and therefore its safe to delete files like composer.json / composer.lock / package.json, etc (unless you have a hook that needs them of course - see below). You can also remove the `deploy.yaml` file if you want to - its not required to be on disk.
```yaml
clear_paths:
  paths:
    - README.md
    - package.json
    - composer.json
    - composer.lock
    - deploy.yaml
```

- `cleanup` - Control how old releases are cleaned up after the deployment is completed. Currently the only key here is `keep_deployments` which allows you to set the number of old deployments to keep. This is limited only by disk space! The default is 5.
```yaml
cleanup:
  keep_deployments: 10
```

### Hooks

`deploy` supports running arbitrary hooks before and after each deployment action. You can specify any CLI command and it will run using the permissions of the user your queue worker runs as via supervisor. The deployment actions are:

- `create_workspace`
- `checkout`
- `composer`
- `shared`
- `writables`
- `clear_paths`
- `activate`
- `finalise`
- `cleanup`

You can define `before` or `after` hooks for any of these actions by adding a new list to your `deploy.yaml` file. For example:
```yaml
composer:
  after:
    - /usr/bin/php scripts/post_dependency_script.php
    - /bin/bash scripts/my_bash_script.sh arg1 arg2
activate:
  before:
    - /usr/bin/php vendor/bin/phinx migrate
shared:
  after:
    - /usr/bin/php scripts/make_sure_shared_files_are_populated.php
```
Obviously the above configuration is made up to illustrate the point - you can run anything you need to make your deployment work. The `activate.before` hook shows an example of running the [phinx](https://github.com/cakephp/phinx) database migrations tool to automatically update the database schema prior to activation.

## Example deploy.yaml

```yaml
---
notify:
  slack:
    webhook: https://hooks.slack.com/services/12345679/ABCDE/FGHIJK
composer:
  install: install --no-dev -o
  after:
    - /usr/bin/php scripts/myscript.php
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
  after:
    - /usr/bin/php vendor/bin/phinx migrate
cleanup:
  keep_deployments: 10
```

## Roadmap (sort of!)

### Things to do

* [ ] Unit tests!
* [ ] Ability to trigger a deployment using a webhook
* [ ] Bitbucket support

### Things that are done

* [x] Implement re-activation rather than deployment for old releases (change of symlink)
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
* [ ] Allow different / extended defaults for specific frameworks
* [ ] Multi-server support

## Useful notes (for development)

* https://developer.github.com/v3/repos/contents/#get-contents
* https://mattstauffer.com/blog/introducing-envoyer.io/
* https://docs.gitlab.com/ee/api/repositories.html#get-file-archive
* https://stackoverflow.com/questions/35160169/bitbucket-how-to-download-latest-tar-gz-file-of-a-bitbucket-repo-programmatical
* https://stackoverflow.com/questions/17682143/download-private-bitbucket-repository-zip-file-using-http-authentication
* https://community.atlassian.com/t5/Bitbucket-questions/How-to-download-repository-as-zip-file-using-the-API/qaq-p/862113
* https://confluence.atlassian.com/bitbucket/app-passwords-828781300.html
