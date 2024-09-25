---
title: Namespaces in WordPress plugin development
summary: In this article I'll point out, what's the reason for using namespaces in our WordPress plugins and what namespaces are. We will learn, that namespaces are important for the upcoming topics in this series
date: 2023-05-03
categories: ["Modern PHP in WP plugins"]
tags: ["oop", "namespaces"]
author: "Marcus Kober"
slug: "using-namespaces-in-wordpress-plugin-development"
lang: en
---

{{< toc >}}

**In this article I'll point out, what's the reason for using namespaces in our WordPress plugins and what namespaces are. We will learn, that namespaces are important for the upcoming topics in this series.**

## The problem with naming constants, functions, and classes in WordPress plugins

[Avoiding name collisions](https://developer.wordpress.org/plugins/plugin-basics/best-practices/#avoid-naming-collisions) is of paramount importance in plugin development. If you use constants, functions, and classes directly in your plugin file, these are registered in the global namespace. Therefore, there is a risk that you will overwrite constants, functions, or classes from other plugins (or from WordPress itself), or vice versa. As a result, your plugin (or other plugins) may not function correctly, or a PHP error may be thrown.

So, let’s assume that there are two plugins installed on our system, each declaring a function called `plugin_init()`:

Plugin file *plugin-01/plugin-01.php*:

```php
<?php
/*
 * Plugin Name: Plugin #1
 */

function plugin_init() {
    // some code
}
```

Plugin file *plugin-02/plugin-02.php*:

```php
<?php
/*
 * Plugin Name: Plugin #2
 */

function plugin_init() {
    // some code
}
```

This will lead to the following error: `PHP Fatal error: Cannot redeclare plugin_init() (previously declared in /../plugin-01/plugin-01.php)`. The reason behind this is that PHP does not allow the declaration of two functions with the same name.

## Using a prefix to solve this problem

We can now come up with a prefix for our plugin, which we will then use consistently throughout our plugin. A prefix is just a string of characters that we put in front of our function and class names, usually followed by an underscore.

Please keep in mind that the prefix must be as unique as possible. If we only use our plugin name, it is possible that another plugin also has that name, which then leads to naming conflicts again. A short combination of your name and the name of your plugin should generally be safe.

Example:

```php
<?php
/*
 * Plugin Name: Prefixed plugin
 */

function mk_pp_plugin_init() {
    // some code
}

function mk_pp_get_data() {
    // some code
}

// ...
```

In this example, we have chosen *mk_pp* as a prefix (a short form of Marcus Kober and an abbreviation for the plugin) and now use this for all our constants and functions. As you can see, this leads to long names and surely doesn’t contribute to clarity and readability. However, such a prefix is relatively safe. But can’t we do it even more elegantly?

## What are namespaces?

[Namespaces](https://www.php.net/manual/en/language.namespaces.rationale.php) offer the ability to encapsulate elements of your code so as not to cause name collisions. Essentially, namespaces are a mechanism for organizing functions, classes, and constants. A namespace defines a unique area in which the names of functions and classes are encapsulated. This makes it possible to use identical names within different namespaces.

You can also imagine namespaces like a folder system. The “subfolders” are separated with backslashes (`/`).

For the root namespace, I recommend using your name or that of your company, or an abbreviation thereof. In my case, that would be `MK`. Then follows the name of your plugin (or an abbreviation thereof): `MK\MyPlugin`.

## We use namespaces

The namespace is defined at the beginning of each PHP file in your plugin. Of course, you should again make sure not to use too generic namespaces like simply Plugin, as this could also lead to a name collision of the namespace.

Example:

```php
<?php
/*
 * Plugin Name: Namespaced plugin
 */

namespace MK\NamespacePlugin;

function plugin_init() {
    // some code
}

function get_data() {
    // some code
}

// ...
```

The [fully qualified name](https://www.php.net/manual/en/language.namespaces.faq.php#language.namespaces.faq.full) of the function `plugin_init()` is now: `MK\NamespacePlugin\plugin_init()`. This resolves the potential name conflict with a possibly existing function `plugin_init()` in another plugin.

## Namespaces in WordPress plugins

Now let’s try to register a function inside a namespace as a callback for a hook:

```php
<?php
/*
 * Plugin Name: Namespaced plugin
 */

namespace MK\NamespacePlugin;

function plugin_init() {
    // some code
}

add_action('init', 'plugin_init');
```

This will lead to a Fatal error:

```
Fatal error: Uncaught TypeError: call_user_func_array(): Argument #1 ($callback) must be a valid callback, function "plugin_init" not found or invalid function name
```

Why does this error message appear? After all, the function `plugin_init()` does exist in our plugin. To understand, let’s look at the file where the error occurs. In the PHP error description, we see below:

```
thrown in /.../wp-includes/class-wp-hook.php on line 308
```

So, the error occurs in the WordPress core file *class-wp-hook.php*, where the core function `apply_filters()` is defined, which calls the callback that we defined in `add_action()`. However, the file *class-wp-hook.php* is outside of the namespace `MK\NamespacePlugin` that we defined, which is why we need to give the function `add_action()` the fully qualified name of the function:

```php
<?php
/*
 * Plugin Name: Namespaced plugin
 */

namespace MK\NamespacePlugin;

function plugin_init() {
    // some code
}

add_action('init', 'MK\NamespacePlugin\plugin_init');
```

So that we don’t have to manually type in the full name everywhere, we can also provide the call with the [magic constant](https://www.php.net/manual/en/language.namespaces.nsconstants.php) `__NAMESPACE__`, which contains the name of the current namespace as a string:

```php
add_action('init', __NAMESPACE__ . 'plugin_init');
```

## Conclusion

Using namespaces is a clean and efficient way to encapsulate our constants, functions, and classes from the global namespace and thus prevent name collisions.

In the next article of this series, we will also see that we need namespaces for autoloading and the folder structure of plugins.

