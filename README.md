# Bladestan

Laravel running on compiled Blade templates and extra static-analysis for Blade

## Install

```bash
composer require tomasvotruba/bladestan --dev
```

## Configure

Configure paths of views for the rule to scan using `templatePaths` config parameter key.

```yaml
parameters:
    template_paths:
        - resources/views
```

## Features

### Custom Error Formatter

We provide custom PHPStan error formatter to better display the template errors. The custom error formatter extends the PHPStan's table error formatter and just adds additional information about template errors to the message.

An example:
![](./assets/example.png "Custom error formatter output example")

To use this custom error formatter you need to run PHPStan with `--error-format blade` option. For example:
```shell
vendor/bin/phpstan analyse src -l8 --error-format blade
```

## Credits

People:

- [Can Vural](https://github.com/canvural) - this package is based on that, with upgrade for Laravel 10 and active maintenance
- [All Contributors](https://github.com/tomasvotruba/bladestane)
