# RCP Wordpress Plugin

This is a **Wordpress** plugin, built by Rock Content to provide a simple way to integrate our customer's blogs with our platform and Communicate with Stage clients.

## Requirements

-   PHP 5.3+
-   Wordpress 3.5+
-   OpenSSL

## Installation

1. Clone or download this repository to the /wp-content/plugins/ directory
2. Activate the Rock Content Integrador plugin through the 'Plugins' menu in WordPress
3. Configure the plugin by going to the Rock Content menu that appears in your admin menu

## Usage

TODO: Write usage instructions

## Contributing

1. Fork it!
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request :D

## Deploying to Wordpress.org directory

1. Create a new release on Git/Github

## Known issues

Sometimes after install, update or uninstall the plugin, the routes are messed up. To fix that you need to
visit `http://blogurl.com/wp-admin/options-permalink.php` and click on **save** to flush the rewrite rules.

To prevent any issues I strongly recommend to perform this action after installing or updating the plugin.

**Note:** This is a wordpress problem. Deal with it.

## Changelog

### 1.0.0

-   Creates a token when user activates the plugin
-   Enable endpoints:
    -   **POST /rcp-publish-content** to publish posts
    -   **POST /rcp-activate-plugin** to activate rcp plugin
    -   **POST /rcp-enable-analytics** to enable rock analytics
    -   **POST /rcp-disable-analytics** to disable rock analytics
    -   **GET /rcp-get-analytics** to get analytics status
    -   **GET /rcp-disconnect-plugin** to retrieve a single post
    -   **GET /rcp-find-post** to retrieve a single post
    -   **GET /rcp-list-categories** to retrieve categories
    -   **GET /rcp-list-posts** to retrieve posts
    -   **GET /rcp-list-users** to retrieve users
    -   **GET /rcp-wp-version** to retrieve wordpress and plugin versions
