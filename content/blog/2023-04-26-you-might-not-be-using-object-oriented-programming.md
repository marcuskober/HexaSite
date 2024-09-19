---
title: You might not be using object-oriented programming
summary: What lies behind this somewhat provocative headline? Over time, I have noticed something about certain WordPress plugins. What exactly, I will describe here.
date: 2023-04-26
categories: ["Modern PHP in WP plugins"]
tags: ["oop"]
author: "Marcus Kober"
slug: "you-might-not-be-using-object-oriented-programming"
lang: en
---

{{< toc >}}

**What lies behind this somewhat provocative headline? Over time, I have noticed something about certain WordPress plugins. What exactly, I will describe here.**

First, I must admit, that I did not only notice that with plugins from other developers, but first and foremost with older plugins by myself. When I was developing my first plugins, I proclaimed with the utmost conviction at some point: “I now use object-oriented programming in my plugins!”. Unfortunately, this was not the truth. My code looked something like this:

```php
<?php
/*
 * Plugin Name: Not object oriented
 * Description: I'm not object oriented
 * Author: Marcus Kober
 */

class MK_MyPlugin
{
    public function __construct()
    {
        add_action('init', [$this, 'init']);
        add_action('wp_enqueue_scripts', [$this, 'assets']);
    }

    public function init()
    {
        $results = $this->getResults();
        // Some more code
    }

    public function assets()
    {
        // wp_enqueue...
    }

    private function getResults()
    {
        // Some code
    }

    // More methods
    // ...
}

new MK_MyPlugin();
```

So what did I do? I have packed all functionality of the plugin inside one large class and then instantiated it directly.

## The complete code inside a huge single class does not make it object-oriented programming

If all the code of your plugin resides within a single class, and this class is instantiated at runtime, this is some sort of programming inside a class, but not yet object-oriented programming.

All that is achieved with the code above is merely a better encapsulation of the code so that there is no need for prefixing your functions and variables to avoid name conflicts (more on that in the upcoming article about namespaces). Unfortunately, it’s nothing more like that and certainly not object-**oriented** programming.

## So, what type of programming do we have here?

Next to [object-oriented programming](https://en.wikipedia.org/wiki/Object-oriented_programming) is [procedural programming](https://en.wikipedia.org/wiki/Procedural_programming). In procedural programming the code is organized in steps, to get a clear structure. Code fragments are often organized in functions and every step is represented by a code line. One step is, for example, a variable declaration, a function call, an if-query, and so on.

The code of our little plugin introduced above would look something like that in procedural programming:

```php
<?php
/*
 * Plugin Name: Not object oriented
 * Description: I'm not object oriented
 * Author: Marcus Kober
 */

function mk_myplugin_init() {
    $results = mk_myplugin_getResults();
    // Some more code
}

function mk_myplugin_assets() {
    // wp_enqueue...
}

function mk_myplugin_getResults() {
    // Some code
}

add_action('init', 'mk_myplugin_init');
add_action('wp_enqueue_scripts', 'mk_myplugin_assets');
```

The only difference to the first code example is that the various functions of the plugin were implemented as methods of a single class. Essentially, the problem is solved in the same procedural way in both versions of the code, only the arrangement of the code is different.

In the world of WordPress, you’ll find many such examples of procedural programming. Many plugins and even large parts of the WordPress core are based or were based on procedural programming, and that is absolutely fine. Even though many parts of WordPress are now built as classes, these are obscured in the WordPress API by functions such as `get_posts()`.

In the [first article]({% post_url 2023-04-25-modern-object-oriented-php-in-wordpress-plugin-development %}) of this series, I already wrote: it is not absolutely necessary to use object-oriented programming and modern PHP for every plugin. However, especially for more extensive plugins, object-oriented programming helps enormously in structuring the code (alongside other advantages that we will get to know).
