=== League Table - WordPress Table Plugin ===
Contributors: DAEXT
Tags: table, table builder, tables, datatable, standings
Donate link: https://daext.com
Requires at least: 4.6
Tested up to: 6.6.2
Requires PHP: 7.2
Stable tag: 1.17
License: GPLv3

League Table is a table plugin that you can use to create sortable and responsive tables on your WordPress website.

== Description ==
League Table is a table plugin that you can use to create sortable and responsive tables on your WordPress website.

Please note that this plugin is the lite version of [League Table](https://daext.com/league-table/), a table WordPress plugin that we distribute on Envato Market since 2014.

### Spreadsheet editor

In the main plugin menu, we have embedded a spreadsheet editor generated with Handsontable. Thanks to this feature, you can move tables available in common spreadsheet editors like Excel, Google Sheets, OpenOffice Calc. to WordPress and vice versa.

### Exceptional customizability

Use the included customization options to create the perfect table for the context.

#### Table layout

This table plugin produces tables with automatic or fixed [table layouts](https://developer.mozilla.org/en-US/docs/Web/CSS/table-layout). You can also define the table width and the width of every single column if needed.

#### Table scrollbars

You can optionally enable the horizontal or vertical scroll bars. We recommend the use of this feature with tables that includes a high amount of data.

#### Scalable font size

The plugin allows you to define the font size of the text in the header and body cells. The selected font size value is used to automatically adapts the cell paddings and other table parameters to generate tables with perfect proportions and optimal readability.

#### Table margin

Set the exact margin of the table to fit the table in your layout or create a vertical rhythm.

#### Table header

Display or hide the table header based on the type of data that you want to represent.

#### Custom typography

Set a custom font family, font weight, and font style for the text in the header and body cells.

#### Custom colors

You can individually define the colors of the following table elements with a handy color picker:

* Header Background Color
* Header Font Color
* Header Link Color
* Header Border Color
* Rows Border Color

#### Striped table

Improve the readability of your table by creating tables with striped rows.

The following striped table options are available:

* Even Rows Background Color
* Odd Rows Background Color
* Even Rows Font Color
* Odd Rows Font Color
* Even Rows Link Color
* Odd Rows Link Color

#### Text alignment

With the **Alignment section**, you can easily define a custom text alignment for table rows or columns.

#### Responsive font size

The plugin allows you to scale the font size based on the viewport width. To achieve this, visit the **Responsive** section and define the breakpoints and the corresponding font sizes.

#### Include images in the table cells

Easily add images in the table cells with the WordPress image uploader. Use this feature to display the flags of sports teams, brand logos, achievements icons, people faces, and more.

#### Automatically generate the "Position" column

The plugin can optionally generate a column that indicates the position of the entity associated with the row. This particular column is created based on your defined sorting criteria. You can make this column the first column of your table or the last column of your table.

### Automatic sorting

Automatically sort the table data based on the values of a specified column. The plugin uses the [tablesorter](https://github.com/Mottie/tablesorter) JavaScript library to perform this task.

### Manual sorting

Enable the **Manual Sorting** option to make a table sortable by your visitors with clicks on the table header.

### Limitations

The plugin currently supports a maximum of 10,000 rows and 40 columns per table. With this limitation, you will be able to create tables with a maximum of 400,000 cells.

Note that these limitations have been introduced for performance reasons and are also present in the [Pro version](https://daext.com/league-table/).


### Pro version

By purchasing the [Pro version](https://daext.com/league-table/) of League Table, you will enable the following additional features:

* Create backups of the plugin data or move the plugin data between different WordPress installations with the Import and Export menus
* Use up to five sorting criteria to sort the table based on the data available in multiple columns
* Merge the table cells
* Create formulas with the following arithmetical operation: Sum, Subtraction, Minimum, Maximum, Average
* Manually apply colors, custom typographic styles, or custom alignments to individual cells
* Automatically apply colors to specific ranking positions of the table or defined lists of rows or columns
* Enter custom HTML content in the table cells
* Specify and display the table caption
* Apply links to the text and images available in the table cells
* Include tables in the posts with a dedicated Gutenberg block

### Manual

Please see the [League Table Documentation](https://daext.com/doc/league-table/) for installation instruction, more details on the plugin usage, or to read the plugin FAQ.

### Credits

This plugin makes use of the following resources:

* [Select2](https://github.com/select2/select2) licensed under the [MIT License](http://www.opensource.org/licenses/mit-license.php)
* [Handsontable](https://github.com/handsontable/handsontable) (Handsontable CE 6.2.2) licensed under the [MIT License](http://www.opensource.org/licenses/mit-license.php)
* [TableSorter](https://github.com/Mottie/tablesorter) licensed under the [MIT License](http://www.opensource.org/licenses/mit-license.php)

== Installation ==
= Installation (Single Site) =

With this procedure you will be able to install the League Table plugin on your WordPress website:

1. Visit the **Plugins -> Add New** menu
2. Click on the **Upload Plugin** button and select the zip file you just downloaded
3. Click on **Install Now**
4. Click on **Activate Plugin**

= Installation (Multisite) =

This plugin supports both a **Network Activation** (the plugin will be activated on all the sites of your WordPress Network) and a **Single Site Activation** in a **WordPress Network** environment (your plugin will be activated on a single site of the network).

With this procedure you will be able to perform a **Network Activation**:

1. Visit the **Plugins -> Add New** menu
2. Click on the **Upload Plugin** button and select the zip file you just downloaded
3. Click on **Install Now**
4. Click on **Network Activate**

With this procedure you will be able to perform a **Single Site Activation** in a **WordPress Network** environment:

1. Visit the specific site of the **WordPress Network** where you want to install the plugin
2. Visit the **Plugins** menu
3. Click on the **Activate** button (just below the name of the plugin)

== Changelog ==

= 1.17 =

*October 22, 2024*

* Improved plugin activation logic to support WordPress.com Calypso and WP CLI.

= 1.16 =

*June 3, 2024*

* The "Order By" field now displays the correct value while in edit mode.

= 1.15 =

*April 8, 2024*

* Fixed a bug (started with WordPress version 6.5) that prevented the creation of the plugin database tables and the initialization of the plugin database options during the plugin activation.

= 1.14 =

*March 12, 2024*

* General refactoring. The phpcs "WordPress" ruleset has been applied to the plugin code.
* Nonce fields have been added to the "Tables" menus to prevent duplications and deletions of the tables not
manually performed by the user.
* The tablesorter library has been updated to version 2.31.3.
* The Chosen library has been replaced with the Select2 library.

= 1.13 =

*April 5, 2023*

* Changelog added.
* Minor backend improvements.

= 1.12 =

*March 15, 2022*

* Improved sanitization and escaping.
* Removed method used to find the localized versions of the linked daext.com website URLs.

= 1.10 =

*July 7, 2021*

* The "Pro Version" page has been updated.

= 1.07 =

*May 5, 2021*

* Initial release.

== Screenshots ==
1. Tables menu.
2. Options menu.