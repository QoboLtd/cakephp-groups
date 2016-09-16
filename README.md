# Groups plugin for CakePHP

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

This plugin works along with [CakeDC Users plugin](https://github.com/CakeDC/users).

The recommended way to install composer packages is:

```
composer require qobo/cakephp-groups
```

Run plugin's migration task:

```
bin/cake migrations migrate -p Groups
```

Run required plugin(s) migration task:

```
bin/cake migrations migrate -p CakeDC/Users
```

```
## Setup
Load plugin
```
bin/cake plugin load --routes Groups
```

Load required plugin(s)
```
bin/cake plugin load Muffin/Trash
bin/cake plugin load --routes --bootstrap CakeDC/Users
```