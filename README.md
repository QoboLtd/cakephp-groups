# Groups plugin for CakePHP

[![Build Status](https://travis-ci.org/QoboLtd/cakephp-groups.svg?branch=master)](https://travis-ci.org/QoboLtd/cakephp-groups)
[![Latest Stable Version](https://poser.pugx.org/qobo/cakephp-groups/v/stable)](https://packagist.org/packages/qobo/cakephp-groups)
[![Total Downloads](https://poser.pugx.org/qobo/cakephp-groups/downloads)](https://packagist.org/packages/qobo/cakephp-groups)
[![Latest Unstable Version](https://poser.pugx.org/qobo/cakephp-groups/v/unstable)](https://packagist.org/packages/qobo/cakephp-groups)
[![License](https://poser.pugx.org/qobo/cakephp-groups/license)](https://packagist.org/packages/qobo/cakephp-groups)
[![codecov](https://codecov.io/gh/QoboLtd/cakephp-groups/branch/master/graph/badge.svg)](https://codecov.io/gh/QoboLtd/cakephp-groups)

## About

CakePHP 3+ plugin for managing user groups.

This plugin is developed by [Qobo](https://www.qobo.biz) for [Qobrix](https://qobrix.com).  It can be used as standalone CakePHP plugin, or as part of the [project-template-cakephp](https://github.com/QoboLtd/project-template-cakephp) installation.

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
