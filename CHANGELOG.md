# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [3.0.1] - 2018-11-07

### Fixed
- Fix a bug where a unix timestamps were used in the email notifying users that an assessment had been reopened (PR #32)
- Fix a bug where the resource from the create_xml_parser() object was being assigned by reference, causing a strict notice to be issued by PHP (PR #39)
- Fix a bug where accessing undefined array index in the class_dao fetch_value() function caused a warning to be issued (PR #37)
- Fix a bug where tutors who are also students can only see a student menu when logged into WebPA (PR #41)

### Security
- Prevent SQL injection attack for the login and password reset page (PR #40)

## [3.0.0] - 2018-08-06

### Added
- Support for PHP 7.x

## [2.0.0.11] - 2013-12-05

## [1.1.0.1] - 2008-07-19

[Unreleased]: https://github.com/WebPA/WebPA/compare/v3.0.1...HEAD
[3.0.1]: https://github.com/WebPA/WebPA/compare/v3.0.0...v3.0.1
[3.0.0]: https://github.com/WebPA/WebPA/compare/v2.0.0.11...v3.0.0
[2.0.0.11]: https://github.com/WebPA/WebPA/compare/v1.1.0.1...v2.0.0.11
[1.1.0.1]: https://github.com/WebPA/WebPA/releases/tag/v1.1.0.1
