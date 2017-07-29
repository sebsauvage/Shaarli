## Basics
Install [Docker](https://www.docker.com/), by following the instructions relevant
to your OS / distribution, and start the service.

### Search an image on [DockerHub](https://hub.docker.com/)

```bash
$ docker search debian

NAME            DESCRIPTION                                     STARS   OFFICIAL   AUTOMATED
ubuntu          Ubuntu is a Debian-based Linux operating s...   2065    [OK]
debian          Debian is a Linux distribution that's comp...   603     [OK]
google/debian                                                   47                 [OK]
```

### Show available tags for a repository
```bash
$ curl https://index.docker.io/v1/repositories/debian/tags | python -m json.tool

% Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
Dload  Upload   Total   Spent    Left  Speed
100  1283    0  1283    0     0    433      0 --:--:--  0:00:02 --:--:--   433
```

Sample output:
```json
[
    {
        "layer": "85a02782",
        "name": "stretch"
    },
    {
        "layer": "59abecbc",
        "name": "testing"
    },
    {
        "layer": "bf0fd686",
        "name": "unstable"
    },
    {
        "layer": "60c52dbe",
        "name": "wheezy"
    },
    {
        "layer": "c5b806fe",
        "name": "wheezy-backports"
    }
]

```

### Pull an image from DockerHub
```bash
$ docker pull repository[:tag]

$ docker pull debian:wheezy
wheezy: Pulling from debian
4c8cbfd2973e: Pull complete
60c52dbe9d91: Pull complete
Digest: sha256:c584131da2ac1948aa3e66468a4424b6aea2f33acba7cec0b631bdb56254c4fe
Status: Downloaded newer image for debian:wheezy
```
