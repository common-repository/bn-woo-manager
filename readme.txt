=== BN Woo Manager ===
Contributors: BN-KareM
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=HQ5X73WB7S5ZQ
Tags: WooCommerce, product edit, AJAX, unit price, shipping class, shipping method, shipping zones, country zones, csv export, bulk edit, tax Info, from prices
Requires at least: 4.1
Tested up to: 4.9
Stable tag: 1.17
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A WooCommerce AddOn for Shipping Management by Shipping Class & Country-Zones, Product-Management by AJAX Product Edit, Unit Price Management


== Description ==

= BN AJAX Product Edit =
Edit Products in table view. Editable fields: Product-Publish-Status, SKU, Shipping-Class, QTY per calculated Shipping Class, Stock-Manage, Stock-Amount, Prices, Unit-Price. AJAX-Update, Direct-Query-Function, UNDO-Function, LOG-Function, Group-Edit-Function, Filter Functions, CSV-Export-Function

= BN Shipping Zones =
Shipping Costs Calculation based on Shipping-Class and 6 specific Country-Zones & additional Postcode (Island) surcharge. (Note: Similar implemented in WC 3+, Part can be deactivated in the plugin-settings)

= BN Product QTY per calculated Shipping Class Function =
Charge Shipping Costs by Package / Box
Example: You send wine bottles still in a 6 box, no matter how many bottles are ordered. The shipping costs will be calculated per box.

= BN Unit Price =
Automatic Unit Price Calculation (e.g. € 3.20 / 100 g) - optionally you can add the package specification ( e.g. € 3.20 / 100 g (pkg. 500 g) )

= BN Price View =
Show starting prices (from prices) in the shop for variable products.
Show Tax-Info (e.g. incl. 20% VAT) after Shop-Price.

Notes:
Please save the LOG-File before update to an new Version, if needed and not backuped so far. (via ftp-download or click LOG-Button in AJAX-Product-Edit and save the file with the browser)

BN-Woo-Manager is WP Multisite ready. Please upgrade to at least WP 4.3.1, because of an critical bug in taxonomy.php, that flutes cron-option & slow-down the Main-Site and AJAX-Calls massively!

PHP 5.3 and a current browser required for some functions, tested up to PHP 7.1
Tested on WooCommerce 2.3 up to 3.3

Translations: EN, DE


== Installation ==

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'BN Woo Manager'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select 'bn-woo-manager.zip' from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download 'bn-woo-manager.zip'
2. Extract the 'bn-woo-manager' directory to your computer
3. Upload the 'bn-woo-manager' directory to the '/wp-content/plugins/' directory
4. Activate the plugin in the Plugin dashboard


== Frequently Asked Questions ==

= Where are the settings of plugin? =
When you install and activate the plugin you will find under the settings section of WooCommerce the Tab "BN Woo Manager"


== Screenshots ==

1. BN Shipping Zones
2. BN AJAX Product Edit
3. BN Unit Price & Tax-Info Shop-View


== Changelog ==

= 1.17 =
* Fix - LOG-File Delete Permission (only Admin) & local date in LOG-File

= 1.16 =
* Compatibility - WooCommerce 3+

= 1.15 =
* Compatibility - WP 4.7 & WooCommerce 2.6

= 1.14 =
* Fix - Secure LOG-File per htaccess and User-Rights on Apache Web-Server

= 1.13 =
* Fix - LOG-File path, LOG-Function now working

= 1.12 =
* Fix - Re-Add the LOG-File

= 1.11 =
* Fix - Small changes in description, delete the LOG-File

= 1.1 =
* First release on wordpress.org - Translation, Code Optimization & Docu

= 1.0 =
* Feature - BN Woo Manager Shipping Zones - Shipping Class & Postcode by 6 Country-Zones
* Feature - BN Woo Manager Product QTY per calculated Shipping Class Function - Charge Shipping Costs by Package / Box
* Feature - BN Woo Manager AJAX Product Edit - Direct Query, Undo, Group-Edit, LOG, CSV-Export
* Feature - BN Woo Manager Unit Price - Automatic calculation
* Feature - BN Woo Manager Price View variable Products


== Upgrade Notice ==

# Please save the AJAX Product Edit LOG-File to local disk before upgrade (if necessary)
