=== Import Social Events ===
Contributors: xylus, dharm1025, Rajat1192
Donate link: http://xylusthemes.com/
Tags: facebook, events, import, calendar, facebook event
Requires at least: 4.0
Requires PHP: 5.3
Tested up to: 6.9
Stable tag: 1.8.8
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

facebook import, facebook events, the events calendar, event import, events manager, import events, event, import event, my calendar, eventon, all in one event calendar, timely, event organiser, event management, event calendar, event manager, facebook-events-importer


<h3>Import Facebook Events into WordPress :</h3>

Import Social Events allows you to import Facebook events into your WordPress site automatically. Automate your Event Marketing using Import Social Events plug-in. Import Events from Facebook Page, Facebook Event and all your events marked as Interested/Going.

You can schedule event import so it will be imported/synchronized automatically [Pro]

Using This plugin you can import facebook event into below listed leading Event Management Plug-ins, which allows you to run imports from facebook right from your dashboard.  Import Facebook Events bridges the gap between your website and your Facebook events making event management easier and it is one of the leading facebook events importers.

<h3>Import Facebook Events into</h3>

* [The Events Calendar](https://wordpress.org/plugins/the-events-calendar/) - Supported with the latest version 6.0. 
* [Events manager](https://wordpress.org/plugins/events-manager/)
* [Event Organiser](https://wordpress.org/plugins/event-organiser/)
* [EventON](https://codecanyon.net/item/eventon-wordpress-event-calendar-plugin/1211017)
* [EventPrime](https://wordpress.org/plugins/eventprime-event-calendar-management/)
* [My Calendar](https://wordpress.org/plugins/my-calendar/)
* [Event Espresso](https://wordpress.org/plugins/event-espresso-decaf/)
* In-built Events

You can use `[facebook_events]` for display in-built facebook events list.

<strong>Full short-code example:</strong> 
**Full Shortcode Example:**  
`[facebook_events col=&quot;2&quot; posts_per_page=&quot;12&quot; category=&quot;cat1,cat2&quot; past_events=&quot;yes&quot; order=&quot;desc&quot; orderby=&quot;post_title&quot; start_date=&quot;2017-12-25&quot; end_date=&quot;2018-12-25&quot;]`

**Additional [PRO Add-on](https://xylusthemes.com/plugins/import-facebook-events/?utm_source=wprepo&utm_campaign=FacebookEvents&utm_medium=readme&utm_content=wprepo-readme) Features**

* Scheduled import events.
* Upcoming Events Widget On Elementor Editor
* Upcoming Events New Grid View Style
* Import events from the facebook page 
* Import events from Facebook Event
* Import My Events will import all events you marked as Interested/Going 
* Sync events with facebook automatically
* Import multiple events easily
* Show events from facebook page into WordPress
* Upcoming Events widget
* Works with WPBackery Page Builder. Support for more page builders is on the way :)

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
13. Plugin's Shortcode.
14. Events page using '[facebook_events layout="style2"]' shortcode
15. Events page using '[facebook_events layout="style3"]' shortcode
16. Events page using '[facebook_events layout="style4"]' shortcode

== Changelog ==

= 1.8.8 =
* ADDED: AJAX Pagination in shortcode
* ADDED: Support for Outlook Calendar event import using Outlook API. ( Pro )
* ADDED: Facebook Event Categories import  
* FIXED: Issue of images not importing in some cases.
* IMPROVEMENTS: Updated Facebook API version to v24.0

= 1.8.7 =
* ADDED: Integrated support for the EventPrime plugin.
* ADDED: Active/Pause button in schedule import. ( Pro )
* IMPROVEMENTS: Improved event fetching using the Facebook Page API.

= 1.8.6 =
* IMPROVEMENTS: Improved security and compatibility with WordPress coding standards.

= 1.8.5 =
* FIXED: Issue with incorrect text domain usage
* IMPROVEMENT: Added compatibility support for WordPress 6.8

= 1.8.4 =
* FIXED: TEC Event Date time bug.

= 1.8.3 =
* FIXED: Add new event link bug.

= 1.8.2 =
* ADDED: Dashboard page for better management.
* ADDED: Import Past Events option.
* ADDED: Move past events in trash option.
* IMPROVEMENTS: Facebook event organizer email in ical import.
* IMPROVEMENTS: Assign a default featured image for event without images.
* FIXED: Event Detailed page date issue.

= 1.8.1 =
* IMPROVEMENTS: Text changed and Added Feedback from the header

= 1.8.0 =
* ADDED: Default Event Feature image option( Grid View ).
* ADDED: Hyperlink in event description.
* REMOVE: Group ID option.
* REMOVE: facebook event link in the event description( iCal import ).

= 1.7.9 =
* ADDED: Copy-Paste button in the settings page.
* IMPROVEMENTS: Support for WP 6.6

= 1.7.8 =
* ADDED: New Events Grid View ( Style 4 )
* ADDED: Importing location with Google GeoLocation.
* FIXED: Timezone issue in iCal method.

= 1.7.7 =
* ADDED: Events Grid List New Layout.
* FIXED: Creating skip event venue in the events calendar
* FIXED: Bug related to duplicate event imports.
* IMPROVEMENTS: Reduced API calls in the iCal method.
* IMPROVEMENTS: Support for WP 6.5
* IMPROVEMENTS: Added Support for PHP 8.3 and some design and security fixes.

= 1.7.6 =
* IMPROVEMENTS: Updated Facebook API version to v18.0

= 1.7.5 =
* ADDED: ICal Data Validation.
* ADDED: Event Image compared by Its Name.
* FIXED: Javascript Exceptions.
* IMPROVEMENTS: EventOn Event Metadata.
* IMPROVEMENTS: Support for WP 6.4

= 1.7.4 =
* ADDED: All In One Event Calendar iCal support.
* ADDED: Time zone support in supported plugin.
* FIXED: Delete WP Cron multiple queue when you delete schedule delete.(PRO)
* IMPROVEMENTS: Added Support for PHP 8.2 and some design and security fixes

= 1.7.3 =
* ADDED: Reduced Facebook API Calls in iCal.
* ADDED: Online Event Location Support in suported plugin.
* ADDED: Google Map API key Option.
* ADDED: Responsive grid view style 1 Support
* ADDED: Skip Trashed Events Option
* ADDED: Time Format support in grid view style 2
* ADDED: Grig View Style 2 Option in Elementor Block (PRO)
* ADDED: Renew License button in the license section. (PRO)
* FIXED: iCal Event Organizer Email Address Format.
* FIXED: iCal Organizer Duplicate Issue in TEC.
* FIXED: iCal URL Saving Bug in Schedule import (PRO).
* FIXED: Delete WP Cron queue when you delete schedule delete.(PRO)
* IMPROVEMENTS: Security and GUI changes
* IMPROVEMENTS: Support for WP 6.3

= 1.7.2 =
* FIXED: Timezone Name issue.
* IMPROVEMENTS: Gutenberg Block
* IMPROVEMENTS: Support for WP 6.2

= 1.7.1 =
* ADDED: Events Grid List New Layout ( PRO )
* ADDED: Upcoming Events Elementor Widget ( PRO )
* ADDED: Support for don't update status & category during auto-update. (Pro)
* ADDED: Setting Page link in notice.
* ADDED: Schedule import Edit Source Data.
* ADDED: Admin Submenu Schedule Import, Import History, etc..
* FIXED: iCal Outlook import Timezone issue.
* FIXED: Duplicate Event issue in TEC.
* FIXED: Duplicate Event issue in My Calendar.
* IMPROVEMENTS: Updated Facebook API version to v15.0

= 1.7.0 =
* FIXED:  All Day events bug.
* FIXED:  Events manager location bug.
* FIXED:  Event organiser location bug.
* IMPROVEMENTS: Upgraded iCal library to v2.30
* IMPROVEMENTS: Support for PHP 8

= 1.6.20 =
* ADDED: Compatibility with The Events Calendar 6.0
* ADDED: "Upgrade to Pro"  Admin menu.
* ADDED: Taiwan language support.

= 1.6.19 =
* ADDED: Compatibility with The Events Calendar 6.0
* FIXED: iCal Facebook Organizer issues

= 1.6.18 =
* ADDED: Support and Docs link in plugin list page
* FIXED: iCal Image, Time, and Location issues
* FIXED: Dummy image url issue

= 1.6.17 =
* ADDED: Support for import image and location for ical

= 1.6.16 =
* ADDED: Considered private status in event already exists check
* ADDED: iCal import support to the plugin

= 1.6.15 =
* ADDED: Clear Import history button
* ADDED: Event source link field in create/edit event
* FIXED: Facebook Location Issue
* FIXED: WPBackery Page builder block issue (PRO)
* IMPROVEMENTS: Support for WP 5.8

= 1.6.14 =
* ADDED: shortcode page for help
* ADDED: New Google map embed
* FIXED: some typos
* IMPROVEMENTS: Support for WP 5.7

= 1.6.13 =
* ADDED: Optional Plugin deactivation Feedback
* FIXED: Time format related error
* FIXED: Event Espresso warning error
* IMPROVEMENTS: Made placehold.it load over https
* IMPROVEMENTS: Support for WP 5.6

= 1.6.12 =
* ADDED: French Translation (Thanks to [PiwEL](https://github.com/piwel))
* IMPROVEMENTS: support for WP 5.5

= 1.6.11 =
* ADDED: Option for author.
* ADDED: Option for time format.
* ADDED: Option for event slug.
* IMPROVEMENTS: Updated to Facebook API Version 7.0
* FIXED: few pages were not fetching events issue.

= 1.6.10 =
* ADDED: Option for Direct Event link to Facebook.
* IMPROVEMENT: FB API call Limit exceed issue.
* IMPROVEMENT: New design for Support and help page.
* FIXED: Event get removed before it happens (for some timezones)

= 1.6.9 =
* FIXED: "The link you followed has expired" Issue

= 1.6.8 =
* IMPROVEMENT: WordPress Coding Standards Improvements.

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
