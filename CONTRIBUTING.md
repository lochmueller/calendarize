# Contribution

Pull Requests are always welcome! Please don't forget to add an issue and connect it to your pull requests. This is very helpful to understand what kind of issue the PR is going to solve.

Bugfixes: Please describe what kind of bug your fix solve and give us feedback how to reproduce the issue. We're going to accept only bugfixes if we can reproduce the issue.

Features: Please create a issue first and describe the feature!

## Code Checks

The extension use PHP CS Fixer & PHP Unit in tooling. Please run "composer code-fix"/"composer code-check" or equivalent to execute PHP CS Fixer and the tests in front of the commit!

More tooling will be added shortly.

## Functional tests

Docker is required. Please run:

`./Build/Scripts/runTests.sh -s functional`
