# How to contribute

We accept third party PRs, we simply have only a few requirements:

1. All code must satisfy PSR-2 code standards. You can check if your code is compliant by running `./vendor/bin/phpcs --standard=psr2 src tests`.
2. All `PHPUnit` tests must pass. Travis will automatically run these tests (and `phpcs`) on your
pull requests automatically, but please try to do this before submitting the PR.
3. Your code should be tested. Please ensure you have some unit tests covered your proposed changes.

All PRs should target the `master` branch. We will periodically tag the master branch when we feel
a new release is warranted. Versioning follows semver, so if your code is not backwards
compatible, it may take longer to land in an official version available from packagist.

## Security related disclosure

If you have discovered a security vulnerability (or think you have), please contact us
at [engineering@zumba.com](mailto:engineering@zumba.com) with steps to reproduce. Once we have a fix in place and released,
we will disclose publically what the vulnerability was, what versions were affected, and steps to patch.
