# WebPA - An Online Peer Assessment Tool for Higher Education

WebPA is an online peer assessment system, or more specifically, a peer-moderated marking system. It is designed for 
teams of students doing groupwork, the outcome of which earns an overall group mark. Each student in a group grades 
their team-mates and their own performance. This grading is then used with the overall group mark to provide each 
student with an individual grade. The individual grade reflects the students contribution to the group.

## Requirements

The following versions of PHP are supported for the latest version of WebPA:

* PHP 7.4
* PHP 8.0

Your PHP instance must also have the following extensions enabled:

* MySQLi
* Sessions
* XML

## Installation

### Download

The easiest way to download WebPA is with the [composer package manager](https://getcomposer.org) using the following
command:

```
composer create-project --prefer-dist --no-dev webpa/webpa webpa
```

Alternatively you can download the latest release from this repository's [release page](https://github.com/WebPA/WebPA/releases).

### Configuration

WebPA has a number of configuration options allowing you to set your database credentials, SMTP mail host details and 
various other options.

The application comes bundled with a `.env.example` file which lists all of the configuation key-value pairs you can
set. 

For speedy development, you can copy this `.env.example` file to a file called `.env` and change the values to suit your
environment. The path of this file can be set in the `includes/inc_global.php` file.

For production environments, please *avoid* using the `.env` file as storing sensitive credentials in a file could be a 
security risk. Instead you should set these key-pairs as environment variables. In Apache, you can set these in your 
`.htaccess` file as follows:

```
SetEnv DB_HOST localhost
```

At a minimum, you should set the following environmental variables to let WebPA function:

* APP_WWW - URL to your instance of WebPA (set without a closing '/')
* DOC_ROOT - Directory path to the WebPA files (set with a closing '/')
* DB_HOST - Database host
* DB_USER - Database username
* DB_PASS - Database password
* DB_NAME - Database name
* DB_PREFIX - Database table prefix. Usually set to 'pa2_'

For more information on the dotenv file please visit the 
[dotenv package's repository](https://github.com/vlucas/phpdotenv). For more information on setting environmental 
variables in Apache, please [visit Apache's website](https://httpd.apache.org/docs/2.4/mod/mod_env.html#setenv).

### Initialise the Database
     
Run the following scripts to initialise the database (edit the files to change the names and password as reqired):

- install/webpa2\_database.sql: create the database schema and user account;
- install/webpa2\_tables.sql: create the database tables;
- install/webpa2\_administrator.sql: create an administrator account and sample module.

#### Upgrading an Existing Installation

If you already have WebPA installed and are upgrading from version 3.1.0 or below, please run:

- install/webpa\_security\_update.sql
     
### Login to WebPA

- navigate to root of WebPA application
- enter a username of _admin_ and a password of _admin_
- change the password to something more secure after logging in
		 
Delete the _install_ folder when you're finished.

## LTI Extension

The standard installation of WebPA does not include [LTI](https://www.imsglobal.org/activity/learning-tools-interoperability) (Learning Tools Interoperability) support which allows it to integrate seamlessly with most popular Virtual Learning Environments. This can be added via an extension created by Stephen P Vickers. Please visit [Stephen's site](http://www.spvsoftwareproducts.com/php/webpa-lti/) for instructions on how to obtain and install this extension. 

## Documentation

Documentation for WebPA can be found on the [WebPA project site](http://webpaproject.lboro.ac.uk/).

## Changelog

Please see our [changelog](https://github.com/WebPA/WebPA/blob/master/CHANGELOG.md) for a list of updates for this system.

This project uses [semantic versioning](https://semver.org/) from version 3.0.0 onwards.

## Contributing

We always welcome contributors to WebPA. If you can help with development, testing, or documentation, please submit a pull request to this repository.

## Support

Bugs and feature requests are tracked on this project's [GitHub issue tracker](https://github.com/WebPA/WebPA/issues).

## License

This software is distributed under the [GNU General Public License version 3](https://www.gnu.org/licenses/gpl-3.0.en.html).

You may copy, distribute and modify the software as long as you track changes/dates in source files. Any modifications 
to or software including (via compiler) GPL-licensed code must also be made available under the GPL along with build & 
install instructions.

## Credits

WebPA was originally developed by the Centre for Engineering and Design Education at [Loughborough University](http://www.lboro.ac.uk/) with financial support from [JISC](https://www.jisc.ac.uk/)'s e-Learning Capital Programme.

It continues to be maintained by a number of [open source contributors](https://github.com/WebPA/WebPA/graphs/contributors). We thank them for their time and effort supporting this system.
