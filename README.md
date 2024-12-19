# Moodle Auth Plugin Installation and Configuration

## Installation

1. **Download the Plugin:**
   - Download the auth plugin from the [Moodle plugins directory](https://moodle.org/plugins).

2. **Upload the Plugin:**
   - Log in to your Moodle site as an admin.
   - Navigate to `Site administration` > `Plugins` > `Install plugins`.
   - Upload the plugin ZIP file and click `Install plugin from the ZIP file`.

3. **Install the Plugin:**
   - Follow the on-screen instructions to complete the installation.
   - After installation, the plugin should be listed under `Site administration` > `Plugins` > `Manage plugins`.

## Configuration

1. **Enable the Plugin:**
   - Go to `Site administration` > `Plugins` > `Authentication` > `Manage authentication`.
   - Find your newly installed auth plugin in the list and enable it by clicking the eye icon.

2. **Set as Default Authentication Plugin:**
   - In the `Manage authentication` page, drag and drop your auth plugin to the top of the list to set it as the default authentication method.
   - Save changes.

## Additional Settings

- **Configure Plugin Settings:**
  - Go to `Site administration` > `Plugins` > `Authentication` > [Plugin name] settings.
  - Adjust the settings as required for your environment.

- **Test the Plugin:**
  - Log out and attempt to log in using the new authentication method to ensure it is working correctly.

For more detailed information, please refer to the [Moodle documentation](https://docs.moodle.org) and the specific plugin's documentation.
