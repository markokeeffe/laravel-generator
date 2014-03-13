laravel-generator
=================

Interface for generating resources (models, views, controllers, seed) from database table based on Jeffry Ways [L4 Generator](https://github.com/JeffreyWay/Laravel-4-Generators) system.

## Prerequisites ##

All prerequisites should be managed by composer. This package uses these packages:

```json
  "require": {
      "way/generators": "dev-master",
      "bllim/datatables": "*",
      "anahkiasen/former": "dev-master",
      "noherczeg/breadcrumb": "dev-master",
      "barryvdh/laravel-ide-helper": "dev-master"
    }
```

## Installation ##

Add the following to the `require` object in your `composer.json`:

```json
  "require": {
    ...
    "markokeeffe/generator": "dev-master"
    ...
  },
```

Update composer:

```bash
$ composer update
```

Add the service provider in `config/app.php`:

```php
'providers' => array(
    ...
    'MOK\Generator\GeneratorServiceProvider',
),
```
## Configuration ##

By default generator will skip these fields from fillable array and form fields:

```php
return array(
  'removable' => array('id', 'created_at', 'updated_at')
);
```

However if you want to change this value, publish the config file to your config directory:

```bash
$ php artisan config:publish markokeeffe/generator
```

You can now change the value in `config/packages/markokeeffe/generator/config.php`:


## Usage ##

Simply use the command `artisan generator2:scaffold` and add a database table as in example:

```bash
$ php artisan generator2:scaffold comments
```

In order to see the DataTable you need to include javascript and jquery (included by default).
Also this script needs to be on every page in order to load basic DataTable:

```js
  $(document).ready(function(){
    $('#datatable').dataTable({
      "bProcessing": true,
      "bServerSide": true,
      "sAjaxSource": ""
    });
  });
```

All variables are usable as in jeffry way generators.
