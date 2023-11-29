# Intro
Zoom is the web and app based video conferencing service (http://zoom.us). This plugin offers tight integration with Moodle.
To connect to the Zoom APIs, this plugin requires an account-level app to be created.

### Server-to-Server OAuth
To [create an account-level Server-to-Server OAuth app](https://developers.zoom.us/docs/internal-apps/create/), the `Server-to-server OAuth app`
permission is required. You should create a separate Server-to-Server OAuth app for each Moodle install.

The Server-to-Server OAuth app will generate a client ID, client secret and account ID.

The following scopes are required by this plugin:
- meeting:read:admin (Read meeting details)
- meeting:write:admin (Create/Update meetings)
- user:read:admin (Read user details)
- recording:read:admin
- tracking_fields:read:admin

## Configuration
After installing the subplugin, the following settings need to be configured to use the subplugin:
- Zoom account ID
- Zoom client ID
- Zoom client secret
- Zoom license

If you get "Access token is expired" errors, make sure the date/time on your server is properly synchronized with the time servers, or the account ID, client ID and client secret is not used in another Moodle or app.
