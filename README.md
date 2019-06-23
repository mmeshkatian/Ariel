# Ariel

[![Total Downloads](https://poser.pugx.org/mmeshkatian/ariel/downloads.png)](https://packagist.org/packages/mmeshkatian/ariel)
[![Latest Stable Version](https://poser.pugx.org/mmeshkatian/ariel/v/stable)](https://packagist.org/packages/mmeshkatian/ariel)
[![Latest Unstable Version](https://poser.pugx.org/mmeshkatian/ariel/v/unstable)](https://packagist.org/packages/mmeshkatian/ariel)
[![License](https://poser.pugx.org/mmeshkatian/ariel/license)](https://packagist.org/packages/mmeshkatian/ariel)
[![Awesome Laravel](https://img.shields.io/badge/Awesome-Laravel-brightgreen.svg?style=flat-square)](https://github.com/mmeshkatian/ariel)


A simple & light-weight laravel package to manage your webApplications.
## Requirements

- PHP >= 7.0.0
- Laravel >= 5.4.0
 
## Installation

Install the package through [Composer](http://getcomposer.org/). 

Run the Composer require command from the Terminal:

    composer require mmeshkatian/ariel
    
If you're using Laravel > 5.5, this is all there is to do. 

Should you still be on version 5.4 of Laravel, the final steps for you are to add the service provider of the package and alias the package. To do this open your `config/app.php` file.

Add a new line to the `providers` array:

	Mmeshkatian\Ariel\ArielServiceProvider::class

And optionally add a new line to the `aliases` array:

	'Ariel' => Mmeshkatian\Ariel\Facade::class,

Now you're ready to start using the Ariel in your application.

## Overview
Look at one of the following topics to learn more about Ariel

* [Configuration](#configuration)
* [Controller Builder](#controllerBuilder)
* [Api Builder](#apiBuilder)

## Configuration
run these commands to publish assets and config：

    php artisan vendor:publish --provider="Mmeshkatian\Ariel\ArielServiceProvider"


## Controller Builder

## Api Builder
