# Contributing

Contributions are **welcome** and will be fully **credited**.

Please read and understand the contribution guide before creating an issue or pull request.

## Etiquette

This project is open source, and as such, the maintainers give their free time to build and maintain the source code
held within. They make the code freely available in the hope that it will be of use to other developers. It would be
extremely unfair for them to suffer abuse or anger for their hard work.

Please be considerate towards maintainers when raising issues or presenting pull requests. Let's show the
world that developers are civilized and selfless people.

It's the duty of the maintainer to ensure that all submissions to the project are of sufficient
quality to benefit the project. Many developers have different skillsets, strengths, and weaknesses. Respect the maintainer's decision, and do not be upset or abusive if your submission is not used.

## Viability

When requesting or submitting new features, first consider whether it might be useful to others. Open
source projects are used by many developers, who may have entirely different needs to your own. Think about
whether or not your feature is likely to be used by other users of the project.

## Procedure

Before filing an issue:

- Attempt to replicate the problem, to ensure that it wasn't a coincidental incident.
- Check to make sure your feature suggestion isn't already present within the project.
- Check the pull requests tab to ensure that the bug doesn't have a fix in progress.
- Check the pull requests tab to ensure that the feature isn't already in progress.

Before submitting a pull request:

- Check the codebase to ensure that your feature doesn't already exist.
- Check the pull requests to ensure that another person hasn't already submitted the feature or fix.

## Requirements

If the project maintainer has any additional requirements, you will find them listed here.

### Coding Standards

We are using [`squizlabs/PHP_CodeSniffer`](https://github.com/squizlabs/PHP_CodeSniffer) to enforce coding standards in PHP files.

Run

```
$ composer fix-cs
```

to automatically fix coding standard violations.

### Static Code Analysis

We are using [`phpstan/phpstan`](https://github.com/phpstan/phpstan) to statically analyze the code.

Run

```
$ composer phpstan
```

to run a static code analysis.

### Tests

We are using [`phpunit/phpunit`](https://github.com/sebastianbergmann/phpunit) to drive the development.

Run

```
$ composer tests
```

to run all the tests.

:exclamation: Make sure to run the whole test suite before opening a PR with the suggested changes.

**Happy coding**!