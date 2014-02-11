Super Simple Events
===================

Super Simple Events WordPress Plugin. This plugin is currently in development, please do not use.

## Installation

This section describes how to install the plugin and get it working.


### Using The WordPress Dashboard 

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'super-simple-events'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

### Uploading in WordPress Dashboard

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `super-simple-events.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

### Using FTP 
1. Download `super-simple-events.zip`
2. Extract the `super-simple-events` directory to your computer
3. Upload the `super-simple-events` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard


## GitHub Updater

The Super Simple Events includes native support for the [GitHub Updater](https://github.com/afragen/github-updater) which allows you to provide updates to your WordPress plugin from GitHub.

This uses a new tag in the plugin header:

>  `* GitHub Plugin URI: https://github.com/<owner>/<repo>`

Here's how to take advantage of this feature:

1. Install the [GitHub Updater](https://github.com/afragen/github-updater)
2. Replace `<owner>` with your username and `<repo>` with the repository of your plugin
3. Push commits to the master branch
4. Enjoy your plugin being updated in the WordPress dashboard

The current version of the GitHub Updater supports tags/branches - whichever has the highest number.

To specify a branch that you would like to use for updating, just add a `GitHub Branch:` header. GitHub Updater will preferentially use a tag over a branch having the same or lesser version number. If the version number of the specified branch is greater then the update will pull from the branch and not from the tag.

The default state is either `GitHub Branch: master` or nothing at all. They are equivalent.

All that info is in [the project](https://github.com/afragen/github-updater).

## License

The Super Simple Events is licensed under the GPL v2 or later.

> This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

> This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

> You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA