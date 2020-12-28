=== Proxy & VPN Blocker ===
Contributors: rickstermuk
Tags: security, proxy blocker, vpn blocker, proxy, vpn, proxycheck, anti spam, spam, anti-spam, Tor, Anti-Tor, Tor block
Donate link: https://pvb.ricksterm.net/donate
Requires at least: 4.9
Tested up to: 5.6
Requires PHP: 5.6
Stable tag: 1.7.2
License: GPLv2

Blocks Proxies, VPN's, select Countries, IP's, Ranges & ASN's accessing your site login or commenting on pages & posts using the proxycheck.io API.

== Description ==
= Proxy & VPN Blocker In Brief =
Using the [proxycheck.io](https://proxycheck.io) API this plugin will prevent Proxies, Tor, VPN's, IP Addresses, Ranges or ASN's & select Countries from accessing your WordPress Login, Registration pages, Select Pages and Posts (or the whole site!), and also prevent them from making comments on your pages and posts. This will also help to prevent spammers as many of them use Proxies to hide their true location.

= Main Blocking Features =
Below is a list of the main blocking features supported by this plugin.

* Block Proxies, SOCKS4/5, The Onion Router (TOR), Web Proxies and Compromised Servers.
* Optionally block VPN's.
* Support for Cloudflare.
* TLS Support for secure communication with the proxycheck.io API.
* Block select Countries and/or Continents by selecting them in a list - optionally make this list a whitelist instead.
* Caching of known good IP addresses for half an hour (configurable between ten and 240 minutes) after the first check to save on repeat queries (and slowing down good visitors).
* Optional blocking based on IP Risk Score functionality provided by the proxycheck.io API.

> Note: By default blocking happens on Login, Registration, WP-Admin area, posting comments, and pingbacks, but you can extend this to blocking on any specified page or even on a specific Div class.

= Added Extras =
Proxy & VPN Blocker has gone much further than just providing the basic API features of proxycheck.io. It has country blocking baked right in, an API Key statistics page and proxycheck.io Whitelist and Blacklist manipulation right from your WordPress Dashboard for ease of use, providing the Dashboard API is enabled on your proxycheck.io account. This is so you can manage most things from within WordPress and don't have to log in to proxycheck.io.

= Customisation =
* You can specify a list of pages and posts to protect in addition to what is protected by default.
* You can select a specific page on your site as the Blocked page rather than the default message page.
* You can specify the blocked message shown if a custom Block page isn't specified.
* You can specify a custom tag text that will be shown instead of the url the query was made from, in your positive detection log.

= The proxycheck.io API =
This plugin can be used without a [proxycheck.io](https://proxycheck.io) API key, however it would be limited to 100 daily queries. You can get a free API key from proxycheck.io that allows for 1000 free daily queries, ideal for small WordPress sites!

There are paid higher query options available, Please see below how the free and paid API options work.

* Free Users without an API Key = 100 Daily Queries.
* Free Users with an API Key = 1,000 Daily Queries.
* Paid Users with an API Key = 10,000 to 10.24 Million+ Daily Queries.

You are not limited to using your API key on one site or application.

= Caching Plugin Notice =
If your WordPress site is using a caching plugin (WP Rocket, WP Super Cache etc) Blocking on specific pages, posts or the option to block on all pages may not function due to how caching plugins work.

= Privacy Notice =
This plugin is designed to work with the proxycheck.io API and by extension of this, the IP addresses of your site visitors are sent to the API to be checked. No other user identifiable information is transmitted. Please refer to the proxycheck.io [privacy notice](https://proxycheck.io/privacy) and [GDPR Compliance](https://proxycheck.io/gdpr) for further information. The plugin developer does not have access to information that identifies your website users.

= Disclaimer =
This plugin is *not* made by proxycheck.io despite being recommended by the company, if you need support with the Proxy & VPN Blocker plugin please use the WordPress Support page for this plugin and not proxycheck.io support on their website, unless you have a query relating to the proxycheck.io API, service or your account. Likewise the plugin developer does not provide support for issues relating to your proxycheck.io account or the API. The plugin developer and proxycheck.io are not the same entity. Logo used with express permission.

= Supporting The Plugin =
Coding a plugin is a lot of hard work and any support from plugin users like you is very much welcomed. Contributions will help with encouragement to continually improve the plugin. Feedback and feature ideas are welcomed too!

== Installation ==
Installing "Proxy & VPN Blocker" can be done either by searching for "Proxy & VPN Blocker" via the "Plugins > Add New" screen in your WordPress dashboard, or by using the following steps:

1. Download the plugin via WordPress.org
2. Upload the ZIP file through the 'Plugins > Add New > Upload' screen in your WordPress dashboard
3. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==
=What is proxycheck.io?=

Proxycheck.io is a simple, accurate and reliable API for the detection and blocking of people using Proxies, Tor & VPN servers.

=Blocking Proxies and VPN's on all pages?=

Although this plugin has an option to block Proxies & VPN's on all pages, this option is not generally recommended due to significantly higher query usage, but was added on user request.

It is important to note that if you are using a WordPress caching plugin (eg WP Super Cache, WP Rocket, W3 Total Cache and many others), these will prevent the Proxy or VPN from being blocked if you are using 'Block on all pages' as the caching plugin will likely serve the visitor a static cached version of your website pages. As the cached pages are served by the caching plugin in static HTML, the code for proxy detection will not run on these cached pages. This won't affect the normal protections this plugin provides for Log-in, Registration and commenting.

=I accidently locked myself out by blocking my own country/continent, what do I do?=
The fix is simple, upload a .txt file called disablepvb.txt to your wordpress root directory, PVB looks for this file when the proxy and VPN checks are made, if the file exists it will prevent the plugin from contacting the proxycheck.io API. You will now be able to log in and remove your country/continent in the PVB Settings.

Remember: If you ever have to do this, delete the disablepvb.txt file after you are done! If you don't remove it, the plugin wont be protecting your site.

== Screenshots ==
1. Options UI.
2. Default Error message shown when a proxy or vpn is detected, this can be changed in the options.
3. Error message example if you opt to use a page within your site's theme.
4. API Key Stats page.
5. Whitelist manipulation page. The blacklist page looks similar to this.

== Changelog ==

= 1.7.2 2020-12-27 =
* Fix for potential errors and issues if your proxycheck API query allowance was exhausted.
* Fix for UI issue with the percentage bar on the API Key Statistics page if queries are over 100% (Burst token in use or queries exhausted).
* Added an option for Proxy & VPN Blocker to send an email to the defined WordPress Admin Email notifying you of a denied status message received from proxycheck.io when making an API Query. This is useful to see if you have gone over used queries or if you have been blocked due to exceeding per second request rate limits or you have been banned.

= 1.7.1 2020-12-16 =
* Fix for php error on some older/outdated php versions (7.0.xx, 7.1.xx, 7.2.xx).

= 1.7.0 2020-12-16 =
* Added option that allows redirecting blocked visitors to an external URL (alternative to redirecting to a page on your site or the default error page with text).
* Updated amcharts for statistics page to version 4.
* Renamed Blacklist/Whitelist pages to Blacklist Editor/Whitelist Editor.
* Minor improvements made to code.
* Renamed "Access Denied Message or Custom Blocked Page" to "Blocked Visitor Action" in Settings.

= 1.6.8 2020-05-22 =
* Fix for Country Whitelist option and grouped this option with the country list near the bottom in the section titled "Block or Allow Countries/Continents" with altered explanation text.

= 1.6.7 2020-05-20 =
* Added an advanced section option to make the country list a list of whitelisted countries instead of blacklisted.
* Removed the Anti-Clickbombing feature as it no longer works as intended.
* Updated text on the Plugin settings page.

= 1.6.6 2020-05-09 =
* Reverted to 1.5.x Cloudflare code due to issues with some server configurations causing IP validation to fail.

= 1.6.5 2020-05-08 =
* Fixed a rare issue that could happen depending on some hosting PHP configurations which may have caused Cloudflare's IP ranges to fail to be acquired.
* Altered slightly how blocking on pages/posts is processed.
* Fixed a visual bug on API Key Statistics Page when API Dashboard Access was disabled in the proxycheck.io account.

= 1.6.4 2020-05-04 =
* An issue was discovered that prevented some wp-cron or admin-ajax tasks from running, correct operation is to ignore wp-cron & admin-ajax, this affected other plugins tasks if they communicate with remote servers due to datacenter IP ranges being detected as VPNs by proxycheck.io API, this has been corrected.

= 1.6.3 2020-05-03 =
* Further refinements to "Block on Entire Site".

= 1.6.2 2020-05-03 =
* An issue was discovered with the "Block on Entire Site" feature (formerly "Block on All Pages") which caused it to not function, this has been corrected.

= 1.6.1 2020-05-02 =
* Fix for database update script.

= 1.6.0 2020-05-01 =
* Implemented proxycheck.io Risk Score functionality.
* Fixed an issue that may have caused blocking on specific Posts & Pages not to work if permalinks were the WordPress default. Please check your selected pages/posts in PVB Settings after updating!
* Pagination on Recent Positive Detections log on API Key statistics page was fixed due to an incrementation issue.
* API Key statistics page Recent Detections log now displays time in the same way as proxycheck.io.
* If Cloudflare is in use and turned on in settings, $_SERVER['REMOTE_ADDR'] is now validated against a list of Cloudflare IP address ranges for additional security.
* If Cloudflare is determined to be in use but the Cloudflare setting is not enabled in PVB settings, there will now be a warning message displayed in admin.
* Known Good IP Cache is now configurable between 10 minutes and 4hrs (previously fixed at 30 minutes).
* Updated plugin settings UI including the order settings are listed in and with groupings for better clarity.
* Refactored many parts of the code.

= 1.5.4 2020-02-02 =
* Implemented Continent Blocking.
* Some minor code improvements.

= 1.5.3 2019-11-06 =
* Corrected an issue that could cause higher than normal known good IP cache misses for some users.

= 1.5.2 2019-10-25 =
* Fix for an issue that could potentially cause conflicting PVB settings CSS styles and other plugins settings CSS styles.
* Implemented a unique settings key feature - When the settings are saved a new unique key is generated, this is saved alongside cached known good IP's and ensures good IP's within the last 30 minutes are checked again if you update the settings.
* Settings CSS updated due to some changes in WordPress 5.3.
* Chosen.js library updated.

= 1.5.1 2019-04-13 =
* Bug fix for PHP Error on statistics page if API key is not specified.
* Updated readme and some text on the plugin settings page.

= 1.5.0 2019-03-31 =
* Added ability to block on specified posts.
* Updated text descriptions for some settings to make it more clear as to what the setting does.
* Improved performance of API Key Statistics Page and new statistics table with pagination.
* Code cleanup and refinements.

= 1.4.0 2018-08-15 =
* added option to allow blocking on specified pages (in addition to the core protection of wordpress, registration, login, admin area, commenting etc).
* added proxycheck.io blacklist & whitelist control to the plugin settings
* added option to redirect blocked users to your own custom page.
* Added a rudimentary Anti Click-Bombing feature to enable the protection of ads or other content if you wrap it in the html div class 'pvb-protect-div'. Not compatible with Block on all pages or if a page caching plugin is used.
* Fixed an issue where WordPress cron tasks could potentially be blocked from running on certain hosts which are detected as a Proxy or VPN.
* Removed the Custom Tag Switch (Checkbox) from the plugin options page, status of custom tagging is now determined by whether the Custom Tag field contains any text.
* Updated Plugin logo and admin panel UI.

= 1.3.2 2018-06-27 =
* Fixed an issue where the Cloudflare option was the reverse on servers that don't support X-Forwarded-For headers used by Cloudflare - If you had the plugin Cloudflare switch set to "on" on such servers it was doing the opposite and not supporting Cloudflare and "off" was the Cloudflare supporting state. If you had the Cloudflare option set to "off" to fix this issue while using Cloudflare, please set it back to "on" after this update.

= 1.3.1 2018-05-16 =
* fixed an issue where the plugin was not setting its new version number to the database on update.
* fixed a minor issue with percentages not being rounded on the information page.

= 1.3.0 2018-05-15 =
* Added the ability to block entire countries if desired, this uses the proxycheck.io data to determine location of the visitor, but note that this will not show up in your statistics due to this check being done within the plugin.
* Altered the API Key Information page to display key, proxycheck.io plan, Queries remaining today, and 30 days stats in a line graph.
* Fixed a minor issue effecting PHP versions prior to v5.6 on the API key Information page, although the plugin is made for PHP v5.6+ this fixes the bug on prior versions on this page.

= 1.2.1 2018-05-01 =
* Known good IP addresses will now get cached for 30 minutes this is to reduce API Queries and site latency on rechecks for legitimate users. Proxy and VPN IP's will not get cached and will be rechecked every time they attempt to visit protected pages.
* Fixed caching issue where Denied pages could potentially be served to other people using the site when 'block on all pages' is enabled while using a Cache Plugin.
* Added warning about Block On All Pages and the use of page caching plugins, please see the FAQ.
* Improved the styling of the settings pages further.

= 1.2.0 2018-04-17 =
* Added IP country to stats page
* Extended stats page to show positive detections from the last 100 queries instead of 50
* Added toggle to block Proxies/VPN's on all pages (Note this is at the expense of significantly higher query usage)
* Added slider that enables setting the amount of days from 1 to 60 (default 7) that an IP will be checked for Proxy/VPN history so that you can set your level of security.

= 1.1.0 2018-01-12 =
* Updated plugin to support the new proxycheck.io v2 API
* Fixed a bug that caused an error when enabling the Cloudflare option but not having Cloudflare enabled for the domain
* Improved plugin options panel UI
* Added a toggle to disable querying the proxycheck.io API without having to deactivate the plugin
* Added a API Key statistics page that uses data from the proxycheck.io dashboard API if you specify an API Key (This does not use your queries!)

= 1.0.2 2017-12-28 =
* Added support for WooCommerce Login Forms for aesthetic reasons
* improved access denied page
* removed unnecessary scripts

= 1.0.1 2017-12-25 =
* Fixed an issue with site login
* Switched from cURL to official WordPress HTTP API for querying the proxycheck.io API

= 1.0 2017-12-22 =
* Initial release