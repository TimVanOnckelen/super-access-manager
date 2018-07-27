=== Super Access Manager ===
Contributors: thexerox
Tags: access, manager, restrited content, specific, user management, privacy, single user page, multi user page, page privacy
Donate link: paypal.me/xeweb
Requires at least: 4.0
Tested up to: 4.9.4
Requires PHP: 5.6
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Manage access to your post in a more advanced way. Allow specific users and roles to access your blog posts.

== Description ==
Manage access to your post in a more advanced way. Allow specific users and roles to access your blog posts.

== Installation ==
1. Go in to your WordPress Admin Panel
2. Navigate to Plugins-->Add New and click upload
3. Browse for the super-access-manager.zip file and click upload
4. Now click activate

== Frequently Asked Questions ==
= Can i setup access to pages or custom post types? =
You can manage access for every post type, custom or native.

= Can you control access on a user based level? =
You can control access to post for every specic user and on a role base.

== Screenshots ==
1. Select who can access your post on a user and role based level.

== Changelog ==
= 0.2.2 =
* Feature: Admins can enable the option to automaticly remove unaccesable posts from menus.

= 0.2 =
* Edit: Added new plugin structure
* Tweak: Add icon to list overview if post is protected
* Feature: Admins can now choose a page to show when page is not available

= 0.1.6.2 =
* Remove: Legacy updated removed
* Tweak: the "xeweb_sam-allowed_users" key is replaced by "txsc_allowed_users"
* Tweak: Admins can select to enable access management for diffrent post types

= 0.1.6.1 =
* Tweak: Added legacy updater

= 0.1.6 =
* Tweak: the "txsc_allowed_users" key is replaced by "xeweb_sam-allowed_users"
* Fix: Non available pages are filtert out category counters
* Feature: Allow categorys to hide/Show automaticlly when no posts available

= 0.1.5 =
* Fix: renamed classes, functions, ... to adjust wordpress plugin dir requirements

= 0.1.4 =
* Fix: Jquery is now loaded from the wordpress core
* Fix: Unneassery defines are removed
* Fix: Guest had no access on pages that hat rules before, now everyone has access when no rules is set

= 0.1.3 =
* Tweak: Filter out non accessable pages in category counters

= 0.1.2 =
* Base plugin relaese