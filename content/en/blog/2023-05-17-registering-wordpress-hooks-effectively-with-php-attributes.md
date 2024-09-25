---
title: 'How to register WordPress hooks with PHP attributes: A step-by-step guide'
summary: 'In this article, I will discuss how and why WordPress hooks are used in plugins, and how we will use PHP attributes for registering hooks in classes effectively and clearly.'
date: 2023-05-17
categories: ["Modern PHP in WP plugins"]
tags: ["hooks"]
author: "Marcus Kober"
slug: "registering-wordpress-hooks-effectively-with-php-attributes"
lang: en
---

{{< toc >}}

**In this article, I will discuss how and why WordPress hooks are used in plugins, and how we will use PHP attributes for registering hooks in classes effectively and clearly.**

## What are hooks and how do they work?

Since I assume a certain level of knowledge about WordPress plugin development for this article series, there is a good chance you are already familiar with hooks. If not, you should [learn about hooks](https://developer.wordpress.org/plugins/hooks/) now. However, I still want to provide a brief overview as an introduction.

## A brief introduction to hooks

Hooks are the most important tool for plugin developers. With hooks, code can be executed at specific predefined points, and data can be modified. Hooks are somewhat similar to events that are fired at certain points in the code, and they are what allow plugins to extend and modify WordPress in the first place!

**Please NEVER use methods other than hooks if you want to create a clean and secure plugin!** Other methods here refer to modifying core files or any other types of hacks.

**There are two types of hooks:**

- **Filters**, which allow you to modify data
- **Actions**, which allow you to execute code at specific points

Hooks consist of a hook name and a callback function that can be attached to the hook.

The callback functions of filters always have a return statement, while the callbacks of actions usually do not require one.

### Filters

Let’s briefly take a look at the function `apply_filters()` ([documentation](https://developer.wordpress.org/reference/functions/apply_filters/)). This function allows us to execute the callbacks that have been added to a filter name. Any number of callbacks can be added to a filter name.

Suppose you want to allow another plugin to modify your plugin’s data. Let’s say you have developed a book management system as a WordPress plugin, and you have decided that the book management system should only allow science fiction books and horror thrillers. However, you want other developers to be able to create their own plugins that extend your book management system.

In your plugin, you use a method called isGenreValid() to check if the book belongs to the correct genre. This could look like this:

```php
public function isGenreValid(string $genre): bool
{
    $genres = [
        'Science Fiction',
        'Horror thriller'
    ];

    return in_array($genre, $genres);
}
```

With that, both genres are hard-coded inside the function and can’t be changed from outside. To allow another plugin to modify your genre list, you can work with `apply_filters()`:

```php
public function isGenreValid(string $genre): bool
{
    $genres = [
        'Science Fiction',
        'Horror thriller'
    ];

    $genres = apply_filters('myplugin_book_genres', $genres);

    return in_array($genre, $genres);
}
```

Now, another plugin (or even your own code, as we’ll see later) can hook into the `myplugin_book_genres` filter and extend or completely replace the list. If there is no callback for this filter, the original list will be returned, which you specify as the second argument in `apply_filters()`.

The other plugin can then register a callback using `add_filter()` that modifies the genre array. This could look like this, using an anonymous function, for example:

```php
add_filter('myplugin_book_genres', function(array $genres) {
    $genres[] = 'Drama';

    return $genres;
});
```

Now, if the genre drama is passed to the `isGenreValid()` method, it will return `true`!

Let’s say, in your book management plugin, it’s possible to create virtual bookshelves. Perhaps it’s important for the code hooking into the filter to know which virtual bookshelf is currently selected. The ID of the bookshelf is stored in the class variable `$shelfId`.

You can add any number of additional arguments to the `apply_filters()` function to provide more information to the callback that hooks into the hook. This is where we can pass the ID of the bookshelf:

```php
$genres = apply_filters('myplugin_book_genres', $genres, $this->shelfId);
```

In the callback function of this hook, we are now able to modify the genre list depending on the shelf ID:

```php
add_filter('myplugin_book_genres', function(array $genres, int $shelfId) {
    switch ($shelfId) {
        case 1:
            $genres[] = 'Drama';
            break;
        case 2:
            $genres[] = 'Comedy';
            break;
    }

    return $genres;
}, 10, 2);
```

There are added two additional arguments to `add_filter()`: `10` and `2`. `10` is the priority of the callback in the list of callbacks registered to the hook, and `2` is the number of arguments passed to the callback. The number of passed arguments has to be `2` here, because we need to get `$genres` and `$shelfId`.

The priority specifies, in which order the callbacks get called if multiple callbacks are registered for a hook. In case you want your code to run earlier than the other callbacks, you have to choose values lower than the default value of 10. Should the code run later, choose values greater than 10. Callbacks registered with the same priority are called in the order they were registered.

Let’s look at the syntax of `add_filter()`:

```php
add_filter( string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1 ): true
```

As you can see, the default value for `$priority` is `10`, and the default for `$accepted_args` `1`.

### Actions

While filters allow data to be modified in specific ways with their callbacks returning the corresponding value, actions interrupt the flow of the code to allow for other additional (external) code to be executed.

The function to run an action is named `do_action()`. Let’s compare it to its counterpart `apply_filters()`:

```php
apply_filters( string $hook_name, mixed $value, mixed $args ): mixed
```

```php
do_action( string $hook_name, mixed $arg )

```

As we can see, `do_action()` lacks the `$value` argument, which contains the value that gets filtered, and there’s no return value. However, like its filter counterpart, `do_action()` can also be given required arguments.

The callback, that gets registered via `add_action()`, runs immediately where `do_action()` is called.

The function `add_action()` has the same syntax as `add_filter()`:

```php
add_action( string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1 ): true
```

Let’s assume, you want to load a specific CSS file in frontend. In this case you have to use the action hook `wp_enqueue_scripts`:

```php
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('myplugin-style', plugins_url('assets/dist/css/my-plugin.css'));
});
```

This ensures that the `wp_enqueue_style()` function is called exactly where it is needed for working properly!

### Actions are actually filters

Actions are essentially a special case of filters. This becomes clearer, when we take a closer look at the source code of the `add_action()` function:

```php
function add_action( $hook_name, $callback, $priority = 10, $accepted_args = 1 ) {
    return add_filter( $hook_name, $callback, $priority, $accepted_args );
}
```

Behind the scenes, `add_action()` simply calls `add_filter()`, passing all arguments – we will use this fact to our advantage below!

**But please don’t use `add_filter()` only**

Nevertheless, you should not simply start using only `add_filter()` from now on – after all, there is a significant difference between actions and filters, and it should always be clear from the code whether a callback is being assigned to an action or a filter.

## How do I find out which hooks exist?

As previously mentioned, hooks are essential for WordPress plugin development. The WordPress core has a wealth of hooks to modify almost every aspect of WordPress. Additionally, many plugins leverage hooks, allowing other developers to change the flow and data.

There are basically four different ways to find out which hooks exist. Each method has different advantages and disadvantages and use cases.

### 1. Google it:

We can search for hooks on Google. This is useful when we wonder if there is a hook for the problem we want to solve. A Google query could look like this: _“wordpress hook for adding a class to the body tag”_. In this way, the desired hook can usually be found quickly and easily.


![Google search result: wordpress hook for adding a class to the body tag](/assets/img/artikel-05-hooks-google.png)

### 2. ChatGPT

We can ask ChatGPT:


![ChatGPT result](/assets/img/artikel-05-hooks-chatgpt.png)
_Version: ChatGPT 4_

Here, we notice how extensively ChatGPT responds, not only providing a code example but also pointing out that the theme must support adding a CSS class to the body tag by using the function `body_class()`.

As always, caution is advised with ChatGPT: firstly, the tool’s knowledge base currently ends in September 2021 (so it cannot know any recent changes), and secondly, it is always better to check and test the provided results!

Nevertheless, this type of hook research is superior to a Google search.

### 3. Documentation and hook directories

1. WordPress itself has a list of hooks, and the hooks themselves have their own subpages explaining their usage: [Hook Reference](https://developer.wordpress.org/reference/hooks/)
2. Plugins that use hooks often provide a corresponding directory as well. For example, [WooCommerce](https://woocommerce.github.io/code-reference/hooks/hooks.html)
3. There used to be a service called hookr.io, a website that listed Core and Plugin hooks. However, I just discovered that this site no longer exists. [This is what it used to look like.](https://web.archive.org/web/20200328143351/http://hookr.io/)

Documentations are often easy to search and provide a good overview of the use of specific hooks. However, they are less suitable for searching for the right hook to solve a particular problem.

### 4. Searching in the source code of WordPress or plugins

The method that brings you the most from a didactic point of view is the direct search in, or reading of, the source code. **As a developer, you should spend a significant amount of time reading and studying other people’s code.** And, of course, it’s important to know at least the parts of the code that are relevant to you, which you want to change with hooks. So, open the WordPress core (usually, this will be the files in _/wp-includes/_) in the code editor of your choice (I recommend [VS Code](https://code.visualstudio.com/)) and search for `apply_filters` and `do_action`.


![Search result: apply_filters](/assets/img/artikel-05-hooks-search-apply-filters.png)
_1.681 times `apply_filters` is found_

In this way, you are able to find all filter hooks WordPress is using, and you are learning, how WordPress uses hooks internally. Search for `add_filter` and `add_action` too and you will see, that WordPress uses its Hook itself. A method we will talk about later.

![Search result: add_filter](/assets/img/artikel-05-hooks-search-add-filter.png)
_405 times `add_filter` is found_

Of course, you should also know the code of a plugin whose hooks you want to use, at least partially, or have it read once. Here you can also search for the used hooks.

## Registering hooks inside a class

Now we have found the hook we’ve been looking for and we want to use it in our WordPress plugin. Let’s follow the basic example from above: we have to add a class to the `<body>` tag using the `body_class` filter. We assume that this is the first step in the development of a comprehensive plugin, where it will be worthwhile to use advanced programming methods.

We want to define hook registrations inside a class by using class methods for callbacks.There are three classic approaches to this.

### Approach 1: Use the class constructor

```php
class Frontend
{
    public function __construct()
    {
        add_filter('body_class', [$this, 'addBodyClass']);
    }

    public function addBodyClass(array $classes): array
    {
        $classes[] = 'my-added-class';

        return $classes;
    }
}
```

At an appropriate place, such as in the main file of the plugin or the main class (we’ll learn more about this in a later article), the class is then instantiated in some way. To keep things simple here, as this step is irrelevant for our discussion, let’s imagine that in the main file of the plugin, the instantiation takes place: `$frontend = new Frontend();`.

When the class is instantiated, the constructor is executed, and thus the `add_filter` function is called. Our callback in the array notation `[$this, 'addBodyClass']` is added to the `body_class` filter with the default priority of `10` and the default number of arguments of `1`.

In a lenient view, this is a valid approach to register a filter callback. However, this approach has some drawbacks, especially in large plugins:

- We are misusing the class constructor to execute the `add_filter` call. Strictly speaking, the constructor of a class should be used to assign the correct values to its parameters at the time of instantiation, in other words, to initialize the object.
- When we learn about unit testing later in this series, we will see that calling `add_filter()` in the constructor makes testing the class more difficult. We haven’t covered unit testing yet, but I would like to briefly mention here the points that make using `add_filter()` in the constructor challenging for testing:
  1. By calling `add_filter()`, the class becomes tightly coupled with WordPress. This makes it harder to test the class in isolation from WordPress, and we would have to work with elaborate mocks just to be able to test it properly.
  2. We violate the rule stating that class constructors should not have side effects but should only be responsible for object initialization. The side effect of adding the callback to the filter makes testing more complicated.
Therefore, we need an approach that allows us to avoid calling add_filter() from the constructor.

### Approach 2: Register from outside the class

In the second approach, we will remove the `add_filter()` call from the class context and perform the call after the class is instantiated:

```php
class Frontend
{
    public function addBodyClass(array $classes): array
    {
        $classes[] = 'my-added-class';

        return $classes;
    }
}

$frontend = new Frontend();
add_filter('body_class', [$frontend, 'addBodyClass']);
```

This is a significant improvement for the class itself. The class is now better suited for unit testing, and we no longer misuse the class constructor. In our example, the class no longer needs a constructor.

However, there are also drawbacks to this approach.

**Drawback 1: Separation of `add_filter()` call and class/method**

When considering the overall structure of our plugin, contemplating its architecture, the question quickly arises as to where the class should be instantiated. Since we want to rely entirely on [autoloading](https://marcuskober.com/autoloading-coding-standards-and-file-structure-in-wordpress-plugin-development/) (it wouldn’t be ideal to use autoloading for some classes while loading others with `require_once()`), the instantiation of the class cannot happen in the PHP file of the class itself.

We have the class file _src/Main/Frontend.php_:

```php
<?php

namespace MK\MyPlugin\Main;

class Frontend
{
    public function addBodyClass(array $classes): array
    {
        $classes[] = 'my-added-class';

        return $classes;
    }
}
```

The call of `add_filter()` is then done, however, e.g. in the main file _my-plugin.php_:

```php
<?php
/*
 * Plugin Name: My Plugin
*/

use MK\MyPlugin\Main\Frontend;

require __DIR__ . '/vendor/autoload.php';

$frontend = new Frontend();
add_filter('body_class', [$frontend, 'addBodyClass']);
```

So, the registration of the `addBodyClass()` method of the Frontend class using `add_filter()` is located in a different file from the class itself. This can lead to a state of unmanageable complexity and that makes changes more difficult, especially in large plugins.

For example, if we realize that the number of arguments needs to be adjusted for a filter, we are forced to edit two files: we have to pass the number of arguments to the `add_filter()` call in the main plugin file and then add the arguments themselves to the method in the class file.

Furthermore, just by looking at the `Frontend` class in our example, we cannot tell that the `addBodyClass()` method is a callback for a hook. We would need to indicate this through a suitable PHP comment:

```php
/**
 * Callback for Filter body_class
 */
public function addBodyClass(array $classes): array
```

Here, we would need to establish a standard for such comments because if a team is working on the plugin, the hook comments should always have a consistent structure.

**Drawback 2: Complexity of registration code**

This structure quickly leads to an ever-growing list of registration calls and class instantiations:

```php
<?php
/*
 * Plugin Name: My Plugin
*/

use MK\MyPlugin\Main\Frontend;
use MK\MyPlugin\Admin\Dashboard;

require __DIR__ . '/vendor/autoload.php';

$frontend = new Frontend();
$backendDashboard = new Dashboard();
// ...

add_filter('body_class', [$frontend, 'addBodyClass']);
add_action('wp_enqueue_scripts', [$frontend, 'enqueueAssets']);
add_action('admin_init', [$backendDashboard, 'registerDashboard']);
// ...
```

The long lists of class instantiations and hook registrations quickly become unwieldy. More questions arise:

- How should the calls be organized – alphabetically, by class, by hook?
- How do we maintain an overview and create a quick reference in the code to determine which methods belong to which class?

### Approach 3: Back to the class

Approach 3 is the version I have used in my plugins for a long time. In this approach, we bring the hook registration back into the class to have the registrations closer to the methods:

```php
class Frontend
{
    public static function register(): void
    {
        $self = new self();

        add_filter('body_class', [$self, 'addBodyClass']);
    }

    public function addBodyClass(array $classes): array
    {
        $classes[] = 'my-added-class';

        return $classes;
    }
}
```

The class can then be instantiated with just one call, for example, in the main plugin file:

```php
<?php
/*
 * Plugin Name: My Plugin
*/

use MK\MyPlugin\Main\Frontend;

require __DIR__ . '/vendor/autoload.php';

Frontend::register();
```

If we have many classes, the list of `::register()` calls can become long, and we will need to consider how to organize it differently. However, at least:

- We are not using the constructor for hook registration with `add_filter()`.
- The hook registration is happening within the class that also contains the callback method.
- This construction is somewhat better for unit testing, as the aforementioned side effect is easier to control through the static method, making the class more suitable for unit testing.

**Nevertheless, this method also has its drawbacks:**

- If multiple hooks are registered within a class, the static method may contain long lists of `add_filter()` and `add_action()` calls.
- With multiple hooks, the code becomes less organized as the registration calls in the static method are relatively far from their associated methods. Again, we would need to use appropriate comments to indicate which method belongs to which hook.
- For unit testing, this method is more suitable, but it would still be nice if the static method could be eliminated.

## Registering hooks using PHP attributes

I occasionally enjoy reading the source code of non-WordPress projects and I’m interested in the structure of large frameworks like [Symfony](https://symfony.com/) and [Laravel](https://laravel.com/).

In Symfony’s [routing](https://symfony.com/doc/current/routing.html), I came across the use of PHP attributes for defining routes ([see here](https://symfony.com/doc/current/routing.html#matching-http-methods)).

It looks something like this:

```php
#[Route('/api/posts/{id}', methods: ['GET', 'HEAD'])]
public function show(int $id): Response
```

I found that very elegant and immediately saw the resemblance to the registration of hooks in WordPress plugins!

### What are PHP attributes?

PHP attributes are metadata used to provide additional information about declarations such as classes, methods, properties, or functions. They allow you to write declarative code that influences the behavior of the corresponding elements in a clean and organized way.

### How are PHP attributes defined?

PHP attributes serve as metadata for classes, methods, functions, parameters, properties, and constants, and they are declared directly on the line above the element that should be adorned with an attribute.

An attribute begins with the hash sign (`#`), while the attribute itself is enclosed in square brackets. The simplest declaration looks like this:

```php
#[AttributName]

```

Attributes can also have parameters:

```php
#[AttributName('value')]
```

We mainly use attributes on methods in this article, so the examples provided focus on that usage.

What is important for us is also the ability to use attributes multiple times since it may be necessary to define two different hooks for a method:

```php
#[Attribut1('value')]
#[Attribut2('value')]
public function someMethod(): void
```

### Attributes for hook registration

Now, we want to achieve the ability to bind hook declarations directly to the method, so that our class looks like the following example. I have expanded the class with an additional hook to make it more illustrative:

```php
class Frontend
{
    #[Filter('body_class')]
    public function addBodyClass(array $classes): array
    {
        $classes[] = 'my-added-class';

        return $classes;
    }

    #[Action('wp_enqueue_scripts')]
    public function enqueueAssets(): void
    {
        wp_enqueue_style('my-plugin', 'style.css');
    }
}
```

At a glance, we can see the advantages that PHP attributes offer compared to the other methods!

- No more need for a static method.
- By using PHP attributes, the declaration of the hook is in direct proximity to the respective method. This is clear and concise, and we immediately know that the method is a callback for a hook. We can make changes in direct correlation to the method.
- The class is not only easier to cover with unit tests, but we can even establish tests to verify that the correct attributes, i.e., the correct hook registrations, are used.

However, we still have two major issues to address:

1. How do PHP and WordPress know what to do with these attributes?
2. How and where should classes with hooks be instantiated?
Fortunately, these questions are answered by each other, as we will see shortly.

### Defining Attributes

First, we need to inform PHP that we want to use two attributes named `Filter` and `Action`, which accept the hook name, priority, and number of arguments as parameters.

To do this, we create the folder _Attributes_ inside _src/_ and create the file _Filter.php_ inside it. With this addition, our sample plugin now has the following structure:

```
my-plugin
├── src
│   ├── Attributes
│   │   └── Filter.php
│   └── Main
│       └── Frontend.php
└── my-plugin.php
```

An attribute in PHP is nothing more than a simple class with its constructor setting the parameters for the attribute. We use [constructor property promotion](https://php.watch/versions/8.0/constructor-property-promotion) to define the parameters:

File _src/Attributes/Filter_

```php
<?php

namespace MK\MyPlugin\Attributes;

class Filter
{
    public function __construct(
        public string $hookName,
        public int $priority = 10,
        public int $acceptedArgs = 1
    )
    {}
}
```

To inform PHP that our simple class is an attribute definition, we can use, ironically, an attribute itself. The attribute declares our `Filter` class as an attribute:

```php
<?php

namespace MK\MyPlugin\Attributes;

use Attribute;

#[Attribute]
class Filter
{
    public function __construct(
        public string $hookName,
        public int $priority = 10,
        public int $acceptedArgs = 1
    )
    {}
}
```

With that, we have successfully defined our Filter attribute. We can further specify that the Filter attribute can only be used on methods and that we can use the attribute multiple times on a single method. We do this by specifying the [corresponding flags](https://www.php.net/manual/en/language.attributes.classes.php) for the attribute: `Attribute::TARGET_METHOD` and `Attribute::IS_REPEATABLE`:

```php
#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
class Filter
```

[Here you can find more about attribute declaration in PHP.
](https://www.php.net/manual/en/language.attributes.classes.php)

So, we have established the `Filter` attribute, but it doesn’t do anything yet. We now have three steps ahead of us:

1. Find a method for instantiating classes with hooks.
2. Find a way to recognize that a class uses hooks.
3. Actually register the hooks using `add_filter()` and `add_action()`.

### Loading, parsing, and instantiating classes with hooks

We will now take care of steps 1 and 2 from the previous section. To do this, we need to tell our plugin where to find the classes that we want to check for hooks.

For the overall architecture, it is important that the classes using hooks should not be instantiated elsewhere. They should only be instantiated through the code we are about to develop. Since classes should generally do only one thing and not have too much responsibility, this should not be a problem.

There are different ways to locate the classes that use hooks. We could search through all classes, but that wouldn’t be very performant.

First, we create a directory in the root folder of our plugin, which we can name config, and create a file inside it, which can be named _hooked-classes.php_, for example:

```
my-plugin
├── config
│   └── hooked-classes.php
├── src
│   ├── Attributes
│   │   └── Filter.php
│   └── Main
│       └── Frontend.php
└── my-plugin.php
```

In the PHP file, we now list our only class that uses hooks:

```php
<?php

use MK\MyPlugin\Main\Frontend;

/**
 * List of classes with hooks
 */
return [
    Frontend::class,
];
```

So, the file simply returns an array with the fully qualified class name of Frontend (`::class` is a static constant that contains the fully qualified class name, more [here](https://www.php.net/manual/de/language.oop5.basic.php#language.oop5.basic.class.class)).

Next, we want to remove the main code from the plugin file _my-plugin.php_ and establish a plugin main class called `App`, which we include under `Main`:

```
my-plugin
├── config
│   └── hooked-classes.php
├── src
│   ├── Attributes
│   │   └── Filter.php
│   └── Main
│       ├─── App.php
│       └── Frontend.php
└── my-plugin.php
```

Now, the App class in the _App.php _file is lean:

```php
<?php

namespace MK\MyPlugin\Main;

class App
{
    public function init(): void
    {
    }
}
```

And the _my-plugin.php_ file is now very clean as well, but we need to define a constant that contains the path of the plugin:

```php
<?php
/*
 * Plugin Name: My Plugin
 */

use MK\MyPlugin\Main\App;

define('MYPLUGIN_DIR', plugin_dir_path( __FILE__ ));

require __DIR__ . '/vendor/autoload.php';

(new App())->init();
```

In the main class, we now establish a private method that will handle our classes with hooks, and in the constructor of the class, we load the list of class names:

```php
<?php

namespace MK\MyPlugin\Main;

class App
{
    public function __construct(
        private array $classNames = [];
    )
    {
        $this->classNames = require MYPLUGIN_DIR . 'config/hooked-classes.php';

    }

    public function init(): void
    {
        $this->registerHooks();
    }

    private function registerHooks(): void
    {
        foreach ($this->classNames as $className) {
            // Code for registering hooks
        }
    }
}
```

In the `registerHooks()` method, we process the class names in a `foreach` loop. But what exactly do we do with the class names we obtain in this way? How can we find out if and which hooks are registered here?

We make use of PHP’s [Reflection API](https://www.php.net/manual/en/intro.reflection.php) for this purpose. With this API, we can examine classes at runtime of the script.

In our loop, we first create a reflection class of the original class using `new ReflectionClass()` ([more about ReflectionClass](https://www.php.net/manual/de/class.reflectionclass.php)). The reflection class can then provide us with all the methods of the class to be examined using the `getMethods()` method ([more about this method](https://www.php.net/manual/de/reflectionclass.getmethods.php)).

```php
foreach ($this->classNames as $className) {
    $reflectionClass = new ReflectionClass($className);

    foreach ($reflectionClass->getMethods() as $method) {
        // Check methods for attributes
    }
}
```

Now we check if the method has attributes. The `getAttributes()` method ([more info](https://www.php.net/manual/de/reflectionfunctionabstract.getattributes.php)) can return all `Filter` attributes in an array by passing the class name of our attribute to the method. If filter attributes are defined, we can then iterate over them with another `foreach` loop:

```php
foreach ($reflectionClass->getMethods() as $method) {
    $attributes = $method->getAttributes(Filter::class);

    foreach ($attributes as $attribute) {
        // Check attributes
    }
}
```

In this final loop, we finally instantiate our class containing hooks and then need to figure out how to register the callback using `add_filter()`. To do this, it would be good if we could first instantiate the attribute class `Filter`. Fortunately, we can do this using the `newInstance()` method of `$attribute`:

```php
foreach ($attributes as $attribute) {
    // Instantiate class with hooks
    $hookedClass = new $className();

    // Instantiate filter class
    $filterClass = $attribute->newInstance();
}
```

Now we have everything we need in one place: we have an instance of the `Frontend` class, an instance of the attribute class `Filter`, and the method adorned with a filter attribute. We want to define the registration process within the `Filter` class itself. To do this, we establish a `register()` method that passes the callback as an array:

```php
#[Attribute]
class Filter
{
    public function __construct(
        public string $hookName,
        public int $priority = 10,
        public int $acceptedArgs = 1
    )
    {}

    public function register(callable|array $method): void
    {
        add_filter(
            $this->hookName,
            $method,
            $this->priority,
            $this->acceptedArgs
        );
    }
}
```

Now that the `register` method is established, we can call it within the loop in the registerHooks method in the _App.php_ file:

```php
foreach ($attributes as $attribute) {
    // Instantiate class with hooks (Frontend in our case)
    $hookedClass = new $className();

    // Instantiate filter class
    $filterClass = $attribute->newInstance();
    $filterClass->register(
        [
            $hookedClass,
            $method->getName()
        ]
    );
}
```

Now we are almost finished with the filter. We just need to ensure that each class with hooks is instantiated only once in `registerHooks`. Multiple instantiation is not necessary here. To achieve this, we create an instance array and check whether the class has already been instantiated. For clarity, here is the complete method again:

```php
private function registerHooks(): void
{
    $instances = []; 

    foreach ($this->classNames as $className) {
        $reflectionClass = new ReflectionClass($className);

        foreach ($reflectionClass->getMethods() as $method) {
            $attributes = $method->getAttributes(Filter::class);

            foreach ($attributes as $attribute) {
                // Instantiate class if not instantiated
                if (! array_key_exists($className, $instances)) {
                    $instances[$className] = new $className();
                }

                // Instantiate filter class
                $filterClass = $attribute->newInstance();
                $filterClass->register(
                    [
                        $instances[$className],
                        $method->getName()
                    ]
                );
            }
        }
    }
}
```

Indeed, we have now completed the registration of filters! We would now need to do the same for actions. But of course, this can be done more easily.

Let’s take another look at this line:

```php
$attributes = $method->getAttributes(Filter::class);
```

And now let’s check what we can do with `getAttributes()`:

```php
public ReflectionFunctionAbstract::getAttributes(?string $name = null, int $flags = 0): array
```

Interesting, so we can provide flags! A look at the [documentation](https://www.php.net/manual/de/reflectionfunctionabstract.getattributes.php) shows that we can set the flag `ReflectionAttribute::IS_INSTANCEOF`, which means that filtering is no longer done by the exact name of the attribute class, but instead `instanceof` is used for filtering.

This naturally leads us to the idea of using an [interface](https://www.php.net/manual/de/language.oop5.interfaces.php) for the attributes. We create two new files in our Attributes folder: _Action.php_ and _HookInterface.php_:

```
my-plugin
├── config
│   └── hooked-classes.php
├── src
│   ├── Attributes
│   │   ├── Action.php
│   │   ├── Filter.php
│   │   └── HookInterface.php
│   └── Main
│       ├── App.php
│       └── Frontend.php
└── my-plugin.php
```

In the _HookInterface.php_ file, we establish our interface that requires our hook attributes to implement a `register()` method:

```php
<?php

namespace MK\MyPlugin\Attributes;

interface HookInterface
{
    public function register(callable|array $method): void;
}
```

In the _Filter.php_ file, we now need to declare the Filter class as an implementation of `HookInterface`:

```php
#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
class Filter implements HookInterface
```

With that, we are indeed completely done with the filter.

### Creating the Action Attribute

Creating the Action attribute class is now the easiest task. As we saw earlier, `add_action()` internally just calls `add_filter()`, which is why we can work with inheritance here. We open the _Action.php_ file and create the following code:

```php
<?php

namespace MK\MyPlugin\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
class Action extends Filter
{
}
```

So, we only create a new class that completely extends the `Filter` class. All properties from `Filter` are inherited in `Action`.

As the last step, we now need to adjust the `getAttributes()` method in the `App::registerHooks()` method:

```php
$attributes = $method->getAttributes(HookInterface::class, ReflectionAttribute::IS_INSTANCEOF);
```

Thus, `getAttributes()` returns all attributes that implement the `HookInterface`, which in our case are the attribute classes `Filter` and `Action`.

## Conclusion and Outlook

In this article, you have learned what hooks in WordPress are and how to register callbacks for hooks. We have seen the methods for registering hooks in classes and how to use autoloading, namespaces, and PHP attributes to implement hook registration in a simple, clear, and unit-test-compatible way.

The solution we have developed is complex but can now be used in any of your plugins that require it. All you have to do is drag the Attributes folder into your new plugin and adjust the namespaces.

In my opinion, we have created a good foundation that defines hooks exactly where the callback is located and ensures that everything is loaded almost magically through [autoloading](https://marcuskober.com/autoloading-coding-standards-and-file-structure-in-wordpress-plugin-development/) without endless require and instantiation lists. In a later step, we should optimize the `registerHooks()` method, or create a separate class for it. This will be covered in one of the upcoming articles.

**In the next article, we will focus on structuring your plugin in such a way that you can rely solely on hooks. This will make your plugins virtually infinitely extensible and even more maintainable. A nice side effect is that you open your plugin for modifications and adjustments by other plugins.**

In a further article, we will return to the techniques described here and learn why **dependency injection** is a must in this architecture.

## Plugin code

You can find the entire plugin code [here on GitHub](https://github.com/marcuskober/wp-articles-code).

**Note:** Usually, you would add the _vendor_ directory to the _.gitignore_ file, as it has no place in the repository. For the sake of clarity, however, I made an exception here so you can see which code is generated by Composer.
