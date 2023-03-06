# multifamily-site-mgmt-plugin

This is a WordPress plugin that imports unit data from the Sightmap API and creates custom post types for each unit. The plugin includes an admin page where users can trigger the API import process and view imported unit data, as well as a shortcode to display a styled list of unit posts on the front end of the site.

## Installation

1. Download the plugin files and upload them to the `/wp-content/plugins/multi-family-site-management` directory.
2. Create a .env file in the plugin directory. 
3. Add Sightmap API credentials to the .env file. Use the following format: API_URL=<url> and API_KEY=<key>
4. Activate the plugin through the 'Plugins' menu in WordPress.

No external dependencies or plugins are required.

## Usage

### Admin Page

After installation, a new menu item titled 'Multi-family Sites' will appear in the WordPress admin sidebar. This menu item will take you to an admin page where you can trigger the API import process by clicking the 'Import Units' button. The plugin will fetch data from the API and create a custom post for each unit. The units can be viewed by selecting the 'Units' option from the admin sidebar.

### Custom Post Type

Each unit is represented by a custom post type titled 'Unit'. These posts are created by the plugin when the user imports unit data from the API.

The 'Unit' post type can be managed like any other post type. Users can edit individual unit posts, add custom fields to each post, and view all units in the WordPress admin.

### Shortcode

The plugin also includes a shortcode that can be used to display a styled list of unit posts on the front end of the site. The shortcode can be used by adding `[multifamily_units]` to a page or post content. Each item in the list is a link to the individual unit's post.

## Customization

The plugin can be customized by modifying the plugin files. To modify the plugin, the user should have a basic understanding of PHP and WordPress development.
