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
	protected $hidden   = ['created_at', 'updated_at'];

	/**
	 * Laravel Mass Assigment protection
	 * @var array
	 */
	protected $fillable =  ['column1', 'column2'];

	/**
	 * LaraCrud Model trait
	 */
	use LaraCrudModel;

}
```

STEP 2:

The controller must look like this

```php
<?php

namespace App\Http\Controllers;

use App\ExampleModel;
use Illuminate\Http\Request;
use LaraCrud\LaraCrudController;

class ExampleController extends Controller
{
	/**
	 * LaraCrud Controller Trait
	 */
	use LaraCrudController;
	
	/**
	 * This variable will store the Model related to this controller.
	 * @var Model
	 */
	protected $model;
	
	/**
	 * This is the base route that will be used with REST methods
	 * @type String
	 */
	protected $route = '/example';

	/**
	 * Name that will be displayed on form headers, session messages, pop up messages, etc.
	 * @type String
	 */
	protected $crudName = 'Example';

	/**
	 * If you want to overwrite the default views of the package
	 * @type String
	 */
	protected $views = 'vendor.laracrud';

	/**
     * The attributes that are available in index view.
     *
     * @var array
     */
    protected $displayable = [
        'column1', 'column2'
    ];
		
	/**
	 * Dispatch events on resource events
	 *
	 * @type array
	 */
    protected $events = [
    	'store' => [
			'class'  => 'App\Events\ExampleEvent',
			'params' => ['result', 'request:password']
    	]
    ];
	
	/**
	 * Let IoC Container to resolve Model and assing it to variable.
	 *
	 * @param  Model ExampleModel $example
	 */
	public function __construct(ExampleModel $example)
	{
		$this->model = $example;
	}

	/**
	 * Method called when post resource has been overwritten
	 * eg: encrypt password before storing
	 *
	 * @param  Illuminate\Http\Request Request $request
	 * @return Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
	 */
	public function modifyRequest(Request $request)
    {
    	$request->merge([
			'password'        => bcrypt($request->password)
    	]);

        return $this->store($request);
    }

}
```

STEP 3:

The routes will be like this:

```php
// Default Laravel Resource
Route::resource('/example', 'ExampleController');

// Overwrite resource REST method to modify request then calling REST method
Route::post('/example', 'ExampleController@modifyRequest');
```

Extra:

If you want to overwrite or modify the default views, the fist thing is to publish the views into the resources folder:

```shell
php artisan vendor:publish
```

The views will be available in resources/vendor/laracrud, feel free to edit them as you please.

## Contributing

Thank you for considering contributing to the LaraCrud Package!

### License

The LaraCrud package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
