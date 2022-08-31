# This package is abandoned and no longer maintained. For the replacement package see tweakwise/magento1-attribute-landing package instead.

---


## Installation
Install package using composer
```sh
composer require emico/magento1-attribute-landing
```
## Configuration
No specific configuration required

## Description / How to use
Configure landing pages under Cms > Attribute landing. An example would be Red dresses.   
To configure the page Red dresses one would create a new landing page with as follows

Title: Red Dresses.  
url path: red-dresses.  
For "Category ID" you use the Magento category id of the Category "Dresses".  
For "Search Attributes" you can use add filter to create a new record, fill in the attribute code of the color attribute and for value the attribute value of "red".  

The page is now available under url red-dresses and should contain all products from the selected category with color red.  

You can add additional filters to further customise the page. 

This plugin is compatible with Tweakwise!
