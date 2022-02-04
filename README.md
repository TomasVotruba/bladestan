# phpstan-blade-rule

<p align="center">
  <a href="https://github.com/canvural/phpstan-blade-rule/actions"><img src="https://github.com/canvural/phpstan-blade-rule/workflows/Tests/badge.svg" alt="Build Status"></a>
  <a href=""><img src="https://img.shields.io/badge/PHPStan-Level%20Max-brightgreen.svg?style=flat&logo=php" alt="PHPStan level max"></a>
  <a href="https://packagist.org/packages/canvural/phpstan-blade-rule/stats"><img src="https://poser.pugx.org/canvural/phpstan-blade-rule/d/total.svg" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/canvural/phpstan-blade-rule"><img src="https://poser.pugx.org/canvural/phpstan-blade-rule/v/stable.svg" alt="Latest Version"></a>
  <a href="https://github.com/canvural/phpstan-blade-rule/blob/main/LICENSE.md"><img src="https://poser.pugx.org/canvural/phpstan-blade-rule/license.svg" alt="License"></a>
  <br><br>
  PHPStan rule for static analysis of Blade templates.
</p>
<hr>

## Installation

To use this extension, require it in [Composer](https://getcomposer.org/):

```bash
composer require --dev canvural/phpstan-blade-rule
```

If you also have [phpstan/extension-installer](https://github.com/phpstan/extension-installer) installed, then you're all set!

<details>
  <summary>Manual installation</summary>

If you don't want to use `phpstan/extension-installer`, include extension.neon in your project's PHPStan config:

```neon
includes:
    - vendor/canvural/phpstan-blade-rule/config/extension.neon
    - vendor/symplify/template-phpstan-compiler/config/services.neon
    - vendor/symplify/astral/config/services.neon
```
</details>

## Configuration

You need to configure paths of views for the rule to scan using `templatePaths` config parameter key. Each path should be a relative path to your `phpstan.neon` config file.

For example for default Laravel installation, you can configure the paths like so:

```neon
parameters:
    templatePaths:
        - resources/views
```

## Features

### Custom error formatter

We provide custom PHPStan error formatter to better display the template errors. The custom error formatter extends the PHPStan's table error formatter and just adds additional information about template errors to the message.

An example:
![](./assets/example.png "Custom error formatter output example")

To use this custom error formatter you need to run PHPStan with `--error-format blade` option. For example:
```shell
vendor/bin/phpstan analyse src -l8 --error-format blade
```

### Known issues / TODOs

- [ ] Custom directives are not supported. Can be supported by custom bootstrap file maybe.
- [ ] Blade components are not analyzed. Support for it will come soon. 

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

People:
- [Can Vural](https://github.com/canvural)
- [All Contributors](https://github.com/canvural/phpstan-blade-rule/contributors)

Resources:
- [symplify/template-phpstan-compiler](https://github.com/symplify/template-phpstan-compiler)
- [symplify/twig-phpstan-compiler](https://github.com/symplify/twig-phpstan-compiler)
- [symplify/latte-phpstan-compiler](https://github.com/symplify/latte-phpstan-compiler)
- [symplify/phpstan-latte-rules](https://github.com/symplify/phpstan-latte-rules)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
