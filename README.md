Project
    - Manifest
    - Deployment
      - Environment Variables

* Deployment steps
  - initialise
    - create release
    - checkout code
  - prepare
    - composer
    - shared files/folders
    - writables
  - release
    - symlink new release



Builder
  -> stage : initialise
  -> stage : prepare
  -> stage : release


$builder = new Builder();
$builder->registerComponent(
  Builder::INITIALISE,
  $container->get(NotifyComponent::class)
);

BuilderInterface
  public function registerStage(StageInterface $stage)

StageInterface
  public function registerStep()
  public function execute()

Stage\ComponentInterface
  public function configure(array $data)
  public function execute()

Stage\ConfigurationInterface
  public set($key, $value)
  public get($key, $default = null)



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


{
    "initialise": {
        "notify": {
            "type": "email",
            "to": "ronan@d3r.com"
        },
        "post": [
            "{{php}} bin/console run:thing"
        ]
    },
    "prepare": {
        "composer": true,
        "writables": {
            "mode": "0750",
            "folders": [
                "var/log",
                "var/cache"
            ],
            "files": [
                "var/db/app.sq3"
            ]
        },
        "shared": {
            "files": [
                ".env.config.ini"
            ],
            "folders": [
                "var/log",
                "var/cache",
                "var/db"
            ]
        },
        "post": [
            "{{php}} bin/console clear:cache"
        ]
    },
    "finalise": {
        "notify": {
            "type": "email",
            "to": "ronan@d3r.com"
        },
        "clear_opcache": false
    }
}