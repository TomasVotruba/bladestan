# phpstan-blade-rule

PHPStan rule for static analysis of Blade templates.

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

- [ ] In error formatter relative paths for templates can be displayed, instead of just file name.
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