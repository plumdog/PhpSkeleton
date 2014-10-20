Skeleton Micro-Framework
========================

Sample index.php file:
```php
<?php
if(strpos($_SERVER['REQUEST_URI'], '/static') === 0) {
	return FALSE;
}

require_once("vendor/autoload.php");
use Skeleton\SkeletonHandler;

$routes = array(
	'/' => 'MyApp\Index',
	'/add' => 'MyApp\Add',
	'/edit' => 'MyApp\Edit',
	'/api' => 'MyApp\API');
SkeletonHandler::handle($routes);
```

Where MyApp is a namespace that can be autoloaded.

The classes that you route to (ie, the values in the `$routes` array
above) should extend `SkeletonRequestHandler` and override its
`handle($request)` method. Here, you should use information from the
request, ie via `$request->post()`, `$request->get()` and do things
using `$request->db()` and then return a SkeletonResponse object.

Simple Example
--------------
```php
<?php
namespace MyApp;
use Skeleton\SkeletonRequestHandler as SkeletonRequestHandler;
use Skeleton\SkeletonResponse as SkeletonResponse;

class Index extends SkeletonRequestHandler {
	public function handle($request) {
		$statement = $request->db()->prepare("SELECT * FROM items;");
		$statement->execute();
		return new SkeletonResponse($request, array('items' => $statement), 'index');
	}
}
```

More Complex Example
--------------------
```php
<?php
namespace MyApp;
use Skeleton\SkeletonRequestHandler as SkeletonRequestHandler;
use Skeleton\SkeletonResponse as SkeletonResponse;

class Edit extends SkeletonRequestHandler {
	public function handle($request) {
		$id = $request->get('id');

		$statement = $request->db()->prepare("SELECT * FROM items WHERE id = :id;");
		$statement->execute(array('id' => $id));
		$item = $statement->fetch();

		if($item === FALSE) {
			return SkeletonResponse::error404($request, 'Item not Found');
		}

		$form = array('name' => '');

		if($request->post()) {
			$statement = $request->db()->prepare('UPDATE items SET name = :name WHERE id = :id;');
			$statement->execute(
				array('id' => $id,
				      'name' => $request->post('name')));
			$request->redirect();
		} else {
			$form['name'] = $item['name'];
		}

		return new SkeletonResponse($request, array('form' => $form), 'form');
	}
}
```

Views
=====

By default, we look in a directory called `views` in the webroot for
`.html` files. You can change where we look by setting `$VIEW_DIR` in
the config. For example:

```php
new SkeletonResponse($request, array('items' => $statement), 'index');
```

Will look for file called `index.html` in the views directory and try
to render it. When rendering, we will have access to a variable called
`$items` which will be set to whatever $statement is.

We only have one-level of template inheritance. You should create a view called `template.html` which should contain the line:

```php
<?php include $SKELETON_VIEW; ?>
```

This is where the inner view will appear. Eg:

```php
<!DOCTYPE html>
  <head>
    <title><?=htmlspecialchars($title)?></title>
    <link rel="stylesheet" type="text/css" href="static/style.css">
  </head>
  <body>
    <h1><?=htmlspecialchars($title)?></h1>
    <?php include $SKELETON_VIEW; ?>
  </body>
</html>
```

Non-HTML Output
---------------

If you need to output something raw (eg JSON) then omit the 3rd
argument when creating the `SkeletonResponse` object. Whatever you set
as the 2nd argument will just be echo-ed out. Eg:

```php
<?php
namespace MyApp;
use Skeleton\SkeletonRequestHandler as SkeletonRequestHandler;
use Skeleton\SkeletonResponse as SkeletonResponse;

class API extends SkeletonRequestHandler {
	public function handle($request) {
		$statement = $request->db()->prepare("SELECT * FROM items;");
		$statement->execute();
		return new SkeletonResponse($request, json_encode($statement->fetchAll()));
	}
}
```

Database
========

Uses PDO, and currently assumes you're using MySQL, in which case
you'll need the driver. Assuming Ubuntu:
```bash
sudo apt-get install php5-mysql
```

This also assumes that you have a file in your web-root called
`config.php` that looks a bit like:

```php
<?php
$config = array(
	'DBNAME' => 'mydb',
	'DBUSER' => 'myuser',
	'DBPASS' => 'password');
```
