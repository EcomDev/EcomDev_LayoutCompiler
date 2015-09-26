Installation
============

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

4. Clean Magento Cache

5. Enable LayoutCompiler for a particular store view or the whole Magneto instance: 

    * **Admin Panel** `System -> Configuration -> Developer -> Template Settings -> EcomDev LayoutCompiler -> Yes`    
    * **N98-Magerun** `n98-magerun config:set dev/template/ecomdev_layoutcompiler 1`

