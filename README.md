mage_tools
==========

Some terminal shortcuts to work with Magento

Functions
---------

### mage_clear_cache
Removes all the content in var/cache/mage-*

### mage_watch_system_log
Watch the Magento system log file (var/log/system.log)

### mage_watch_exception_log
Watch the Magento exception log file (var/log/exception.log)

### mage_fix_file_permissions
Set the file permissions

### mage_template_copy_file
Usage: mage_template_copy_file source/file.phtml destination-layout

Change to the Magento design frontend or backend folder and call mage_template_copy_file to copy the source file into the same folder inside the given layout.
i.e.:
     
     mage_template_copy_file checkout/cart/crosssell.phtml meinfoto