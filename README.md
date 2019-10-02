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

In addition it is *strongly* recommended that you use a proper RDBMS like MySQL
or MariaDB to host the database. The default sqlite database is suitable for
development but you will almost certainly run into database locks if you use it
in production.

`deploy` includes a queue runner that does the heavy lifting. You can run this
via cron if you want to but I recommend using supervisord (again available in most
linux distributions in the standard package catalogue).

Once you have the required software installed on the host you can then get on with
the installation.



## Things to do

* [ ] Unit tests!
* [ ] Ability to trigger a deployment using a webhook
* [ ] Bitbucket support
* [ ] Better user account support

## Things that are done

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
