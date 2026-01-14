<div align="center">
    <a href="https://github.com/phptg">
        <img src="logo.png" alt="PHPTG">
    </a>
    <h1 align="center">PHPTG Scaffolder</h1>
    <br>
</div>

PHPTG Scaffolder is a tool for setting up project structure and configuration
for PHP packages in the [PHPTG](https://github.com/phptg). It is based on
[vjik/scaffolder](https://github.com/vjik/scaffolder) and automatically creates and configures essential
project files including `composer.json`, GitHub Actions workflows, configuration files, and documentation structure.

[![Static analysis](https://github.com/phptg/scaffolder/actions/workflows/phpstan.yml/badge.svg?branch=master)](https://github.com/phptg/scaffolder/actions/workflows/phpstan.yml?query=branch%3Amaster)

## General Usage

Run the scaffolder using Docker from your project directory:

```bash
docker run \
  --volume .:/project \
  --user $(id -u):$(id -g) \
  --interactive --tty --rm --init \
  ghcr.io/phptg/scaffolder:latest
```

## Documentation

If you have any questions or problems with this package, use [author telegram chat](https://t.me/predvoditelev_chat) for communication.

## License

The `phptg/scaffolder` is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE) for more information.
