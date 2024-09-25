---
title: "Elevate Your Plugin Development with Hook-Driven-Development"
description: "After the previous article in this series was very technical, today we'll take a breather and indulge in a slightly more theoretical piece. Our topic is Hook-Driven-Development and why you too should implement it in your plugins."
summary: "After the previous article in this series was very technical, today we'll take a breather and indulge in a slightly more theoretical piece. Our topic is Hook-Driven-Development and why you too should implement it in your plugins."
date: 2023-05-24
categories: ["Modern PHP in WP plugins"]
tags: ["event-driven"]
author: "Marcus Kober"
slug: "elevate-your-plugin-development-with-hook-driven-development"
lang: en
---

{{< toc >}}

**After the previous article in this series was very technical, today we'll take a breather and indulge in a slightly more theoretical piece. Our topic is Hook-Driven-Development and why you too should implement it in your plugins.**

In WordPress plugin development, hooks are a fundamental and indispensable tool. Hooks ensure that a plugin is extendable, scalable, and remains cleanly structured. Hook-Driven-Development is an approach in plugin development that not only involves using the existing hooks of WordPress and other plugins but also incorporating your own hooks and using them within the plugin itself.

In this article, I assume a basic understanding of hooks and their use in developing WordPress plugins.

Advantages of Hook-Driven-Development
-------------------------------------

We don't want to use Hook-Driven-Development just for fun – its application also has some compelling advantages.

### Advantage 1: Modularity and Flexibility

By consistently using hooks in your plugin, you enhance its modularity and flexibility.

*   **Ease of Modification and Expansion** 
Hooks allow for easier code adaptation to individual or new needs. They also facilitate the addition of extra functions without changing existing code. New functions, when using hooks, can either be defined in the actual plugin code, or added via additional plugins (e.g., addons). This enables you to offer a plugin in a free and a paid version. You develop the free version as a complete plugin, and the additional functions of the paid version are then integrated as a paid addon plugin. <small>However, if you want to implement such a model of a free and paid version, please ensure the free version provides real added value and avoid angering your users with an inadequate free version that wants to sell the paid version at every turn! There are already far too many such plugins.</small>
    
*   **Replacement of Components** 
The modular nature of plugins developed via Hook-Driven-Development allows individual components of the plugin to be easily replaced without affecting the rest of the code.
    

### Maintainability and Scalability

The maintainability and scalability of your plugin also benefit from hook-based development.

*   **Better Structuring of Large Projects** Here we are back to my favorite topic! By strictly using hooks as a fundamental element of code structuring, it becomes much easier to implement well-designed plugins that can naturally grow and remain transparent. The question of where to put newly implemented code will rarely (if ever) arise.
*   **Easier Updating and Debugging** With clearly defined hooks and the corresponding callbacks in small classes, updating individual components and troubleshooting becomes significantly easier – all without affecting the entire system.

### Interoperability and Collaboration

The use of hooks facilitates interoperability between plugins and cooperation among developers.

*   **Interoperability** 
As a clear interface, hooks facilitate communication between plugins. To reflect the adaptability of WordPress and many plugins in your plugin, you should offer hooks at important points in your plugin. Of course, it's absolutely permitted to leave certain things **without** hooks if these things should not be changeable for security reasons.
*   **Easier Cooperation through Shared Standards** 
Hooks form the common basis in plugin development, which is familiar to all plugin developers. This makes it easier to win developers for collaborative development. But your plugin will also gain respect in the developer community when you define enough hooks so that your plugin can be customized to individual needs.

Principles of Hook-Driven-Development
-------------------------------------

All this sounds great, but what should it look like in practice? I will outline that here.

### Actions and Filters as the Central Concept in Plugin Development

