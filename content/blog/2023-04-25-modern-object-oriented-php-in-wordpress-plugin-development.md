---
title: Modern object-oriented PHP in WordPress plugin development
summary: 'In this article series, I will present methods for building WordPress plugins in a modern and object-oriented way. The focus will be on code quality, reusability, maintainability, and extensibility of plugins and plugin code.'
date: 2023-04-25
categories: ["Modern PHP in WP plugins"]
tags: ['plugin development']
author: "Marcus Kober"
slug: "modern-object-oriented-php-in-wordpress-plugin-development"
lang: en
---

{{< toc >}}

**In this article series, I will present methods for building WordPress plugins in a modern and object-oriented way. The focus will be on code quality, reusability, maintainability, and extensibility of plugins and plugin code.**

In this first article, I would like to briefly introduce myself and explain why I'm writing this series. I also want to highlight when these approaches and methods are unsuitable and provide additional considerations when considering modern PHP and WordPress plugins.

My Name is Marcus and at the time of publication, I'm 47 years old, living in Cologne with my wife and two kids. I have been professionally developing websites and web apps using PHP since 1999, and since 2005 using WordPress too. During this time, I have worked on large-scale web application projects using Yii2, Symfony, and Laravel. Furthermore, I have developed over 150 custom WordPress themes and more than 50 different plugins during this time. These themes and plugins were mainly commissioned by clients, including some very extensive plugins (with over 100 PHP files) that are installed on many client sites. Since then, I have enjoyed thinking and learning about plugin architecture and software architecture in general, always striving to improve my code.

There are many good tutorials and articles on plugin development available on the internet, but most of them end just where it becomes interesting for developing more complex and larger plugins. Unfortunately, there are very few resources available on code organization, file structure, and advanced PHP programming in WordPress plugins.

That's why I developed the desire to gather all the knowledge I have acquired so far. In one place and as comprehensively as possible.

## Modern PHP and WordPress - do they even fit together?

WordPress itself is not really built with object-oriented programming and does not use modern PHP due to backward compatibility. However, this does not mean that new plugins must follow this philosophy. If you are developing plugins for the [WordPress Plugin Repository](https://wordpress.org/plugins/) and want to reach the widest possible audience of users, it may be advisable to avoid certain PHP features that were introduced later. It is also essential to explicitly specify the PHP version in the [plugin header](https://developer.wordpress.org/plugins/plugin-basics/header-requirements/) so that plugin activation is prevented on WordPress installations with incompatible PHP versions. For example: `Requires PHP: 8.1`.

So yes, if you know what you're doing, you can use modern PHP in your plugins. Especially if you have control over who receives your plugins (for example, in commissioned work) or even manage the hosting for your clients, there is absolutely nothing against it.

## Brillant idea, from now on I will use these advanced techniques and object orientation for all my plugins!

No, please don't. Not every plugin needs those things. Especially smaller plugins with a few lines of code and just a few functions are better developed without these advanced methods. It's not necessary to generate more overhead as needed for a project. Plugins that register only two or three hooks, haven't to use autoloading, PHP attributes, and dependency injection (more on this in the following articles).

## Why the focus on PHP when WordPress is moving more and more towards JavaScript / React?

Although I am a big fan of Gutenberg and block themes, and developing custom blocks is one of my favorite topics, PHP remains relevant for extensive plugins. Therefore, this article series is also of long-term importance.

I may also write some articles on block development after this series. However, for now, my focus is on how modern PHP techniques can be effectively used in WordPress plugins.

## Preview

In this article series, the focus is on efficiently using hooks and implementing them with modern PHP. Through many practical code examples, we will explore topics such as dependency injection, its importance, and implementation in WordPress plugins, as well as methods for unit testing and its implementation in the WordPress context.

I will strive to regularly publish new articles and - as far as my job and family allow - to already have many articles completed before the first article goes online. 

## Who is this article series for?

You should already have some experience with WordPress development and want to take the next step. You should also have heard of object-oriented programming before.
