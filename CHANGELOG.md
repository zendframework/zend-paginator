# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.7.0 - 2016-04-11

### Added

- [#19](https://github.com/zendframework/zend-paginator/pull/19) adds:
  - `Zend\Paginator\AdapterPluginManagerFactory`
  - `Zend\Paginator\ScrollingStylePluginManagerFactory`
  - `ConfigProvider`, which maps the `AdapterPluginManager` and
    `ScrollingStylePluginManager` services to the above factories.
  - `Module`, which does the same, for zend-mvc contexts.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.6.1 - 2016-04-11

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#7](https://github.com/zendframework/zend-paginator/pull/7) adds aliases for
  the old `Null` adapter, mapping them to the new `NullFill` adapter.

## 2.6.0 - 2016-02-23

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#4](https://github.com/zendframework/zend-paginator/pull/4),
  [#8](https://github.com/zendframework/zend-paginator/pull/8), and
  [#18](https://github.com/zendframework/zend-paginator/pull/18) update the code
  base to be forwards-compatible with the v3 releases of zend-servicemanager and
  zend-stdlib.
