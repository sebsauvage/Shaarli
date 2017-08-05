## Introduction
### PGP and GPG
[Gnu Privacy Guard](https://gnupg.org/) (GnuPG) is an Open Source implementation of the
[Pretty Good Privacy](https://en.wikipedia.org/wiki/Pretty_Good_Privacy#OpenPGP)
(OpenPGP) specification. Its main purposes are digital authentication, signature and encryption.

It is often used by the [FLOSS](https://en.wikipedia.org/wiki/Free_and_open-source_software) community to verify:

- Linux package signatures: Debian [SecureApt](https://wiki.debian.org/SecureApt), ArchLinux [Master 
Keys](https://www.archlinux.org/master-keys/)
- [SCM](https://en.wikipedia.org/wiki/Revision_control) releases & maintainer identity

### Trust
To quote Phil Pennock (the author of the [SKS](https://bitbucket.org/skskeyserver/sks-keyserver/wiki/Home) key server - http://sks.spodhuis.org/):

> You MUST understand that presence of data in the keyserver (pools) in no way connotes trust. Anyone can generate a key, with any name or email address, and upload it. All security and trust comes from evaluating security at the “object level”, via PGP Web-Of-Trust signatures. This keyserver makes it possible to retrieve keys, looking them up via various indices, but the collection of keys in this public pool is KNOWN to contain malicious and fraudulent keys. It is the common expectation of server operators that users understand this and use software which, like all known common OpenPGP implementations, evaluates trust accordingly. This expectation is so common that it is not normally explicitly stated.

Trust can be gained by having your key signed by other people (and signing their key back, too :) ), for instance during [key signing parties](https://en.wikipedia.org/wiki/Key_signing_party), see:

- [The Keysigning party HOWTO](http://www.cryptnet.net/fdp/crypto/keysigning_party/en/keysigning_party.html)
- [Web of trust](https://en.wikipedia.org/wiki/Web_of_trust)

## Generate a GPG key
- [Generating a GPG key for Git tagging](http://stackoverflow.com/a/16725717) (StackOverflow)
- [Generating a GPG key](https://help.github.com/articles/generating-a-gpg-key/) (GitHub)

### gpg - provide identity information
```bash
$ gpg --gen-key

gpg (GnuPG) 2.1.6; Copyright (C) 2015 Free Software Foundation, Inc.
This is free software: you are free to change and redistribute it.
There is NO WARRANTY, to the extent permitted by law.

Note: Use "gpg2 --full-gen-key" for a full featured key generation dialog.

GnuPG needs to construct a user ID to identify your key.

Real name: Marvin the Paranoid Android
Email address: marvin@h2g2.net
You selected this USER-ID:
    "Marvin the Paranoid Android <marvin@h2g2.net>"

Change (N)ame, (E)mail, or (O)kay/(Q)uit? o
We need to generate a lot of random bytes. It is a good idea to perform
some other action (type on the keyboard, move the mouse, utilize the
disks) during the prime generation; this gives the random number
generator a better chance to gain enough entropy.
```

### gpg - entropy interlude
At this point, you will:
- be prompted for a secure password to protect your key (the input method will depend on your Desktop Environment and configuration)
- be asked to use your machine's input devices (mouse, keyboard, etc.) to generate random entropy; this step _may take some time_ 

### gpg - key creation confirmation
```bash
gpg: key A9D53A3E marked as ultimately trusted
public and secret key created and signed.

gpg: checking the trustdb
gpg: 3 marginal(s) needed, 1 complete(s) needed, PGP trust model
gpg: depth: 0  valid:   2  signed:   0  trust: 0-, 0q, 0n, 0m, 0f, 2u
pub   rsa2048/A9D53A3E 2015-07-31
      Key fingerprint = AF2A 5381 E54B 2FD2 14C4  A9A3 0E35 ACA4 A9D5 3A3E
uid       [ultimate] Marvin the Paranoid Android <marvin@h2g2.net>
sub   rsa2048/8C0EACF1 2015-07-31
```

### gpg - submit your public key to a PGP server (Optional)
``` bash
$ gpg --keyserver pgp.mit.edu --send-keys A9D53A3E
gpg: sending key A9D53A3E to hkp server pgp.mit.edu
```

## Create and push a GPG-signed tag

See [Release Shaarli](Release Shaarli).
