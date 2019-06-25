# Ariel

[![Total Downloads](https://poser.pugx.org/mmeshkatian/ariel/downloads.png)](https://packagist.org/packages/mmeshkatian/ariel)
[![Latest Stable Version](https://poser.pugx.org/mmeshkatian/ariel/v/stable)](https://packagist.org/packages/mmeshkatian/ariel)
[![Latest Unstable Version](https://poser.pugx.org/mmeshkatian/ariel/v/unstable)](https://packagist.org/packages/mmeshkatian/ariel)
[![License](https://poser.pugx.org/mmeshkatian/ariel/license)](https://packagist.org/packages/mmeshkatian/ariel)
[![Awesome Laravel](https://img.shields.io/badge/Awesome-Laravel-brightgreen.svg)](https://github.com/mmeshkatian/ariel)


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
### Persian Simple Usage : [Virgool.io](https://virgool.io/@mmeshkatian/%D8%AA%D9%88%D8%B3%D8%B9%D9%87-%D9%BE%D9%86%D9%84-%D9%85%D8%AF%DB%8C%D8%B1%DB%8C%D8%AA-%D8%A8%D8%A7-laravel-%D9%88-%D9%BE%DA%A9%DB%8C%D8%AC-ariel-j4yubj1qcmbt). 
### English Simple Usage : [medium.com](https://virgool.io/@mmeshkatian/%D8%AA%D9%88%D8%B3%D8%B9%D9%87-%D9%BE%D9%86%D9%84-%D9%85%D8%AF%DB%8C%D8%B1%DB%8C%D8%AA-%D8%A8%D8%A7-laravel-%D9%88-%D9%BE%DA%A9%DB%8C%D8%AC-ariel-j4yubj1qcmbt). 

## Overview
Look at one of the following topics to learn more about Ariel

* [Configuration](#configuration)
* [ControllerBuilder](#controllerBuilder)
* [ApiBuilder](#apiBuilder)

## Configuration
run these commands to publish assets and config：

    php artisan vendor:publish --provider="Mmeshkatian\Ariel\ArielServiceProvider"


## Controller Builder
create your controller by artisan command

	php artisan make:controller TestArielController

open TestArielController placed in App/Http/Controllers then change the controller like below :
```php
<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Mmeshkatian\Ariel\BaseController;

class TestArielController extends BaseController
```
your controller should extends from Mmeshkatian\Ariel\BaseController .
ariel use configure method to initialize so create configure method in your controller .

```php
public function configure()
    {
    
    }
```

### configure method
#### variables

| Variable                          | Description                              | Example                          |
| ----------------------------------| ---------------------------------------- | -------------------------------- |
| $this->model                      | Define Base Model to work with.          | $this->model = User::class |
| $this->RoutePrefix     (Optional) | Define your Route prefix.(for "CRUD" auto generation)        | $this->RoutePrefix = 'ariel' |

#### methods
##### setRoute($route,$name,$params = [])
Change default auto-generated route.

| Parameter   | Description                                                    |
|-------------|----------------------------------------------------------------|
| $route      | Route identifier to change ["save","update","main","create"]   |
| $name       | Route name defined in your routes/web.php                      |
| $params     | Optional parameters you may need to pass the route             |


```php
$this->setRoute('index','ariel.user.index',["test"=>"123"]);
```
##### addColumn($name,$value)
Add new column to .index table

| Parameter   | Description                                                              |
|-------------|--------------------------------------------------------------------------|
| $name       | Column Header Name                                                       |
| $value      | Value to use in row -- you can also use helpers described below in here  |

```php
$this->addColumn('UserName','name');
```
# unfinished !
##### addAction($action,$icon,$caption,$ask = false,$defaultParms = [])
Add new action infront of each rows in .index table

| Parameter       | Description                                                              |
|-----------------|--------------------------------------------------------------------------|
| $action         |  |
| $icon           |  |
| $caption        |  |
| $ask            |  |
| $defaultParms   |  |


```php
$this->addColumn('UserName','name');
```
## Api Builder
