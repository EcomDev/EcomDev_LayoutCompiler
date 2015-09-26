Layout changes are not shown on the frontend
============================================

In case if you don't see changes in layout on the frontend, this section may help you.

Refresh Cache in Production
---------------------------

Refresh layout cache in the admin panel: `System -> Cache Management -> Layout Cache -> Refresh`. 

This will force re-validation of the compiled layout file checksum and your changes will be updated on the next frontend page visit.
 
Do not forget also to invalidate php OpCode cache, as compiled layout structure is cached by it.

Disable Cache in Development
----------------------------

In development mode it is recommended to disable `Layout Cache`, so compiler will re-validate layout checksum every time you view a page.

There is a small performance drawback in this case, as compiled php files are re-validated on every request. It might take from 50ms till 1s depending on IO configuration.
 

Flush All Compiled Files
------------------------

Sometimes you will need to flush all the compiled layout structure, as you customize layout parser or just want to be sure you have latest compiled files. You can simply remove all the files via a single command:

`rm -rf var/ecomdev/layoutcompiler`

