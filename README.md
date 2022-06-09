Brought to you and maintained by [Trellis Commerce](https://trellis.co/) - A full-service eCommerce agency based in 
Boston, MA.

# Trellis Customer Force Login
This extension forces a customer to login before using the site. Admin configuration settings control which CMS 
pages and other URL patterns are available to customers to view without logging-in. Any customer that tries to 
access a restricted page without logging-in will be redirected to the login page.

## Installation
Follow the instructions below to install this extension using Composer.
```bash
composer require trellis/module-customer-force-login
bin/magento module:enable --clear-static-content Trellis_CustomerForceLogin
bin/magento setup:upgrade
bin/magento cache:flush
```

## Configuration

See configuration at Stores > Configuration > Trellis > Force Login.

* Enable Force Login - yes/no.
* Allowed Action Names - Comma separated list of full action names to allow. Include the route, controller, and action. Example: "catalog_product_view"
* Allowed CMS Pages - multiselect of all CMS pages.