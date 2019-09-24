# deploy

A work in progress, single server deployment tool.

## Things to do

* [x] User accounts
* [ ] Hooks
* [ ] Unit tests!
* [ ] Environment variable support?
* [x] Notifications
* [ ] Ability to keep specific releases
* [ ] Ability to deploy a specific branch
* [ ] Associate deployments with users

## Example deploy.yaml

```yaml
---
finalise:
  slack:
    webhook: https://hooks.slack.com/services/12345679/AKSJDHFGASJDHFG/ADLJFBWIAEJFBWIDJCDC
composer:
  install: install --no-dev
writables:
  - var/log
  - var/cache
shared:
  files:
    - ".env.config.ini"
  folders:
    - var/log
    - var/cache
    - var/db
clear_paths:
  - README.md
  - package.json
  - deploy.yaml
```

## Useful things

* https://developer.github.com/v3/repos/contents/#get-contents
* https://mattstauffer.com/blog/introducing-envoyer.io/
* https://docs.gitlab.com/ee/api/repositories.html#get-file-archive
