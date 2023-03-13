# Bladestan

Static analysis for Blade templates in Laravel projects.

## Install

```bash
composer require tomasvotruba/bladestan --dev
```

## Configure

Configure paths to your Blade views, unless you use the default `resources/views` directory:

```yaml
parameters:
    bladestan:
        template_paths:
            # default
            - resources/views
```

## Features

### Custom Error Formatter

We provide custom PHPStan error formatter to better display the template errors. The custom error formatter extends the PHPStan's table error formatter and just adds additional information about template errors to the message.

An example:

![](./assets/example.png "Custom error formatter output example")

How to use custom error formatter?

```bash
vendor/bin/phpstan analyze --error-format blade
```

## Credits

People:

- [Can Vural](https://github.com/canvural) - this package is based on that, with upgrade for Laravel 10 and active maintenance
- [All Contributors](https://github.com/TomasVotruba/bladestan/graphs/contributors)
