# Structura

[![License](https://img.shields.io/github/license/structuraphp/structura.svg)](https://github.com/structuraphp/structura/blob/main/LICENSE "LICENSE")
[![PHP from Packagist](https://img.shields.io/badge/PHP-%3E%3D8.2-%238892bf)](/README.md#php-version "PHP version 8.2 minimum")
[![Packagist Downloads](https://img.shields.io/packagist/dm/structuraphp/structura)](https://packagist.org/packages/structuraphp/structura "packagist downloads")

<p align="center">
  <img src="docs/structura-logo.png" alt="Structura Logo" width="400">
</p>

## About

Structura is an architectural testing tool for PHP, designed to help developers maintain a clean and
consistent code structure.

## 📖 Documentation

Full documentation is available at **[structuraphp.github.io/structura](https://structuraphp.github.io/structura)**.

## Quick Start

### Installation

```shell
composer require --dev structuraphp/structura
```

### Configuration

```shell
php vendor/bin/structura init
```

### Run

```shell
php vendor/bin/structura analyze
```

## Requirements

| PHP Version           | Structura 0.x |
|-----------------------|---------------|
| <= 8.1                | ✗ Unsupported |
| 8.2 / 8.3 / 8.4 / 8.5 | ✓ Supported   |

## With PHPUnit

Structura can integrate architecture testing with PHPUnit:

<https://github.com/structuraphp/structura-phpunit>

## Inspiration

StructuraPHP is the result of current tools failing to meet, or only partially meeting, our needs as an architecture
testing tool.

Our design and features are heavily inspired by the following tools:

- [ArchUnit](https://github.com/TNG/ArchUnit): for rules
- [Arkitect](https://github.com/phparkitect/arkitect): for rules, independence, full php,
- [Deptract](https://github.com/deptrac/deptrac): for rules
- [Pest Architecture](https://pestphp.com/docs/arch-testing): for rules, function names, output format,
- [PhpUnit](https://github.com/sebastianbergmann/phpunit/): for test classes, flags,
- [Phpat](https://github.com/carlosas/phpat): for rules.
