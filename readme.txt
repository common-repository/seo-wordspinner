=== SEO WordSpinner ===
Contributors: katzwebdesign
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=billing%40katzwebdesign%2enet&item_name=SEO+WordSpinner+Plugin&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8
Tags: google, spin, content, random, word, generator, seo, spinner,title tags, titles, categories, wordspinner, content spinner, php spinner, jetspinner, webspinner, article spinner, unique content, article marketing
Requires at least: 2.8.0
Tested up to: 3.0.3
Stable tag: trunk

Improve SEO by 'spinning' website content to create SEO-friendly variations of articles. Avoid duplicate content issues!

== Description ==

> <strong>This plugin is temporarily unavailable from WordPress.org</strong>. Please visit the official <strong><a href="http://www.seodenver.com/seo-wordspinner/">SEO Wordspinner Page</a></strong> to download.


SEO Wordspinner is an SEO plugin for Wordpress that can be used to 'spin' the content in your blog. Spinning content allows you to create SEO-friendly variations of articles. The idea is to avoid content such as excerpts or page titles being seen as duplicate content when seen on various archive pages. 

### New in Version 2.0

* __Nested spins__ - spin text inside spun text; up to five levels deep. This is the feature you've been waiting for!
* __Spin entire output__ - spin the contents of the <em>entire page</em> at once.
* __Exclude `HTML` tags__ - You can choose not to spin tags such as `<style>`, `<script>`, `<code>` and `<pre>`. This is very important for inline code.
* __Works with WP Multisite__ - this version of WordSpinner has been tested on a Multisite WordPress installation. (<a href="http://codex.wordpress.org/Create_A_Network" rel="nofollow">learn more about Multisite</a>)

#### Plugin Notes:

* This functionality is very powerful. Please use responsibly!
* Websites charge big bucks for this functionality (<a href="http://sn.im/content-spinner-search" rel="nofollow">do a search for "content spinner"</a>, and you'll see what I mean). If you're grateful this is free, __<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=billing%40katzwebdesign%2enet&item_name=SEO+WordSpinner+Plugin&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8" rel="nofollow">please donate</a>__!
* Please report bugs, ideas, and leave comments on the <a href="http://www.seodenver.com/gravity-forms-addons/" rel="nofollow">official plugin page</a>.

#### An example content spin:

`This is <em>{an example|a demonstration|a demo|a test}</em> of the powers of content spinning`<br />

####Would produce the following spun variations:

* This is <em>an example</em> of the powers of content spinning
* This is <em>a demonstration</em> of the powers of content spinning
* This is <em>a demo</em> of the powers of content spinning
* This is <em>a test</em> of the powers of content spinning

== Installation ==

* Upload the plugin
* Activate
* Configure in Settings -> SEO WordSpinner
* Start adding content spins in your posts and pages

== Frequently Asked Questions ==

= How can I use this as a PHP function? =

Once the plugin is activated, you can use the `seo_spin()` and `get_seo_spin()` functions, such as: `<?php $example = "spin {this|that}"; echo get_seo_spin($example); ?>`, which would output either "spin this" or "spin that".

= Does this plugin support nested content spins? =

__Yes!__ You can spin content inside of spun content to create ##very unique content##. `{Example {One|Two|Three}|Sample {This|That|The Other}}`.

= I need more information! =

When you install the plugin and go to the plugin options page (Settings -> SEO WordSpinner), there is additional information available there.

= What is the plugin license? =

This plugin is released under a GPL license.

== Screenshots ==

1. The SEO WordSpinner plugin configuration screen.

== Changelog ==

= 2.0.4 =
* Fixed a potential issue: if a website defines a fixed `mt_srand()` elsewhere, randomization does not work. Now it does.
* Improved code for empty spins where there were multiple next to each other "I like {this|} pudding" could say "I like pudding" or "I like this pudding".
* Added Custom Field configuration option for per-page enabling and disabling SEO WordSpinner. Add a custom field `SEOSpinEnabled` with a value of "false" or "true" to turn on and off on a per-page basis.
* Added a `get_seo_spin_enabled` filter for more precise enabling and disabling of the WordSpinner.

= 2.0.3 =
* Added filters so that trackback titles are spun when using the "Spin Entire Output" option. <em>The last update for a while - sorry folks.</em>

= 2.0.2 = 
* <strong>Critical update:</strong> - Fixed exclude tags functionality.

= 2.0.1 = 
* Forgot to add the javascript for better Settings page handling.
* Lots of functionality added in 2.0!
	* __Nested spins__ - spin text inside spun text; up to five levels deep. This is the feature you've been waiting for!
	* __Spin entire output__ - spin the contents of the <em>entire page</em> at once.
	* __Exclude `HTML` tags__ - You can choose not to spin tags such as `<style>`, `<script>`, `<code>` and `<pre>`. This is very important for inline code.
	* __Works with WP Multisite__ - this version of WordSpinner has been tested on a Multisite WordPress installation. (<a href="http://codex.wordpress.org/Create_A_Network" rel="nofollow">learn more about Multisite</a>)

= 2.0 =
Lots of functionality added:

* __Nested spins__ - spin text inside spun text; up to five levels deep. This is the feature you've been waiting for!
* __Spin entire output__ - spin the contents of the <em>entire page</em> at once.
* __Exclude `HTML` tags__ - You can choose not to spin tags such as `<style>`, `<script>`, `<code>` and `<pre>`. This is very important for inline code.
* __Works with WP Multisite__ - this version of WordSpinner has been tested on a Multisite WordPress installation. (<a href="http://codex.wordpress.org/Create_A_Network" rel="nofollow">learn more about Multisite</a>)

= 1.2.2 =
* Fixed "Spin Text" widget error, should be functional now.

= 1.2.1 = 
* Updated with additional GPL license information

= 1.2 = 
* Added spinning capabilities for category names and page names (in category list and page lists as well)
* Added spinning capabilities to the blog name.

= 1.1 = 
* Updated when to call filters (from `wp` to `init`) so that spun titles and content would apply to trackbacks and in the admin

= 1.0 =
* SEO WordSpinner plugin is released.

== Upgrade Notice ==

= 2.0.4 =
* Fixed a potential issue: if a website defines a fixed `mt_srand()` elsewhere, randomization does not work. Now it does.
* Improved code for empty spins where there were multiple next to each other "I like {this|} pudding" could say "I like pudding" or "I like this pudding".
* Added Custom Field configuration option for per-page enabling and disabling SEO WordSpinner. Add a custom field `SEOSpinEnabled` with a value of "false" or "true" to turn on and off on a per-page basis.
* Added a `get_seo_spin_enabled` filter for more precise enabling and disabling of the WordSpinner.

= 2.0.3 =
* Added filters so that trackback titles are spun when using the "Spin Entire Output" option.

= 2.0.2 = 
* <strong>Critical update:</strong> - Fixed exclude tags functionality.

= 2.0.1 = 
* Forgot to add the javascript for better Settings page handling.

= 2.0 =
* Should fix issues with most fatal errors.
* Tons of good stuff added (check the Changelog)

= 1.2.2 =
* Fixed "Spin Text" widget error, should be functional now.

= 1.0 =
No upgrades, just the first installation!