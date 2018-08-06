# WebPA - An Online Peer Assessment Tool for Higher Education

WebPA is an online peer assessment system, or more specifically, a peer-moderated marking system. It is designed for 
teams of students doing groupwork, the outcome of which earns an overall group mark. Each student in a group grades 
their team-mates and their own performance. This grading is then used with the overall group mark to provide each 
student with an individual grade. The individual grade reflects the students contribution to the group.

## Requirements

The following versions of PHP are supported:

* PHP 7.0
* PHP 7.1
* PHP 7.2

Your PHP instance must also have the following extensions enabled:

* MySQLi
* Sessions
* XML

## Installation

### Download

The easiest way to download WebPA is with the [composer package manager](https://getcomposer.org) using the following
command:

```
composer create-project webpa/webpa webpa
```

Alternatively you can download the latest release from this repository's [release page](https://github.com/WebPA/WebPA/releases).

### Setup

Edit the includes/inc\_global.php file in the includes directory to configure the application; in particular:

- APP\_\_WWW: URL to the instance of WebPA (without a closing "/");
- DOC\_\_ROOT: directory path to the WebPA files (with a closing "/" or "\\");
- database settings:
	- APP\_\_DB\_HOST: host name
	- APP\_\_DB\_USERNAME: username
	- APP\_\_DB\_PASSWORD: password
	- APP\_\_DB\_DATABASE: database name
	- APP\_\_DB\_TABLE\_PREFIX: table prefix (default is `pa2_` which means that the new version can share the same database as the old version if required)
- Configure the LDAP settings if you wish to authenticate via LDAP.
     
Run the following scripts to initialise the database (edit the files to change the names and password as reqired):

- install/webpa2\_database.sql: create the database schema and user account;
- install/webpa2\_tables.sql: create the database tables;
- install/webpa2\_administrator.sql: create an administrator account and sample module.
     
Login to WebPA:

- navigate to root of WebPA application;
- enter a username of "admin" and a password of "admin"
- change the password to something more secure after logging in
		 
Delete the _install_ folder when you're finished.

## LTI Extension

The standard installation of WebPA does not include [LTI](https://www.imsglobal.org/activity/learning-tools-interoperability) (Learning Tools Interoperability) support which allows it to integrate seamlessly with most popular Virtual Learning Environments. This can be added via an extension created by Stephen P Vickers. Please visit [Stephen's site](http://www.spvsoftwareproducts.com/php/webpa-lti/) for instructions on how to obtain and install this on extension. 

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

It continues to be mainted by a number of [open source contributors](https://github.com/WebPA/WebPA/graphs/contributors). We thank them for their time and effort supporting this system.
