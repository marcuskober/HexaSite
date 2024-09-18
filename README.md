# HexaSite

An efficient static site generator built with PHP and Symfony, transforming Markdown files into static HTML using Twig templates.

**Note:** HexaSite is currently under heavy development. Features and functionalities may change, and usage is at your own risk.

## Overview

HexaSite provides a straightforward way to build static websites by converting Markdown files into HTML pages. Leveraging the power of PHP and the Symfony framework, it utilizes Twig for templating, allowing for flexible and customizable site designs.

## Features

- **Markdown to HTML Conversion**: Easily convert your Markdown files into static HTML pages.
- **Twig Templating**: Customize your site's appearance with powerful Twig templates.
- **Symfony Integration**: Built on Symfony for robust performance and scalability.
- **Lightweight and Easy to Use**: Minimal setup with a focus on simplicity.

## Installation

### Prerequisites

- PHP 8.3 or higher
- Composer

### Steps

1. **Clone the Repository**

   ```bash
   git clone https://github.com/marcuskober/HexaSite.git
   cd hexasite
   ```

2. **Install Dependencies**

   ```bash
   composer install
   ```

3. **Prepare Your Content**

   Place your Markdown files in the `content` directory.

## Usage

Generate your static site by running:

```bash
symfony console static:build
```

The generated HTML files will be placed in the `build` directory.

## Customization

- **Templates**: Modify the Twig templates in the `templates` directory to change the site's layout and design.

## Roadmap

- [x] First prototype for generating html files from markdown files
- [ ] Internal linking
- [ ] Asset management
- [ ] Menus
- [ ] Article list (for blog pages)
- [ ] Multi language support

## Contributing

Contributions are welcome! Please open an issue or submit a pull request for any enhancements or bug fixes.

## License

This project is licensed under the [MIT License](LICENSE).

## Contact

For questions or support, please contact [hello@marcuskober.de](mailto:hello@marcuskober.de).