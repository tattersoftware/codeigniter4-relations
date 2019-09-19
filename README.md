# Tatter\Relations
Entity relationships for CodeIgniter 4

## Quick Start

1. Install with Composer: `> composer require tatter/relations`
2. Extend the model: `class UserModel extends \Tatter\Relations\Model`
3. Load relations: `$users = $userModel->with('groups')->findAll();`

## Installation

Install easily via Composer to take advantage of CodeIgniter 4's autoloading capabilities
and always be up-to-date:
* `> composer require tatter/relations`

Or, install manually by downloading the source files and adding the directory to
`app/Config/Autoload.php`.

## Configuration (optional)

The library's default behavior can be altered by extending its config file. Copy
**bin/Relations.php** to **app/Config/** and follow the instructions
in the comments. If no config file is found in **app/Config** the library will use its own.

### Schemas

All the functionality of the library lies in the specialized model and the generated
database schema. The schema is generated from
[Tatter\Schemas](http://github.com/tattersoftware/codeigniter4-schemas) and can be adjusted
based on your needs (see the **Schemas** config file). If you want to use the auto-generated
schema your database will have follow conventional naming patterns for foreign keys and
pivot/join tables; see [Tatter\Schemas](http://github.com/tattersoftware/codeigniter4-schemas)
for details.

## Usage

In order to take advantage of relationship loading you need your model to extend
`Tatter\Relations\Model`. This model extends the finders from CodeIgniter's core model and
injects related items that you define into the returned rows. Related items can be requested
by adding a `$with` property to your model:
```
	protected $with = 'groups';
	// or
	protected $with = ['groups', 'permissions'];
```

... or by requesting it on-the-fly using the model `with()` method:
```
$users = $userModel->with('groups')->findAll();
foreach ($users as $userEntity)
{
	echo "User {$user->name} has " . count($user->groups) . " groups.";
...
```

As you can see the related items are added directly to their corresponding object or array
returned from the primary model.

## Returned items

**Schemas** will attempt to associate your database tables back to their models, and if
successful, **Relations** will use each table's model to find the related items. This keeps
consistent the return types, events, and other aspects of your models. In addition to the
return type, **Relations** will also adjust related items for singleton relationships:
```
// User hasMany Widgets
$user = $userModel->with('widgets')->find($userId);
echo "User {$user->name} has " . count($user->widgets) . " widgets.";

// ... but a Widget belongsTo one User
$widget = $widgetModel->with('users')->find($widgetId);
echo $widget->name . " belongs to " . $widget->user->name;
```

### Nesting

**Relations** supports nested relation calls, but these can be resource intensive so may
be disabled by changing `$allowNesting` in the config. With nesting enabled, any related
items will alos load their related items:
```
/* Define your models */
class UserModel extends \Tatter\Relations\Model
{
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
