## Laravel CRUD Package

[![License](https://poser.pugx.org/laravel/framework/license.svg)](https://packagist.org/packages/laravel/framework)

## Official Documentation

Documentation for the package can be found on the [LaraCrud website](http://laracrud.org/docs).

## Documentation

Install Package using composer:

```javascript
composer require aluna/laracrud
```

Add to providers array in the config/app.php file the next entry:

```php
LaraCrud\LaraCrudServiceProvider::class,
```

STEP 1:

Create a new model, edit recently created module and include:

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use LaraCrud\LaraCrudModel;

class ExampleModel extends Model
{

	/**
	 * Model columns to be hidden on views
	 * @var array
	 */
	protected $hidden   = ['created_at', 'updated_at', 'deleted_at'];

	/**
	 * Laravel Mass Assigment protection
	 * @var array
	 */
	protected $fillable =  ['column1', 'column2'];

	/**
	 * GroceryCrud Model trait
	 */
	use LaraCrudModel;
	use SoftDeletes;

}
```

STEP 2:

The controller must look like this

```php
<?php

namespace App\Http\Controllers;

use App\ExampleModel;
use LaraCrud\LaraCrudController;

class ExampleController extends Controller
{

	use LaraCrudController;

	protected $model;

	protected $route = '/example';

	protected $crudName = 'Example';

	public function __construct(ExampleModel $example)
	{
		$this->model = $example;
	}

}
```



## Contributing

Thank you for considering contributing to the LaraCrud Package!

### License

The LaraCrud package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
