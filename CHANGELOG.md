# Changelog

All notable changes to `bumpcore/panorama` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.1] - 2026-06-02

### Added

- Add Laravel Boost AI guidelines for Panorama usage.

## [0.1.0] - 2026-06-01

### Added

- Add the `HasSource` trait for building Eloquent models from query sources.
- Add named source params with `withParams()` and builder-scoped param state.
- Add support for Eloquent builder and base query builder source returns.
- Add configurable source aliases with validation for empty aliases.
- Add package-specific exceptions for missing and invalid query sources.
- Add compatibility coverage for Eloquent relations, eager loading, `whereHas()`, custom builders, `staudenmeir/laravel-cte`, and `tpetry/laravel-postgresql-enhanced`.
- Add PHPUnit, PHPStan level 9, PHP CS Fixer, GitHub Actions, and a 100% coverage gate.
- Add README documentation and MIT license.

[Unreleased]: https://github.com/bumpcore/panorama/compare/v0.1.1...0.x
[0.1.1]: https://github.com/bumpcore/panorama/compare/v0.1.0...v0.1.1
[0.1.0]: https://github.com/bumpcore/panorama/releases/tag/v0.1.0
