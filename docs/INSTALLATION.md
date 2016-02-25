Installation
============

The only supported way of installing an extension, is using it as a composer dependency.

1. Add extension as a requirement

        "require": {
            "ecomdev/layout-compiler": "^1.0"
        }

2. Run `composer install`

3. Clean Magento Cache

4. Enable LayoutCompiler for a particular store view or the whole Magneto instance: 

    * **Admin Panel** `System -> Configuration -> Developer -> Template Settings -> EcomDev LayoutCompiler -> Yes`    
    * **N98-Magerun** `n98-magerun config:set dev/template/ecomdev_layoutcompiler 1`

