# deploy

A tool for simple deployments from common source control providers.

* Github and Gitlab support (Bitbucket planned)
* Zero downtime deployments with rollbacks
* Fine grained control of deployments using a repository based configuration file
* Responsive UI - fully usable on any device
* Shared and writable path support
* Arbitrary hook support
* Slack notifications
* Simple user account management

## Things to do

* [ ] Unit tests!
* [ ] Ability to trigger a deployment using a webhook
* [ ] Bitbucket support

## Things that are done

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
