---
title: Autoloading, coding standards and file structure in WordPress plugin development
summary: In the last article, I explained the use of namespaces, which we need today to establish autoloading, which simultaneously defines our file and folder structure.
date: 2023-05-09
categories: ["Modern PHP in WP plugins"]
tags: ["autoloading", "composer", "psr"]
author: "Marcus Kober"
slug: "autoloading-coding-standards-and-file-structure-in-wordpress-plugin-development"
lang: en
---

{{< toc >}}

**In the last article, I explained the use of namespaces, which we need today to establish autoloading, which simultaneously defines our file and folder structure.**

## Declare classes in their own PHP files

To keep our code clear, we distribute the code sensibly across several PHP files. Each class should be declared in its own file. This file must then be loaded into the main code (e.g. via `require_once()`) before the class can be instantiated.

Let’s assume the following folder structure of an example plugin:

```
my-plugin
├── class-01.php
├── class-02.php
└── my-plugin.php
Content class-01.php:
```

```php
<?php
class class01
{
    public function __construct()
    {
        echo 'class01 instantiated.';
    }
}
```

If we now want to instantiate the class01 in the plugin file *my-plugin.php*, we must first include the file and then we can instantiate the class:

```php
<?php
/**
 * Plugin Name: MyPlugin
 */

require_once __DIR__ . '/class-01.php';

$klasse01 = new class01(
```

If we also need `class02` in this script, we also have to include the corresponding file first and then we can instantiate the class:

```php
<?php
/**
 * Plugin Name: MyPlugin
 */

require_once __DIR__ . '/class-01.php';
require_once __DIR__ . '/class-02.php';

$klasse01 = new class01();
$klasse02 = new class02();
```

This can quickly become confusing even with a small number of classes and lead to long lists of `require_once()`.

Now a requirement for our plugin changes and we have to introduce a service that must be used by `class02`, for example.

We add our service as a new class in the *service-01.php* file:

```
my-plugin
├── class-01.php
├── class-02.php
├── my-plugin.php
└── service-01.php
```

```php
<?php
class service01
{
    public function __construct()
    {
        echo 'service01 instantiated.';
    }
}
```

And change `class02`:

```php
<?php
class class01
{
    protected service01 $service01;

    public function __construct(service01 $service01)
    {
        $this->service01 = $service01;

        echo 'class02 and service01 instantiated.';
    }
}
```

Now we have to include the file *service-01.png* in the main file of the plugin:

```php
<?php
/**
* Plugin Name: MyPlugin
*/

require_once __DIR__ . '/class-01.php';
require_once __DIR__ . '/class-02.php';
require_once __DIR__ . '/service-01.php';

$service01 = new service01();

$klasse01 = new class01();
$klasse02 = new class02($service01);
```

And at this point, at the latest, it becomes confusing. First, there is a long list of `require_once()`, and with each new class we need, this list becomes even longer. Moreover, our folder structure no longer looks very clear. In the next step, an inc folder could be introduced, containing all the files we want to include using `require_once()`:

```
my-plugin
├── inc
│   ├── class-01.php
│   ├── class-02.php
│   └── servie-01.php
└── my-plugin.php
```

In principle, there is nothing wrong with such a setup at this stage. However, as the plugin becomes more extensive and we have different classes like models, services, and classes with hooks, we also need a folder structure that organizes all the new files in a sensible and clear manner.

In addition, we want to move away from the very long list of `require_once()` calls.

## Preparing for Autoloading and code standards

Autoloading refers to the automatic loading of required files, and to reliably use this, we need to equip our existing code with namespaces and follow certain coding standards. Coding standards affect both the code itself and the folder structure we will use. These standards help bring some order to the chaos that PHP generally allows. There are standards for indenting code (the eternal war: tabs or spaces?), naming variables, functions, classes, and methods, etc.

### Why are standards important?

Coding standards lead to more clarity, and they generally provide a schema for how something should look so that developers don’t have to worry about it, and so that everyone can quickly and easily find their way into unfamiliar code, especially in a team.

### Coding standards