We define Actions and Filters as the central concept in plugin development. You are probably familiar with the MVC pattern ([Model, View, Controller](https://en.wikipedia.org/wiki/Model–view–controller)). In a way, one could say that in hook-driven development, the hooks (or the classes whose methods serve as callbacks for hooks) take on the role of the [controllers](https://en.wikipedia.org/wiki/Model–view–controller#Controller) from the MVC approach. In the MVC pattern, the controller is the central control unit between the [model](https://en.wikipedia.org/wiki/Model–view–controller#Model) (i.e., the business logic) and the [view](https://en.wikipedia.org/wiki/Model–view–controller#View).

**In our hook-driven approach, the hooks, or the classes containing hooks, form the central control unit.**

### Designing Plugin Architecture Around Hooks

An effective hook-driven development strategy requires that the plugin architecture be designed from the ground up to integrate hooks. This means structuring the code in a way that it is broken down into smaller, reusable modules and these modules then communicate via hooks.

This promotes the efficient structuring of the plugin into reusable modules, a clear separation of functions and responsibilities. As a result, the plugin can be easily and quickly extended in the future and is simple to maintain and test.

### Definition and Use of Own Hooks

If your plugin already uses hooks from WordPress and/or other plugins (which it should), it probably still does not automatically already rely on hook-driven development. Only the definition and use of own hooks in sufficiently extensive plugins forms the basis for well-implemented and successful hook-driven development.

By using your own hooks, you open up your plugin to the outside world and allow other developers to build upon your plugin or modify individual aspects while simultaneously ensuring a good structure for your code.

## Best Practices for Hook-Driven-Development

For your hook-driven approach to be truly successful and to facilitate the structuring of your plugin on the one hand, and to meaningfully open up your plugin to the outside on the other hand, you should consider a few best practices. Only in this way is effective and sustainable implementation possible.

### Naming Conventions

Choosing unique and meaningful names for hooks is crucial for the readability and understandability of the code. Here are some recommendations for effective naming conventions:

- **Use of a Prefix**
So that the hooks of your plugin are directly recognizable as such, they should consistently use their own prefix. This also makes it less likely that identical hook names will be defined by WordPress or other plugins. The prefix could be the name of your plugin, if it is short enough, or you could use an abbreviation of the plugin name. The [hooks of WooCommerce](https://woocommerce.github.io/code-reference/hooks/hooks.html), for example, all start with `woocommerce_`.
- **Use Descriptive Names**
Choose descriptive names for your hooks. It should be as clear as possible what your hook is used for. It should be clear that a name like `myplugin_hook_1` is of little use, while `myplugin_menu_items` suggests that it could be a filter for menu entries.
- **Consistent Notation**
Maintain a consistent notation of your hook names. How you write the hooks is not very relevant - the important thing is not to mix different notations. WordPress and therefore many plugins use the underscore (`myplugin_menu_items`), while others also use *camelCase* (`mypluginMenuItems`). For particularly extensive plugins with many hooks, the use of slashes (`myplugin/navigation/menuItems`) could lead to more clarity, and there are some examples of this in the plugin world, such as the well-known plugin [Elementor](https://wordpress.org/plugins/elementor/).

### Documentation

Not only developers of other plugins need good documentation of your plugin's hooks. You yourself (or especially your team, if you have one) also need this documentation. After some time, you will be very happy to be able to look up what a certain hook does and where it is used.

There are two approaches:

- **Inline Comments**
For you and other developers, it is especially important to rely on inline comments when there are functions and dependencies that are not directly evident from the hook name and the underlying code. The general rule of PHP comments applies here: use as few inline comments as possible, but as much as necessary.
- **External Documentation**
Especially for extensive plugins, it can be advantageous to create external documentation. You can create this on your website or plugin website, or you can use services that are already designed for this purpose, such as [GitBook](https://www.gitbook.com/) or [Read the Docs](https://readthedocs.org/). There is also an interesting project that automatically documents your hooks: the [Pronamic WordPress Documentor](https://github.com/pronamic/wp-documentor). It automatically generates documentation in various formats based on your hook declarations.

### Testing the Hooks

Since hooks often serve as interfaces to other plugins or parts of WordPress, it is important to ensure their stability and reliability. This requires careful testing, for example, with PHP Unit, which we will cover in a later article. It is important to consider the following points:

- **Test Coverage**
Make sure that all hooks, or their corresponding callbacks, are covered by tests.
- **Testing Dependencies on External Plugins**
Dependencies on other plugins should be avoided as much as possible during plugin development. However, if your plugin still requires another plugin, it is important to clearly communicate the dependencies, and your plugin should check if the required plugin is installed and activated during activation using appropriate methods. All dependencies must be tested, and when external plugins are updated, it must be ensured that the communication still works smoothly.
- **Testing Incompatibilities with Other Plugins**
Test your plugin in a WordPress installation that has popular plugins installed and activated. Do problems with your hooks occur here, such as name collisions? All of this must be tested to ensure smooth operation.

### Backward Compatibility When Renaming Hooks

It may happen that you need or want to rename hooks. Perhaps because you just read this article and have been thinking about your naming conventions.

As long as your plugin is still in development, you only need to make sure to change all occurrences of the hook accordingly.

However, the situation is different if your plugin is already being used on websites. In that case, other plugins (or themes) may be using your hooks. If you were to simply rename your hooks and the new version of your plugin is updated on the websites, the hooks used by other plugins would no longer work. And this could potentially affect the users' websites who use your plugins.

To avoid such conflicts, you need to respond to hook renaming in an appropriate manner.

Let's imagine that you noticed that a hook in your plugin from the early stages of development still uses a hook with the not-so-nice name `myplugin_hook_01`. While creating your external documentation, you get annoyed by it and decide that the hook should have the new name `myplugin_menu_items`.

There is a perfect way to achieve backward compatibility in case other developers have already been using `myplugin_hook_01` in their plugins (and have already been frustrated by the name)!

For this example, let's assume that the call with the old hook name looks like this:

```php
$menuItems = apply_filters('myplugin_hook_01', $menuItems, $menuId, $menuObject);
```

In the first step, you rename the hook:

```php
$menuItems = apply_filters('myplugin_menu_items', $menuItems, $menuId, $menuObject);
```

In the second step, you create a file in your plugin called *DeprecatedHooks.php*. In this file, you register a callback for the **new(!)** hook using our PHP attribute method from [previous article](https://marcuskober.com/registering-wordpress-hooks-effectively-with-php-attributes/):

```php
<?php

namespace MK\MyPlugin\Main;

use MK\Attributes\Filter;

class DeprecatedHooks
{
    #[Filter('myplugin_menu_items', 0, 3)]
    public function menuItems(array $menuItems, int $menuId, object $menuObject): array
    {
    }
}
```

Now we can simply fire the old hook and we're done:

```php
#[Filter('myplugin_menu_items', 0, 3)]
public function menuItems(array $menuItems, int $menuId, object $menuObject): array
{
    return apply_filters('myplugin_hook_01', $menuItems, $menuId, $menuObject);
}
```

However, this would silently fire the old hook, and other developers would only become aware of the renaming if they read your changelogs.

That's why we have the functions `apply_filters_deprecated()` and `do_action_deprecated`.

In our example, we are focusing on filters, but the same applies to actions. Let's take a look at `apply_filters_deprecated()`:

```php
apply_filters_deprecated(
    string $hook_name, 
    array $args, 
    string $version, 
    string $replacement = '', 
    string $message = '' 
)
```

Great! We can pass the old hook name to the function, all the arguments in the `$args` array, the version from which the hook was deprecated in `$version`, and the new hook name in `$replacement`. Additionally, we can even provide a message regarding the renaming in `$message`.

In our case, it would look like this:

```php
#[Filter('myplugin_menu_items', 0, 3)]
public function menuItems(array $menuItems, int $menuId, object $menuObject): array
{
    return apply_filters_deprecated(
        'myplugin_hook_01', // Old hook name
        [$menuItems, $menuId, $menuObject], // Arguments as an array
        '1.1.0', // Version of our plugin where the renaming took place
        'myplugin_menu_items', // New hook name
        'Changed the hook name.' // Message
    );
}
```

Now, the old hook still works as if nothing has changed. However, WordPress will throw a deprecation notice that will be displayed if the `WP_DEBUG` constant in the **wp-config.php** file is set to `true`:

`Deprecated: Hook myplugin_hook_01 is deprecated since version 1.1.0! Use myplugin_menu_items instead. Changed the hook name.`

The same concept applies to actions using the `do_action_deprecated()` function.

By following this approach, we have achieved everything that is important when renaming a hook:

- The hook has been renamed.
- The old hook continues to function for backward compatibility.
- Other developers or our development team will be warned when using old hooks.

## Examples of Successful Hook-Driven Plugins

There are already many excellent plugins that utilize hook-driven development. The extent to which a plugin is entirely based on hooks may vary. The following plugins can serve as good examples, although they may not be suitable examples in all aspects of architectural decisions.

### WooCommerce

[WooCommerce](https://woocommerce.com/) is **the** E-Commerce plugin for WordPress, developed by [Automattic](https://automattic.com/), the company founded by Matt Mullenweg, the creator of WordPress. WooCommerce:

- Offers a wide range of hooks that allow for extensive extension and customization of WooCommerce.
- Utilizes the hooks defined within the plugin extensively for its own functionality.
- Serves as a great example of the possibility for extension through standalone add-on plugins, which WooCommerce distributes as modular paid plugins.

### Advanced Custom Fields

The well-known plugin [Advanced Custom Fields](https://wordpress.org/plugins/advanced-custom-fields/), commonly loved by developers, especially during the pre-Gutenberg era, is another excellent example of hook-driven development. Originally developed by [Elliot Condon](https://profiles.wordpress.org/elliotcondon/), acquired by [Delicious Brains](https://deliciousbrains.com/), and now driven by [WP Engine](https://wpengine.com/).

Even today, the plugin remains powerful and essential and can serve as a good example of hook-driven development. ACF provides many hooks for developing great extensions.

These are just a few examples of successful hook-driven plugins. While the level of implementation may vary, they demonstrate the benefits and possibilities of hook-driven development.

## Code Example

While we can't develop a complete plugin using hook-driven development in this text, I can provide a small example to illustrate the concept. Please note that due to space limitations, the example may be simplified.

Let's assume you have developed a plugin that needs to add menu items to a specific menu with the slug `main-menu`. You're already using the hook `"wp_nav_menu_{$menu->slug}_items"` to achieve this ([documentation](https://developer.wordpress.org/reference/hooks/wp_nav_menu_menu-slug_items/)):

```php
class FrontendMenu
{
    #[Filter('wp_nav_menu_main-menu_items', 10, 2)]
    public function addMenuItems(string $items, object $args): string
    {
        $newItems = [
            'dashboard' => [
                'url' => esc_url(get_permalink(get_option('myplugin_dashboard_page_id'))),
                'title' => 'Dashboard',
            ],
            'faq' => [
                'url' => esc_url(get_permalink(get_option('myplugin_faq_page_id'))),
                'title' => 'FAQ',
            ],
        ];

        $newItems = array_map(function($menuItem) {
            return sprintf(
                '<li><a href="%s">%s</a></li>',
                $menuItem['url'],
                $menuItem['title']
            );
        }, $newItems);

        $items .= implode("\n", $newItems);

        return $items;
    }
}
```

In this code, we register the `addMenuItems()` method as a callback for the `wp_nav_menu_main-menu_items` hook. Inside the method, we define an array `$newItems` that contains the new menu items. We use `array_map()` to transform the menu item arrays into HTML list item strings. Finally, we concatenate the new items to the `$items` string and return it.

Now, let's take a step towards hook-driven development and expose the new menu items to the outside so that we and other developers can manipulate the items. We introduce a new filter called `myplugin_menu_items`:

```php
#[Filter('wp_nav_menu_main-menu_items', 10, 2)]
public function addMenuItems(string $items, object $args): string
{
    $newItems = apply_filters('myplugin_menu_items', [], 'main-menu', $args);

    $newItems = array_map(function($menuItem) {
        return sprintf(
            '<li><a href="%s">%s</a></li>',
            $menuItem['url'],
            $menuItem['title'],
        );
    }, $newItems);

    $items .= implode("\n", $newItems);

    return $items;
}
```

We pass an empty array as the default value to our new filter, along with the menu slug and the `$args` object of the original filter, in case we or other developers need it. In the original class, we can then populate the menu:

```php
class FrontendMenu
{
    #[Filter('wp_nav_menu_main-menu_items', 10, 2)]
    public function addMenuItems(string $items, object $args): string
    {
        $newItems = apply_filters('myplugin_menu_items', [], 'main-menu', $args);

        $newItems = array_map(function($menuItem) {
            return sprintf(
                '<li><a href="%s">%s</a></li>',
                $menuItem['url'],
                $menuItem['title'],
            );
        }, $newItems);

        $items .= implode("\n", $newItems);

        return $items;
    }

    #[Filter('myplugin_menu_items', 10, 3)]
    public function generateMenuItems(array $items, string $menuSlug, array $args): array
    {
        if ('main-menu' !== $menuSlug) {
            return $items;
        }

        $newItems = [
            'dashboard' => [
                'url' => esc_url(get_permalink(get_option('myplugin_dashoboard_page_id'))),
                'title' => 'Dashboard',
            ],
            'faq' => [
                'url' => esc_url(get_permalink(get_option('myplugin_faw_page_id'))),
                'title' => 'FAQ',
            ],
        ];

        return array_merge($items, $newItems);
    }
}
```

So, first, we apply the filter using `apply_filters` with an empty array to populate it in the `generateMenuItems()` method.

What have we achieved with this approach?

- Flexibility:
  - We can now add new menu items from anywhere in our code by using the `myplugin_menu_items` hook. For example, we can conditionally display a login or logout link based on whether a user is logged in. 
  - By decoupling the addition of menu items from the `wp_nav_menu_main-menu_items` hook, we have the flexibility to insert new menu items before the Dashboard link by using the `myplugin_menu_items` hook with a priority of `9`.
- Extensibility:
  - We allow other plugins to modify the list of new menu items. Other plugins can add or remove menu items to our plugin's menu.
  - Our own code or add-on plugins can also influence the menu by utilizing the `myplugin_menu_items` hook.
- Testability:
  - The array containing the new menu items can now be the subject of tests.

This was just a small and perhaps somewhat contrived example, but it demonstrates the approach and benefits of hook-driven development. In the remaining articles, the concept will become even clearer.

## Conclusion and Outlook

In this article, we learned about what Hook-Driven Development is, the benefits it brings to WordPress plugin development, and the best practices involved. We also saw a small code example that demonstrated how this approach can be implemented in real-world scenarios.

In the next article, we will learn about using Dependency Injection in WordPress plugins. We will explore why Dependency Injection may be necessary, especially when utilizing hook registration via PHP attributes.

