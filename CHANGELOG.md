# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1] - 2025-21-03

### Added

- New Artisan command `gateway-auth:publish` to easily publish configuration files
- Additional test coverage for command functionality and configuration options

### Changed

- Improved configuration publishing mechanism

## [1.0.0] - 2025-21-03

### Added

- Initial release
- Middleware for validating API Gateway requests
- Dual-layer authentication (API Key and Gateway Secret)
- Configurable header names
- Debug mode for development environments
- Request logging capabilities
- Support for Laravel 10.x
- Comprehensive test suite
- Detailed documentation

### Security

- Header-based authentication system
- Environmental variable configuration
