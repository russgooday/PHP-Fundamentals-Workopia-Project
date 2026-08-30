# PSR-4 Autoloader

### configuration

The namespaces are configured with a **namespace prefix** key and a **base directory** value.

```
[
    'Framework\\'   => '/src/Framework/',
    'App\\'         => '/src/App/',
    'App\\Config\\' => '/src/Config/
]
```

### classname

The **autoloader** is passed a *classname* and using a loop, working from right to left the classname is split on a separator into two segments. The left segment is used to find a match in the namespace prefix keys, and the right segment is used as a relative path when a match is found.

Working right to left matters because it checks the **most specific** namespace prefix first. If both `App\` and `App\Config\` are configured, a classname of `App\Config\Routes` needs to match `App\Config\`, not the shorter `App\`.

Here is a breakdown of the process using the above configuration and a *classname* of `App\Http\Controllers\HomeController`

```
Start:        classname = 'App\Http\Controllers\HomeController'

Iteration 1:  split on 'HomeController'
              classname = 'App\Http\Controllers\'    → not in mappings, continue
              relative  = 'HomeController'

Iteration 2:  split on 'Controllers'
              classname = 'App\Http\'                 → not in mappings, continue
              relative  = 'Controllers\HomeController'

Iteration 3:  split on 'Http'
              classname = 'App\'                      → FOUND → use 'src/App/'  ✓ stop
              relative  = 'Http\Controllers\HomeController'
```

When a match is found the complete **filename** can be assembled.

| root directory | base directory | relative path                   | extention |
| -------------- | -------------- | ------------------------------- | --------- |
| www.workopia/  | src/App/       | Http/Controllers/HomeController | .php      |

#### In code

There are numerous way to approach this and I have tried a few including regexes. In this version, starting from the right I use [strrpos](https://www.php.net/manual/en/function.strrpos.php) to match the first occurrence of a slash and *split* on that position. On each iteration the offset is shifted left to next segment. I like this approach because you don't need to mutate the classname, instead you just move a pointer along.

```php
// for demo purposes
class Config {
    public const NAMESPACES = [
        'App\\' => 'src/App/',
        'App\\Config\\' => 'src/special/Config/',
        'Framework\\' => 'src/Framework/'
    ];
}

// helper to split a string on a postion and return a tuple
function str_split_pos(string $str, int $pos): array {
    return [substr($str, 0, $pos), substr($str, $pos)];
}

function loader(string $class): ?string {
    $namespaces = Config::NAMESPACES;
    $len = strlen($class);
    $rt_offset = 0;

    // position of backslash starting from the right
    while ($pos = strrpos($class, '\\', $rt_offset)) {

        // to include the trailing backslash in the prefix
        // we need to add 1 to the position
        [$prefix, $relative] = str_split_pos($class, $pos + 1);

        // check if namespaces has a matching for $prefix
        if ($base_dir = ($namespaces[$prefix]) ?? null) {

            // in practice the filename needs to be checked to see if
            // it exists and then required
            return ".../workopia/" // mock root
                . $base_dir
                . str_replace('\\','/', $relative)
                . ".php";
        }

        // shift the right offset along to the next segment
        // e.g. with abc\cde\fgh : (-5) from h to e -> (-9) from e to c
        $rt_offset = $pos - ($len + 1);
    }

    return 'No match!!';
}
```
And some basic tests
```php
echo loader('App\\Controllers\\Home') . "\n";
// .../workopia/src/App/Controllers/Home.php
echo loader('App\\Config\\Routes') . "\n";
// .../workopia/src/special/Config/Routes.php
echo loader('Framework\\Database') . "\n";
// .../workopia/src/Framework/Database.php
echo loader('Random\\Folder') . "\n";
// No match!!
```



```php
$auto_loader = (new Autoloader())
    ->addNamespace('App\\', 'src/App/')
    ->addNamespace('App\\Config\\', 'src/special/App/Config/')
    ->register();
```
