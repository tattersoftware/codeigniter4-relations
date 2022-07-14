# Tatter\Relations
Entity relationships for CodeIgniter 4

[![](https://github.com/tattersoftware/codeigniter4-relations/workflows/PHPUnit/badge.svg)](https://github.com/tattersoftware/codeigniter4-relations/actions/workflows/phpunit.yml)
[![](https://github.com/tattersoftware/codeigniter4-relations/workflows/PHPStan/badge.svg)](https://github.com/tattersoftware/codeigniter4-relations/actions/workflows/phpstan.yml)
[![](https://github.com/tattersoftware/codeigniter4-relations/workflows/Deptrac/badge.svg)](https://github.com/tattersoftware/codeigniter4-relations/actions/workflows/deptrac.yml)
[![Coverage Status](https://coveralls.io/repos/github/tattersoftware/codeigniter4-relations/badge.svg?branch=develop)](https://coveralls.io/github/tattersoftware/codeigniter4-relations?branch=develop)

## Quick Start

1. Install with Composer: `> composer require tatter/relations`
2. Add the trait to your model: `use \Tatter\Relations\Traits\ModelTrait`
3. Load relations: `$users = $userModel->with('groups')->findAll();`
4. Add the trait to your entity: `use \Tatter\Relations\Traits\EntityTrait`
5. Load relations: `foreach ($user->groups as $group)`

(See also Examples at the bottom)

## Installation

Install easily via Composer to take advantage of CodeIgniter 4's autoloading capabilities
and always be up-to-date:
```shell
    > composer require tatter/relations
```

Or, install manually by downloading the source files and adding the directory to
**app/Config/Autoload.php***.

## Configuration (optional)

The library's default behavior can be altered by extending its config file. Copy
**examples/Relations.php** to **app/Config/** and follow the instructions
in the comments. If no config file is found in **app/Config** the library will use its own.

### Schemas

All the functionality of the library relies on the generated database schema. The schema comes from
[Tatter\Schemas](http://github.com/tattersoftware/codeigniter4-schemas) and can be adjusted
based on your needs (see the **Schemas** config file). If you want to use the auto-generated
schema your database will have follow conventional naming patterns for foreign keys and
pivot/join tables; see [Tatter\Schemas](http://github.com/tattersoftware/codeigniter4-schemas)
for details.

## Usage

Relation loading is handled by traits that are added to their respective elements.

### Eager/Model

**ModelTrait** adds relation loading to your models by extending the default model `find*`
methods and injecting relations into the returned results. Because this happens at the model
level, related items can be loaded ahead of time in batches ("eager loading").

Add the trait to your models:
```php
	use \Tatter\Relations\Traits\ModelTrait
```

Related items can be requested by adding a `$with` property to your model:
```php
	protected $with = 'groups';
	// or
	protected $with = ['groups', 'permissions'];
```

... or by requesting it on-the-fly using the model `with()` method:

```php
$users = $userModel->with('groups')->findAll();
foreach ($users as $userEntity)
{
	echo "User {$user->name} has " . count($user->groups) . " groups.";
...
```

As you can see the related items are added directly to their corresponding object (or array)
returned from the framework's model.

### Lazy/Entity

**EntityTrait** adds relation loading to individual items by extending adding magic `__get()`
and `__call()` methods to check for matching database tables. Because this happens on each
item, related items can be retrieved or updated on-the-fly ("lazy loading").

Add the trait and its necessary properties to your entities:
```php
	use \Tatter\Relations\Traits\EntityTrait
	
	protected $table      = 'users';
	protected $primaryKey = 'id';
```

Related items are available as faux properties:
```php
	$user = $userModel->find(1);
	
	foreach ($user->groups as $group)
	{
		echo $group->name;
	}
```

... and can also be updated directly from the entity:
```php
	$user->addGroup(3);
	
	if ($user->hasGroups([1, 3]))
	{
		echo 'allowed!';
	}
	
	$user->setGroups([]);
```

Available magic method verbs are: `has`, `set`, `add`, and `remove`, and are only applicable
for "manyToMany" relationships.

## Returned items

**Schemas** will attempt to associate your database tables back to their models, and if
successful, **Relations** will use each table's model to find the related items. This keeps
consistent the return types, events, and other aspects of your models. In addition to the
return type, **Relations** will also adjust related items for singleton relationships:
```php
// User hasMany Widgets
$user = $userModel->with('widgets')->find($userId);
echo "User {$user->name} has " . count($user->widgets) . " widgets.";

// ... but a Widget belongsTo one User
$widget = $widgetModel->with('users')->find($widgetId);
echo $widget->name . " belongs to " . $widget->user->name;
```

### Nesting

**ModelTrait** supports nested relation calls, but these can be resource intensive so may
be disabled by changing `$allowNesting` in the config. With nesting enabled, any related
items will also load their related items (but not infinitely):
```php
/* Define your models */
class UserModel
{
	use \Tatter\Relations\Traits\ModelTrait;

	protected $table = 'users';
	protected $with  = 'widgets';
...
	
/* Then in your controller */
$groups = $groupModel->whereIn('id', $groupIds)->with('users')->findAll();

foreach ($groups as $group)
{
	echo "<h1>{$group->name}</h1>";
	
	foreach ($group->users as $user)
	{
		echo "{$user->name} is a {$user->role} with " . count($user->widgets) . " widgets.";
	}
}
```

### Soft Deletes

If your target relations correspond to a CodeIgniter Model that uses [soft deletion](https://codeigniter.com/user_guide/models/model.html#usesoftdeletes)
then you may include the table name in the `array $withDeletedRelations` property to include
soft deleted items. This is particularly helpful for tight relationships, like when an item
`belongsTo` another item that has been soft deleted. `$withDeletedRelations` works on both
Entities and Models.

## Performance

*WARNING*: Be aware that **Relations** relies on a schema generated from the **Schemas**
library. While this process is relatively quick, it will cause a noticeable delay if a page
request initiates the load. The schema will attempt to cache to prevent this delay, but
if your cache is not configured correctly you will likely experience noticeable performance
degradation. The recommended approach is to have a cron job generate your schema regularly
so it never expires and no user will trigger the un-cached load, e.g.:
```shell
php spark schemas
```

See [Tatter\Schemas](http://github.com/tattersoftware/codeigniter4-schemas) for more details.

### Eager or Lazy Loading

You are responsible for your application's performance! These tools are here to help, but
they still allow dumb things.

Eager loading (via **ModelTrait**) can create a huge performance
increase by consolidating what would normally be multiple database calls into one. However,
the related items will take up additional memory and can cause other bottlenecks or script
failures if used indiscriminately.

Lazy loading (via **EntityTrait**) makes it very easy to work with related items only when
they are needed, and the magic functions keep your code clear and concise. However, each entity
issues its own database call and can really start to slow down performance if used over
over.

A good rule of thumb is to use **ModelTrait** to preload relations that will be handled
repeatedly (e.g. in loops) or that represent a very small or static dataset (e.g. a set of
preference strings from 10 available). Use **EntityTrait** to handle individual items, such
as viewing a single user page, or when it is unlikely you will use relations for most of the
items.
