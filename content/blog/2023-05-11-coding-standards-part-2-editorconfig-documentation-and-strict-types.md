---
title: 'Coding standards part 2: .editorconfig, documentation and strict types'
summary: 'In the previous article, we already learned a lot about coding standards, and today I like to discuss three more points: the file _.editorconfig_, the documentation of your code, and the declaration of strict types.'
date: 2023-05-11
categories: ["Modern PHP in WP plugins"]
tags: ["coding standards", "documentation", "psr"]
author: "Marcus Kober"
slug: "coding-standards-part-2-editorconfig-documentation-and-strict-types"
lang: en
---

{{< toc >}}

**In the previous article, we already learned a lot about coding standards, and today I like to discuss three more points: the file _.editorconfig_, the documentation of your code, and the declaration of strict types.**

## The file .editorconfig

The _.editorconfig_ file is a text file you create in the root folder of your plugin. This file ensures, that you and your team use the same standards for line breaks, indentations, and character sets throughout the project. Editors like Visual Studio Code can read this file and apply the formatting and settings automatically. [Visual Studio Code](https://code.visualstudio.com/) requires [this extension](https://marketplace.visualstudio.com/items?itemName=EditorConfig.EditorConfig) for that purpose.

The extension for Visual Studio code supports these settings:

- `indent_style`: Indentation style: spaces or tabs
- `indent_size`: Size of indentation when spaces are used (soft tabs)
- `tab_width`: Size of indentation when tabs are used
- `end_of_line` (applied when saving): Type of line breaks
- `insert_final_newline` (applied when saving): Specifies whether an empty line should be added to the end of the file (see PSR-2)
- `trim_trailing_whitespace` (applied when saving): Removes any whitespace after the actual code line

In the _.editorconfig_, you can set the styles for each programming language or file extension.

My file usually looks like this:

```yaml
# http://editorconfig.org

root = true

[*]
charset = utf-8
end_of_line = lf
indent_size = 2
indent_style = space
trim_trailing_whitespace = true

[*.php]
indent_size = 4
insert_final_newline = true

[*.scss]
insert_final_newline = true

[*.md]
trim_trailing_whitespace = false
```

With `root = true`, it is specified that the file is located in the root directory of your plugin. Text editors like VS Code initially search for a _.editorconfig_ file in the folder of the currently opened code file. If none is found, it searches one folder above, and so on. The line `root = true` means that the search can end here and the file can be used since root has been reached.

With the declarations under `[*]`, the general settings are made, and below the settings for specific file extensions are overwritten. I want the final newline only in PHP and SCSS files, for example.

**Even if you don’t work in a team, I recommend using a _.editorconfig_ file. This ensures that you use the same styles in every project and on every device.**

More infos: [editorconfig.org](https://editorconfig.org/).

## Documentation

Hated by many developers, it is still an important tool for you and your team. I would like to emphasize here that documenting your code is also important when developing your plugin alone. I promise you will love your documentation when you have to go through your code again after two to three months! Code quickly becomes distant, and after just a few months, you won’t remember specific details, like why you implemented something in a particular way and not differently.

When it comes to documentation, we generally distinguish between two types of documentation:

- Documentation in the code itself.
- External Documentation.

### Documentation inside your code

In PHP, we distinguish not only single-line documentation with a preceding double slash (`//`) and multi-line documentation in the following form:

```php
/* This is a comment, 
   that spans
   over multiple lines
*/
```

We also distinguish between documentation at the file, class, and function/method level. I recommend documenting in the [PHPDoc format](https://de.wikipedia.org/wiki/PHPDoc), as used, for example, by [phpDocumentor](https://docs.phpdoc.org/guide/getting-started/what-is-a-docblock.html#what-is-a-docblock). A PHPDoc block starts not with `/*`, but with two asterisk symbols: `/**`. Text editors like Visual Studio Code can quickly create these blocks when typing `/**`, which reduces typing work.

Here is an example of the various doc blocks.

```php
<?php

/**
 * At the very top appears the file-level documentation. Here you can discuss
 * what exactly is happening in this file. As with all documentation:
 * 
 * Document as much as necessary and as little as possible!
 * 
 * What this means, you can read further below.
 * 
 * Here you can, for example, specify the author and the version with which
 * this file was created:
 * 
 * @author: Marcus Kober
 * @since: 1.2.1
 */ 

declare(strict_types=1);

namespace MK\Test\Main;

use MK\Test\Attributes\Filter;

/**
 * DocBlock at the class level
 * 
 * The DocBlock is created directly before the class to be documented and indicates
 * what the class is for.
 */
class Frontend
{
    /**
     * DocBlock at the variable level
     * 
     * Here you can explain what this variable does
     *
     * @var array
     */
    protected array $addedClasses = [];

    /**
     * DocBlock at the method level
     * 
     * Here you can explain what the method does.
     * 
     * The DocBlock comes before the declaration of attributes, if
     * any are used.
     *
     * @param array $classes
     * @return array
     */
    #[Filter('body_class')]
    public function addClass(array $classes): array
    {
        // Single-line comment, very briefly, explaining something specific
        $this->addedClasses[] = 'my-class';

        return array_merge($classes, $this->addedClasses);
    }
}
```

The statements starting with `@`, such as `@author` or `@since`, are called “tags” of the DocBlock syntax. Here you can find a list of possible tags. For variables and methods, you can additionally specify types with `@var`, or `@param` and `@return`.

Commenting your code with DocBlocks not only makes your code more transparent for you and your team. You can also use phpDocumentor to create [external API documentation](https://docs.phpdoc.org/guide/getting-started/generating-documentation.html#generating-documentation) from sufficiently well-documented code. This can be added to your external documentation (if you need it) or even replace it.

###External Documentation

For very extensive plugins, you can create external documentation either publicly or just for yourself and/or your team. External documentation takes place outside of your code, for example, on a website, or you can use services designed for it, such as [GitBook](https://www.gitbook.com/) or [Read the Docs](https://readthedocs.org/).

External documentation is particularly important for documenting your own hooks. There is also an interesting project that automatically documents your hooks: the [Pronamic WordPress Documentor](https://github.com/pronamic/wp-documentor). It automatically generates documentation in various formats from your hook declarations. You can find more about hooks in the upcoming article on **Hook-Driven Development.**

## Declare strict types

Although the corresponding declaration is missing in most examples in the early articles of this series due to space constraints, I strongly recommend enabling PHP’s strict types check.

The declaration is made with `declare(strict_types=1)`. This should be done in every file of your plugin, and it must be this way to ensure that you fundamentally activate strict type checking. The `declare` statement is placed immediately after the opening PHP tag at the beginning of the file and before the namespace declaration:

```php
<?php

declare(strict_types=1);

namespace MKMyPlugin;

// use statements

// ...
```

Refer to this as well: [PSR-12: Declare Statements, Namespace, and Import Statements](https://www.php-fig.org/psr/psr-12/#3-declare-statements-namespace-and-import-statements)

### What does declare(strict_types=1) do exactly?

The statement `declare(strict_types=1)` enables strict type-checking in PHP. This leads to improved code quality and stability, as using strict type-checking allows errors to be detected more quickly and seen during development in the text editor.

This checks the so-called scalar types, which are `int`, `float`, `string`, and `bool`.

Let’s take a look at the following function as an example:

```php
function add(float $a, float $b): float
{
    return $a + $b;
}
```

Without enabling strict type checking, we can run the following without any issues and error messages:

```php
echo add('1.2', 2.4);
// -> 3.6
```

PHP silently converts the `string('1.2')` to `float(1.2)` and then returns the calculated `float(3.6)`.

Since this is a potential source of errors that can be difficult to find, we enable strict type checking in every PHP file with `declare(strict_types=1)`.

Let’s see this in the example:

```php
<?php

declare(strict_types=1);

function add(float $a, float $b): float
{
    return $a + $b;
}

echo add('1.2', 2.4);
```

This leads to the following error:

```
Fatal error: Uncaught TypeError: Argument 2 passed to add() must be of the type float, string given
```

Of course, this also applies to the return value:

```php
<?php

declare(strict_types=1);

function add(float $a, float $b): float
{
    return (string) $a + $b;
}

echo add(1.2, 2.4);
```

Here, we correctly pass two floats to the `add()` function. However, since return type hinting is enabled and we specify that the function should return a `float`, but at the same time cast the result of the addition as a `string`, we will encounter the following error message:

```
Fatal error: Uncaught TypeError: Return value of add() must be of the type float, string returned
```

**Important:** In order to use type checking correctly, your functions and methods must, of course, use type hinting! Type hinting refers to the type declarations, as seen, for example, in the declaration of the function above. There, we explicitly tell `$a` and `$b` that they should be of type `float`, and we indicate to the function that the return value should also be of type `float`:

```php
function add(float $a, float $b): float
```

**I strongly recommend equipping all PHP files of your plugin with `declare(strict_type=1)`!**

## Conclusion

In this article, you have learned about three ways to make your code cleaner and more maintainable. Two methods deal with the outer form of the code (_.editorconfig_ and documentation), and one with the quality and security of the actual code (Strict Types).
