=== Import Social Events ===
Contributors: xylus,dharm1025
Donate link: http://xylusthemes.com/
Tags: facebook, events, import, calendar, facebook event, facebook import, facebook events, the events calendar, event import, events manager, import events, event, import event, my calendar, eventon, all in one event calendar, timely, event organiser, event management, event calendar, event manager, facebook-events-importer
Requires at least: 4.0
Requires PHP: 5.3
Tested up to: 5.2
Stable tag: 1.6.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Import Facebook events into your WordPress website and/or Event Calendar. Nice Display with shortcode & Event widget.


== Description ==

<h3>NOTICE:</h3>
>**You need below things to work Facebook Event Importing using API.**
>
>* Facebook app ([Here](http://docs.xylusthemes.com/docs/import-facebook-events/creating-facebook-application/) is how to create FB app)
>* Your site need to HTTPS (SSL certificate)
>* You need to mark events as interested or going on facebook to get imported
>


<h3>Import Facebook Events into WordPress :</h3>

Import Social Events allows you to import Facebook events into your WordPress site automatically. Automate your Event Marketing using Import Social Events plug-in. Import Events from Facebook Page, Facebook Event and all your events marked as Interested/Going.

You can schedule event import so it will be imported/synchronized automatically [Pro]

Using This plugin you can import facebook event into below listed leading Event Management Plug-ins, which allows you to run imports from facebook right from your dashboard.  Import Facebook Events bridges the gap between your website and your Facebook events making event management easier and it is one of the leading facebook events importers.

<h3>Import Facebook Events into</h3>

* [The Events Calendar](https://wordpress.org/plugins/the-events-calendar/)
* [Events manager](https://wordpress.org/plugins/events-manager/)
* [All-in-One Event Calendar](https://wordpress.org/plugins/all-in-one-event-calendar/)
* [Event Organiser](https://wordpress.org/plugins/event-organiser/)
* [EventON](https://codecanyon.net/item/eventon-wordpress-event-calendar-plugin/1211017)
* [My Calendar](https://wordpress.org/plugins/my-calendar/)
* [Event Espresso 4 (EE4)](https://wordpress.org/plugins/event-espresso-decaf/)
* In-built Events

You can use `[facebook_events]` for display in-built facebook events list.

<strong>Full short-code example:</strong> 
`[facebook_events col="2" posts_per_page="12" category="cat1,cat2" past_events="yes" order="desc" orderby="post_title" start_date="2017-12-25" end_date="2018-12-25" ]`

**Additional [PRO Add-on](https://xylusthemes.com/plugins/import-facebook-events/?utm_source=wprepo&utm_campaign=FacebookEvents&utm_medium=readme&utm_content=wprepo-readme) Features**

* Scheduled import events.
* Import events from the facebook page 
* Import events from the facebook group
* Import events from Facebook Event
* Import My Events will import all events you marked as Interested/Going 
* Sync events with facebook automatically
* Import multiple events easily
* Show events from facebook page into WordPress
* Upcoming Events widget
* Works with Visual Composer ( WPBackery Page Builder ). Support for more page builders is on the way :)

><strong>New All in one Event Import Tool!</strong><br>
>We’ve developed bulk event imports tool. This add-on service for The Events Calendar allows you import events from your favorite sources like Facebook, Meetup, Eventbrite, iCalendar, and ICS.
>
>[Check out WP Event Aggregator now](https://wordpress.org/plugins/wp-event-aggregator/).
>

 
><strong>Our Plugins for importing events!</strong>
> 
* [WP Event Aggregator](https://wordpress.org/plugins/wp-event-aggregator/)
* [Import Meetup Events](https://wordpress.org/plugins/import-meetup-events/)
* [Import Eventbrite Events](https://wordpress.org/plugins/import-eventbrite-events/)
* [WP Bulk Delete](https://wordpress.org/plugins/wp-bulk-delete/)
>

== Installation ==

= This plugin can be installed directly from your site. =

1. Log in and navigate to Plugins & Add New.
2. Type "Import Social Events" into the Search input and click the "Search" button.
3. Locate the "Import Social Events" in the list of search results and click "Install Now".
4. Click the "Activate Plugin" link at the bottom of the install screen.

= It can also be installed manually. =

1. Download the "Import Social Events" plugin from WordPress.org.
2. Unzip the package and move to your plugins directory.
3. Log into WordPress and navigate to the "Plugins" screen.
4. Locate "Import Social Events" in the list and click the "Activate" link.

== Screenshots ==

1. Events page using '[facebook_events posts_per_page="12"]' shortcode
2. Single Event page (Twenty Sixteen Theme).
3. Facebook Events Gutenberg Block
4. Import Facebook events by Event IDs.
5. Import Facebook events by Organization/Page ID (Pro).
6. Import Facebook events by .ics File.
7. Scheduled Facebook Imports (Pro).
8. Import History
9. Settings
10. Upcoming Facebook Events widget in the backend (Pro)
11. Upcoming Facebook Events widget in front-end with Event image(Pro)
12. Upcoming Facebook Events widget in front-end without Event image(Pro)

== Changelog ==
= 1.6.7 =
* IMPROVEMENT: Some Code Improvements.

= 1.6.6 =
* ADDED: Renamed Plugin
* ADDED: Accent Color Functionality
* IMPROVEMENT: Some Security Improvements.
* FIXED: some bug fixes.

= 1.6.5 =
* ADDED: Background import process for scheduled import
* IMPROVEMENT: Some Improvements.
* FIXED: some bug fixes.

= 1.6.4 =
* ADDED: My Pages Dropdown for import by User's pages
* ADDED: Timezone support for "All-in-One Events Calendar"
* IMPROVEMENT: Some Improvements.
* FIXED: some bug fixes.

= 1.6.3 =
* ADDED: Support for WP 5.0
* IMPROVEMENT: Some Improvements.
* FIXED: some bug fixes.

= 1.6.2 =
* ADDED: Facebook Events Gutenberg block.
* IMPROVEMENT: Some Improvements

= 1.6.1 =
* FIXED: bug in get facebook user access_token

= 1.6.0 =
* ADDED: Facebook Authorization, so import by facebook event ID possible now (event need to marked as interested or going is mandatory)
* IMPROVEMENT: Some Improvements
* FIXED: some bug fixes.

= 1.5.5 =
* ADDED: Import by .ics support (Facebook has the functionality to export your events to ics file so you can import your facebook event using this)
* FIXED: some bug fixes.

= 1.5.4 =
* IMPROVEMENT: Some Improvements
* FIXED: some bug fixes.

= 1.5.3 =
* IMPROVEMENT: Some Improvements
* FIXED: some bug fixes.

= 1.5.2 =
* IMPROVEMENT: Import by Page now working after Facebook's API restriction and Some other Improvements

= 1.5.1 =
* ADDED: Support for a Events Manager 5.9.1
* IMPROVEMENT: Some Code Improvements

= 1.5.0 =
* ADDED: Element for Visual Composer ( WPBackery Page Builder)
* ADDED: Template Overrides from Theme
* ADDED: Merged Pro & Free codebase, Introduced Pro as an add-on
* IMPROVEMENT: Some Improvements
* FIXED: some bug fixes.

= 1.4.0 =
* ADDED: Support for recurring facebook events
* IMPROVEMENT: Some Improvements
* FIXED: some bug fixes.

= 1.3.0 =
* ADDED: Import into Event Espresso 4 support.
* ADDED: Advanced Sync for Facebook (Pro).
* ADDED: Functionality for Edit Scheduled import (Pro).
* ADDED: Select tags functionality for TEC, EM and IFE (Pro).
* FIXED: some bug fixes.

= 1.2.0 =
* ADDED: Support import events from the Facebook group (Pro).
* ADDED: Now user can import events which are accessible from user’s profile (Pro).
* ADDED: Authorization option for import group events (Pro).
* FIXED: some bug fixes

= 1.1.5 =
* ADDED: more options in shortcode full shortcode is now like. [facebook_events col="2" posts_per_page="12" category="cat1,cat2" past_events="yes" order="desc" orderby="post_title" start_date="2017-12-25" end_date="2018-12-25" ]
* ADDED: Past Events display by add 'past_events="yes"' into shortcode.
* ADDED: Option for delete data on plugin uninstall
* IMPROVEMENTS: City, State and Country fields mapping to new version of EventON.
* FIXED: jQuery UI css conflict some plugin
* FIXED: TimeZone issue in “All in one Event Calendar” sometime imports wrong eventtime
* FIXED: some bug fixes

= 1.1.4 =
* FIXED: some bug fixes.

= 1.1.3 =
* ADDED: Upcoming Facebook Events Widget (Pro)
* IMPROVEMENTS: make date multilingual
* IMPROVEMENTS: in the event archive and single event details page.
* FIXED: some bug fixes.

= 1.1.2 =
* ADDED: option for disable inbuilt event management
* ADDED: option in shortcode for eventlist "category,col"
* FIXED: some bug fixes in ai1ec events import.

= 1.1.1 =
* FIXED: some bug fixes in events management.

= 1.1.0 =
* Added: in-built Event management system.
* Added: Import into My Calendar
* Added: Import into eventON
* Added: import into All-in-One Event Calendar
* Added: import into Event Organizer
* Added: Import history
* Added: support links.
* Improvements in scheduled imports
* Fixes: some bug fixes

= 1.0.1 =
* FIXED: some bug fixes

= 1.0.0 =
* Initial Version.