Because it would be too easy otherwise, there are different standards. 😅 Coding standards are defined by different sources. There are standards set by WordPress itself, standards set by the PHP Framework Interoperability Group (PHP-FIG), and individual companies and agencies can also define their own standards, of course.

**At this point, we are primarily interested in two definitions:**

- The standards that [WordPress itself defines](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/), with the [standards for PHP](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/) being important in this article.
- The [PHP Standard Recommendation](https://www.php-fig.org/psr/), or PSR for short, from the aforementioned [PHP-FIG](https://www.php-fig.org/).

**So which standards should we follow?**

The answer you won’t like: *it depends*.

It depends on your personal taste, and there is no right or wrong here. You can also define your own standards (which I do not recommend). The important thing is to actually stick to one thing.

**Which standards will we follow in this series?**

I don’t want to dictate which standards you should follow, but I stick to the PSR, as they are also followed by other PHP frameworks like [Symfony](https://symfony.com/) and [Laravel](https://laravel.com/). If you rely on PSR, you are not trapped in the WordPress universe and can seamlessly work on Symfony or Laravel projects as well. Opinions differ here, though; there are numerous advocates of the WordPress standard when programming for WordPress. If you ever want to become a core developer, you should definitely be aware that different coding standards exist.

Please take the time, if you are not familiar with PSR, to review the following PSRs:

- [PSR-1](https://www.php-fig.org/psr/psr-1/): Basic Coding Standard
- [PSR-2](https://www.php-fig.org/psr/psr-2/): Coding Style Guide
- [PSR-12](https://www.php-fig.org/psr/psr-12/): Extended Coding Style

**IMPORTANT:**

PHP files that contain either pure PHP code or end with a PHP code block must not contain a closing PHP tag (`?>`) (see PSR-2, 2.2 Files)! This can especially lead to a “Headers already sent” error in plugin development if whitespace or a blank line follows the `?>`.

### Standard for folder structure

Since we have to follow the namespaces in folder naming for autoloading, I don’t want to define fixed standards for the folder structure. This is ultimately more a matter of taste, especially in WordPress plugin development. Frameworks, however, are much stricter.

For later (and also for PSR-4 autoloading, see below), it is important that all PHP files, except for the main plugin file, are located within a src folder. This becomes especially important when we get to unit tests, as the actual source code is in src and all associated tests are in tests. But also for autoloading, as we will see shortly, this is important.

### Our previous example code is now standardized

To establish autoloading well, we now fully align our code with the standards and introduce namespaces. We make the following changes:

- Class names are written in [StudlyCaps](https://www.php-fig.org/psr/psr-1/#3-namespace-and-class-names), so `Class01` instead of `class01`
- The file names are identical to the class names, so *Class01.php* instead of *class-01.php*
- All files in src have namespaces, with the base namespace `MK\MyPlugin` following the `Manufacturer\Plugin` pattern, and our classes 1 and 2 are in the `Main` sub-namespace, while the service is in `Service`.

### Folder structure

We now establish the folder structure that follows the naming of the namespaces:

```
my-plugin
├── src
│   ├── Main
│   │   ├── Class01.php
│   │   └── Class02.php
│   └── Service
│       └── Service01.php
└── my-plugin.php
```

### The files

*src/Main/Class01.php*

```php
<?php

namespace MK\MyPlugin\Main;

class Class01
{
    public function __construct()
    {
        echo 'Class01 instantiated.';
    }
}
```

*src/Main/Class02.php*

```php
<?php

namespace MK\MyPlugin\Main;

use MK\MyPlugin\Service\Service01;

class Class02
{
    protected Service01 $service01;

    public function __construct(Service01 $service01)
    {
        $this->$service01 = $service01;

        echo 'Class02 instantiated.';
    }
}
```

*src/Service/Service01.php*

```php
<?php

namespace MK\MyPlugin\Main;

class Service01
{
    public function __construct()
    {
        echo 'Service01 instantiated.';
    }
}
```

*my-plugin.php*

```php
<?php
/**
* Plugin Name: MyPlugin
*/

require_once __DIR__ . '/src/Main/Class01.php';
require_once __DIR__ . '/src/Main/Class02.php';
require_once __DIR__ . '/src/Service/Service01.php';

use MK\MyPlugin\Main\Class01;
use MK\MyPlugin\Main\Class02;
use MK\MyPlugin\Main\Service01;

$service01 = new Service01();

$class01 = new Class01();
$class02 = new Class02($service01);
```

## Using autoloading in WordPress plugins

Basically, there are two ways to use autoloading:

- Self-configured autoloading via `spl_autoload_register()`
- PSR-4 autoloading via Composer

I generally use autoloading via Composer and highly recommend it. However, to cover the most important things here, I also deal with `spl_autoload_register()`.

### Autoloading with spl_autoload_register()

With the PHP function `spl_autoload_register()`, we can implement our own autoloading. To do this, let’s look at the [function call](https://www.php.net/manual/function.spl-autoload-register.php):

```php
spl_autoload_register(?callable $callback = null, bool $throw = true, bool $prepend = false): bool
```

So, the function expects at least one callback function, and this function is given the name of the class to be loaded:

```php
callback(string $class): void
```

We can now comment out the `require_once()` calls in our main file my-plugin.php and insert `spl_autoload_register()` for testing, using an [anonymous function](https://www.php.net/manual/en/functions.anonymous.php) as the callback:

```php
<?php
/**
* Plugin Name: MyPlugin
*/

// require_once __DIR__ . '/src/Main/Class01.php';
// require_once __DIR__ . '/src/Main/Class02.php';
// require_once __DIR__ . '/src/Service/Service01.php';

use MK\MyPlugin\Main\Class01;
use MK\MyPlugin\Main\Class02;
use MK\MyPlugin\Main\Service01;

spl_autoload_register(function(string $className) {
    var_dump($className);
});

$service01 = new Service01();

$class01 = new Class01();
$class02 = new Class02($service01);
```

By disabling the `require_once()` calls and integrating `spl_autoload_register()`, PHP tries to get the appropriate PHP file via the autoloader when calling `new Service01()`. To do this, PHP passes the fully qualified class name to the callback function that we passed to `spl_autoload_register()`.

All our callback does at the moment is to output the class name using `var_dump()`. So our above code generates the following message:

```
string(29) "MK\MyPlugin\Service\Service01"
Fatal error: Uncaught Error: Class "MK\MyPlugin\Service\Service01" not found in 
[(...)/wp-content/plugins]/autoload-plugin-02/autoload-plugin-01.php:18 
Stack trace: #0 (...)/wp-settings.php(453): include_once() #1 (...)/wp-config.php(93): 
require_once '...' #2 (...)/wp-load.php(50): require_once '...' #3 (...)/wp-admin/admin.php(34): 
require_once '...' #4 (...)/wp-admin/plugins.php(10): require_once '...' #5 {main} 
thrown in (...)/wp-content/plugins/autoload-plugin-02/autoload-plugin-01.php on line 18
```

In this code, I have replaced my local paths with (…) and added manual line breaks to avoid making the message too long.

First, the output of `var_dump()` appears:

```php
string(29) "MK\MyPlugin\Service\Service01"
```

This is the fully qualified name of the class that is instantiated first.

Then follows the Fatal Error, as the class could not be found. We now need to ensure in our callback that the appropriate file is loaded.

Since we have made it very easy for ourselves with the namespaces and the corresponding folder structure, we can get the filename with a few simple adjustments to the fully qualified class name:

```php
<?php
/**
* Plugin Name: MyPlugin
*/

// require_once __DIR__ . '/src/Main/Class01.php';
// require_once __DIR__ . '/src/Main/Class02.php';
// require_once __DIR__ . '/src/Service/Service01.php';

use MK\MyPlugin\Main\Class01;
use MK\MyPlugin\Main\Class02;
use MK\MyPlugin\Main\Service01;

spl_autoload_register(function(string $className) {
    // MKMyPlugin vom Klassennamen durch den Pfad zu src ersetzen:
    $className = str_replace('MK\\MyPlugin\\', __DIR__ . '/src/', $className);

    // Die restlichen Backslashes durch Verzeichnis-Trenner (Slashes) ersetzen und .php anhängen
    $classFile =  str_replace('\\', '/', $className) . '.php';

    // Klassen-Datei laden
    require_once $classFile;
});

$service01 = new Service01();

$class01 = new Class01();
$class02 = new Class02($service01);
```

In the example of the `Service01` class, the class name is `MK\MyPlugin\Service\Service01`. The part `MK\MyPlugin` can be seen as an alias to the src directory of our plugin.

So, to get the path for the class file from this, in the first step we only need to replace the part `MK\MyPlugin` with `__DIR__ . '/src/`. Afterward, we exchange all backslashes (`\`) for the directory separator (slash, `/`) and add the extension `.php`.

So, `MK\MyPlugin\Service\Service01` becomes `(…)/wp-content/plugins/my-plugin/src/Service/Service01.php`, which exactly matches our file. The part (…) corresponds to your local path to the WordPress installation.

With the above code, our plugin should now automatically reload all class files. So now we just need to remove our redundant, commented out code:

```php
<?php
/**
* Plugin Name: MyPlugin
*/

use MK\MyPlugin\Main\Class01;
use MK\MyPlugin\Main\Class02;
use MK\MyPlugin\Main\Service01;

spl_autoload_register(function(string $className) {
    // Replace MK\MyPlugin in the class name with the path to src:
    $className = str_replace('MK\\MyPlugin\\', __DIR__ . '/src/', $className);

    // Replace the remaining backslashes with directory separators (slashes) and append .php
    $classFile =  str_replace('\\', '/', $className) . '.php';

    // Load class file
    require_once $classFile;
});

$service01 = new Service01();

$class01 = new Class01();
$class02 = new Class02($service01);
```

However, if we now implement this code and reload the page in our WordPress installation, we will be confronted with a new error:

```
Warning: require_once(WP_Site_Health.php): Failed to open stream: No such file or directory 
in (...)/wp-content/plugins/autoload-plugin-02/autoload-plugin-01.php on line 27

Fatal error: Uncaught Error: Failed opening required 'WP_Site_Health.php' (include_path='.:/usr/share/php:/www/wp-content/pear') 
in (...)/wp-content/plugins/autoload-plugin-02/autoload-plugin-01.php:27 
Stack trace: #0 [internal function]: {closure}('WP_Site_Health') 
#1 (...)/wp-settings.php(604): class_exists('WP_Site_Health') #2 (...)/wp-config.php(93): 
require_once('...') #3 (...)/wp-load.php(50): require_once('...') 
#4 (...)/wp-admin/admin.php(34): require_once('...') #5 (...)/wp-admin/plugins.php(10): 
require_once('...') #6 {main} thrown in (...)/wp-content/plugins/autoload-plugin-02/autoload-plugin-01.php on line 27
```

**What’s happening here?**

PHP tries to load the WordPress core class WP_Site_Health, from which our callback code makes the file WP_Site_Health.php, and of course, this cannot be found.

The autoloader callback that we register with `spl_autoload_register()` is declared in the global namespace. So, we need to ensure that we only process class names that are actually present in our namespace, as PHP uses the callback for every loaded class that is loaded after registration. This includes WordPress core classes.

To ensure that only class names in our namespace are handled in our autoloader, we add the following check:

```php
<?php
/**
* Plugin Name: MyPlugin
*/

use MK\MyPlugin\Main\Class01;
use MK\MyPlugin\Main\Class02;
use MK\MyPlugin\Main\Service01;

spl_autoload_register(function(string $className) {
    if (false === strpos($className, 'MK\\MyPlugin')) {
        return;
    }

    // Replace MK\MyPlugin in the class name with the path to src:
    $className = str_replace('MK\\MyPlugin\\', __DIR__ . '/src/', $className);

    // Replace the remaining backslashes with directory separators 
    $classFile =  str_replace('\\', '/', $className) . '.php';

    // Load class file
    require_once $classFile;
});

$service01 = new Service01();

$class01 = new Class01();
$class02 = new Class02($service01);
```

With `if (false === strpos($className, 'MK\MyPlugin'))`, we check if the beginning of our namespace is present in the class name. If not (`strpos` returns `false`), we terminate the callback with `return`.

Now we have a working autoloader that only handles the class names of our plugin.

## Autoloading with Composer

The manual transformation of the class name in the callback of `spl_autoload_register()` and the exception handling of class names outside the namespace of the plugin are both cumbersome and prone to errors.

In terms of code reusability, this version of autoloading doesn’t score well, as we always have to manually adjust the namespace in at least two places.

It would be much nicer if we could use an established solution here. That’s where [Composer](https://getcomposer.org/) comes in. Composer is a cross-platform dependency management tool for PHP that helps developers easily manage and automatically download required libraries and packages for their projects. At the same time, Composer also offers a [PSR-4](https://www.php-fig.org/psr/psr-4/)-based autoloading that we want to use for our plugin from now on.

### Setting up Composer

To use Composer in the plugin, we first need to set up Composer. Composer must already be installed on your device; you can follow [this tutorial](https://getcomposer.org/doc/00-intro.md) to install it.

Once Composer is installed, please open a terminal (I recommend [Warp](https://www.warp.dev/) if you’re on a Mac, or [hyper.js](https://hyper.is/#installation) otherwise) and switch to your plugin directory.

Then enter the following command: `composer init`. The Composer config generator will guide you through the setup. In most cases, you can use the default settings.

Since we don’t want to define any dependencies yet, you can answer the questions `“Would you like to define your dependencies (require) interactively”` and `“Would you like to define your dev dependencies (require-dev) interactively”` with no.

Then comes the question we need:

`Add PSR-4 autoload mapping? Maps namespace "Marcuskober\MyPlugin" to the entered relative path. [src/, n to skip]:`

Here, Composer automatically generates a namespace from the initially specified package name, which by default consists of the name you chose when setting up Composer and the directory name. We confirm the selection here with enter to choose the name and the suggested src directory.

The setup must then be confirmed with a yes.

Composer now creates a vendor directory for your plugin and the config file *composer.json*. The vendor directory already contains the files that Composer needs for autoloading, and packages will be placed in this directory later if you install them via Composer.

My config file now looks like this:

```json
{
    "name": "marcuskober/my-plugin",
    "autoload": {
        "psr-4": {
            "Marcuskober\\MyPlugin\\": "src/"
        }
    },
    "authors": [
        {
            "name": "Marcus Kober",
            "email": "marcus.kober@gmail.com"
        }
    ]
}
```

In my case, I need to correct the namespace under `"psr-4”` so that it matches our chosen namespace. Depending on your choice of name, directory, and namespaces, you may also need to do this.

So, we’ll change this (from `Marcuskober` to `MK`) accordingly:

```json
"psr-4": {
    "MK\\MyPlugin\\": "src/"
}
```

After changing the namespace in composer.json, we need to inform Composer to adjust its autoload files accordingly. To do this, we enter the following command in the terminal: `composer dump-autoload`. If Composer returns `Generated autoload files` in the terminal, we have correctly set up Composer.

## Using composer autoload in our plugin

To use Composer autoloading in our plugin, all we need to do is remove the `require_once()` calls and load the file *vendor/autoload.php*:

```php
<?php
/**
* Plugin Name: MyPlugin
*/

use MK\MyPlugin\Main\Class01;
use MK\MyPlugin\Main\Class02;
use MK\MyPlugin\Main\Service01;

require __DIR__ . '/vendor/autoload.php';

$service01 = new Service01();

$class01 = new Class01();
$class02 = new Class02($service01);
```

And simply by adding the line `require __DIR__ . '/vendor/autoload.php';`, the autoloading with Composer works directly out of the box!

## Conclusion and Outlook

As you can see, autoloading with Composer is not rocket science and, unlike `spl_autoload_register()`, is quickly and easily implemented.

Once your plugins have reached a certain size, I recommend using autoloading with Composer. Even though you can freely adjust the settings, autoloading still forces you to use clean class naming, a logical folder structure, and the use of a single src directory for your PHP files. And believe me, the constraint is purely positive at this point.

In the upcoming articles in this series, we will delve deeper into the development and structuring of complex plugins, and the code will become more tangible as we will see usable code after these rather theoretical first articles!

In the next article, we will focus on hooks and their registration from your plugin.
