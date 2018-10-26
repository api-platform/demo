# Contributing to API Platform

First of all, thank you for contributing, you're awesome!

To have your code integrated in the API Platform project, there is some rules to follow, but don't panic, it's easy!

## Reporting bugs

If you happen to find a bug, we kindly request you to report it. However, before submitting it, please:

  * Check the [project documentation available online](https://api-platform.com/docs/)

Then, if it appears that it's a real bug, you may report it using Github by following these 3 points:

  * Check if the bug is not already reported!
  * A clear title to resume the issue
  * A description of the workflow needed to reproduce the bug,

> _NOTE:_ Don’t hesitate giving as much information as you can (OS, PHP version extensions...)

## Pull requests

### Writing a Pull Request

First of all, you must decide on what branch your changes will be based. If the changes your are going to make are
fully backward-compatible, you should base your changes on the latest stable branch (`2.0` at the moment).
Otherwise, you should base your changes on the `master` branch.

### Matching Coding Standards

The API Platform project follows [Symfony coding standards](https://symfony.com/doc/current/contributing/code/standards.html).
But don't worry, you can fix CS issues automatically using the [PHP CS Fixer](http://cs.sensiolabs.org/) tool:

```bash
php-cs-fixer.phar fix
```

And then, add fixed file to your commit before push.
Be sure to add only **your modified files**. If another files are fixed by cs tools, just revert it before commit.

### Sending a Pull Request

When you send a PR, just make sure that:

* You add valid test cases.
* Tests are green.
* You make a PR on the related documentation in the [api-platform/docs](https://github.com/api-platform/docs) repository.
* You make the PR on the same branch you based your changes on. If you see commits
  that you did not make in your PR, you're doing it wrong.
* Also don't forget to add a comment when you update a PR with a ping to [the maintainers](https://github.com/orgs/api-platform/people),
  so he/she will get a notification.
* Squash your commits into one commit. (see the next chapter)

All Pull Requests must include the following header:

```markdown
| Q             | A
| ------------- | ---
| Bug fix?      | yes/no
| New feature?  | yes/no
| BC breaks?    | no
| Deprecations? | no
| Tests pass?   | yes
| Fixed tickets | #1234, #5678
| License       | MIT
| Doc PR        | api-platform/docs#1234
```

## Squash your Commits

If you have 3 commits. So start with:

```bash
git rebase -i HEAD~3
```

An editor will be opened with your 3 commits, all prefixed by `pick`.

Replace all `pick` prefixes by `fixup` (or `f`) **except the first commit** of the list.

Save and quit the editor.

After that, all your commits where squashed into the first one and the commit message of the first commit.

If you would like to rename your commit message type:

```bash
git commit --amend
```

Now force push to update your PR:

```bash
git push --force
```

## API tests

There are two kinds of tests in the API: unit (`phpunit`) and integration (`behat`) tests.

Both `phpunit` and `behat` are development dependencies and should be available in the `vendor` directory.

### PHPUnit and coverage generation

To launch unit tests:

```bash
docker-compose exec php bin/phpunit
```

If you want coverage, you will need the `phpdbg` package and run:

```bash
docker-compose exec php phpdbg -qrr bin/phpunit --coverage-html coverage
```

Coverage will be available in `api/coverage/index.html`.

### Behat

To launch Behat tests:

```bash
docker-compose exec php bin/behat
```

You may need to clear the cache manually before running Behat tests because of the temporary database. To do so, just
remove the `test` cache directory:

```bash
docker-compose exec php rm -r var/cache/test
```

### Doctrine schema validation

To analyse your Doctrine schema, use:

```bash
docker-compose exec php bin/console doctrine:schema:validate --skip-sync
```

### Security checker

To check security issues in project dependencies, use:

```bash
docker-compose exec php bin/security-checker security:check
```

## Doctrine Migrations

Here we use the doctrine migrations bundle to manage the database's schema.

To generate a migration version file, use the following command:

```bash
docker-compose exec php bin/console doctrine:migrations:diff
```

To generate a blank migration file:

```bash
docker-compose exec php bin/console doctrine:migrations:generate
```

To execute the migrations:

```bash
docker-compose exec php bin/console doctrine:migrations:migrate
```

To see the complete documentation: https://symfony.com/doc/master/bundles/DoctrineMigrationsBundle/index.html

## Doctrine Extensions

To use the doctrine extension bundle, you have to enable each extension you need in the `app/config.yml` file.

See details at the documentation [https://github.com/Atlantic18/DoctrineExtensions](https://github.com/Atlantic18/DoctrineExtensions).

## Client tests

### Jest and coverage generation

To launch unit tests:

```bash
docker-compose exec client yarn jest
```

If you want coverage, add `--coverage` option:

```bash
docker-compose exec client yarn jest --coverage
```

Coverage will be available in `coverage/clover.xml`.

### Nightwatch.js

Nightwatch.js is pre-configured in this project:

```bash
docker-compose exec client yarn nightwatch -e docker
```

# License and Copyright Attribution

When you open a Pull Request to the API Platform project, you agree to license your code under the [MIT license](LICENSE)
and to transfer the copyright on the submitted code to Kévin Dunglas.

Be sure to you have the right to do that (if you are a professional, ask your company)!

If you include code from another project, please mention it in the Pull Request description and credit the original author.
