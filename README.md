# EcomDev_LayoutCompiler
[![Dev Build Status](https://travis-ci.org/EcomDev/EcomDev_LayoutCompiler.svg?branch=develop)](https://travis-ci.org/EcomDev/EcomDev_LayoutCompiler?branch=develop) [![Develop Coverage Status](https://coveralls.io/repos/EcomDev/EcomDev_LayoutCompiler/badge.svg?branch=develop)](https://coveralls.io/r/EcomDev/EcomDev_LayoutCompiler?branch=develop)

Layout Compiler for Magento

Transforms recursive layout xml files into simple simple one level php code, that can be cached by opcode cache

## Requirements
* Magento 1.x (1.7.0.0 or later)
* PHP 5.4 or later
* (Optional) PHP OpCache (for better performance)

## Installation

The only supported way of installing an extension, is using it as a composer dependency.

1. Add extension and firegento repositories in composer  

         "repositories": [
            {
              "type": "composer",
              "url": "http://packages.firegento.com"
            },
            {
              "type": "vcs",
              "url": "git@github.com:EcomDev/EcomDev_LayoutCompiler.git"
            }
        ]

2. Add extension as a requirement (currently in develop stability)

        "require": {
            "ecomdev/layout-compiler": "dev-develop"
        }

3. Run `composer install`
