# deploy

A work in progress, single server deployment tool.

## Things to do

* User accounts
* Hooks
* Builder logging
* Release detail page
* Unit tests!
* Environment variable support?
* Notifications
* Ability to keep specific releases
* Ability to deploy a specific branch

## deploy.yaml (working example)

```yaml
---
initialise:
  notify:
    type: email
    to: ronan@d3r.com
  post:
  - "{{php}} bin/console run:thing"
prepare:
  composer: true
  writables:
    mode: '0750'
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
  post:
  - "{{php}} bin/console clear:cache"
finalise:
  notify:
    type: email
    to: ronan@d3r.com
  clear_opcache:
    socket: /var/run/php70.sock
```

## Useful things

* https://developer.github.com/v3/repos/contents/#get-contents
* https://mattstauffer.com/blog/introducing-envoyer.io/
