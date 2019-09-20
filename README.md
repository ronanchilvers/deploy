# deploy

A work in progress, single server deployment tool.

## Things to do

* User accounts
* Hooks
* Unit tests!
* Environment variable support?
* Notifications
* Ability to keep specific releases
* Ability to deploy a specific branch

## Example deploy.yaml

```yaml
---
composer:
  install: install --no-dev
writables:
  folders:
    - var/log
    - var/cache
  files:
    - var/db/app.sq3
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
