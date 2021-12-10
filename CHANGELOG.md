# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [3.2.1] - 2021-12-10
### Fixed
- The environment variable for the database port number now applies to all database connections in WebPA (PR #91)

## [3.2.0] - 2021-12-06
### Added
- You can now specify the MySQL port number in the environment variables (PR #88)

### Fixed
- Fixed bug where students were told they had not submitted to a closed assessment when they had (PR #89)

## [3.1.2] - 2021-04-22
### Changed
- Emails are now triggered via a single script, `jobs/Email.php` instead of calling`tutors/asessments/email/ClosingReminder.php` and `tutors/assessments/email/TriggerReminder.php` directly. 

### Removed
- Removed LDAP functionality and options as the implementation did not work.

### Security
- Fixed a large amount of SQL injection attacks
- Change password hashing to use the native password_hash() function in PHP instead of MD5 hashing which is insecure

## [3.1.1] - 2020-10-02
### Fixed
- Remove LTIAuthenticator class as it is a duplicate of DBAuthenticator as should not be used (PR #77)
- Fix bugs found using static analysis (PR #78)

## [3.1.0] - 2020-09-16
### Changed
- Removed most include and require statements and replaced with PSR-4 autoloading (PR #69)
- Remove all global variables from the application to make it easier to maintain the code (PR #70)
### Fixed
- Change default authenticator to be database instead of SAML (PR #64)

## [Unreleased]

## [3.0.7] - 2020-01-20
### Fixed
- Remove a blank line at the top of a PHP class that was causing a fatal error
- Fixed the display academic drop down which was not displaying past years (PR #62)

## [3.0.6] - 2019-11-18
### Fixed
- Fixed a PHP syntax issue in the class_engcis.php file where a missing closing bracket was causing a fatal error to be thrown. (PR #60) 

## [3.0.5] - 2019-11-08
### Fixed
- When using the LDAP integration, users logging in had their firstname, surname, and email address set to a blank value. This was due to code being added to the system that was specific to one insitution, rather than a generalised implementation. It has been fixed by reverting the LDAP authenticator to the same one as in version 2 (PR #59)

## [3.0.4] - 2019-10-18
### Fixed
- When viewing assessments in a module, if no assessment has been created the dropdown will display the years 1969/70. This has now been fixed to display the current academic year instead (PR #58)

## [3.0.3] - 2019-03-21
### Fixed
- Fix undefined offset notice in the class_dao.php file (PR #48)
- Fix respondent list for assessments not showing (PR #48)
- Fix login issue where activity timestamp was not being stored in the database (PR #48)
- Fix issue preventing users from creating assessments via the assessment wizard (PR #48)

## [3.0.2] - 2018-11-20
### Fixed
- Fixed a bug where the database connection was not being properly closed down, meaning subsequent database calls would always fail, even if the `$DB->open()` function was called as the old, closed connection would not be replaced or reinstantiated (PR #43)
- Fixed a bug where only one year value can be displayed in the assessments and metrics academic year drop down (PR #42)

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

[Unreleased]: https://github.com/WebPA/WebPA/compare/v3.2.1...HEAD
[3.2.1]: https://github.com/WebPA/WebPA/compare/v3.2.0...v3.2.1
[3.2.0]: https://github.com/WebPA/WebPA/compare/v3.1.2...v3.2.0
[3.1.2]: https://github.com/WebPA/WebPA/compare/v3.1.1...v3.1.2
[3.1.1]: https://github.com/WebPA/WebPA/compare/v3.1.0...v3.1.1
[3.1.0]: https://github.com/WebPA/WebPA/compare/v3.0.7...v3.1.0
[3.0.7]: https://github.com/WebPA/WebPA/compare/v3.0.6...v3.0.7
[3.0.6]: https://github.com/WebPA/WebPA/compare/v3.0.5...v3.0.6
[3.0.5]: https://github.com/WebPA/WebPA/compare/v3.0.4...v3.0.5
[3.0.4]: https://github.com/WebPA/WebPA/compare/v3.0.3...v3.0.4
[3.0.3]: https://github.com/WebPA/WebPA/compare/v3.0.2...v3.0.3
[3.0.2]: https://github.com/WebPA/WebPA/compare/v3.0.1...v3.0.2
[3.0.1]: https://github.com/WebPA/WebPA/compare/v3.0.0...v3.0.1
[3.0.0]: https://github.com/WebPA/WebPA/compare/v2.0.0.11...v3.0.0
[2.0.0.11]: https://github.com/WebPA/WebPA/compare/v1.1.0.1...v2.0.0.11
[1.1.0.1]: https://github.com/WebPA/WebPA/releases/tag/v1.1.0.1
