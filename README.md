mdkaif/pro_updater_git - Laravel Git Auto-Updater
mdkaif/pro_updater_git is a robust Laravel package designed to provide seamless auto-updating functionality for your Laravel applications directly from a Git repository. It simplifies the process of deploying updates by checking for new versions in the background and offering a user-friendly interface to initiate updates.

✨ Features
Git-Based Updates: Pulls the latest code directly from your configured Git repository.

Private Repository Support: Securely stores personal access tokens (hashed in the database) for private repositories.

Version Tracking: Compares local and remote VERSION.txt files to detect new updates.

Update Notifications: Displays a beautiful, inline-CSS styled popup modal to notify users of available updates.

Background Checks: Periodically checks for updates without impacting user experience, configurable via middleware.

Artisan Commands: Provides convenient Artisan commands for initial setup and manual updates.

Cache Clearing: Automatically clears relevant Laravel caches (config, route, view, cache) after a successful update.

🚀 Installation
You can install the package via Composer:

composer require mdkaif/pro_updater_git

The package will automatically discover its service provider in Laravel versions 5.5 and above.

For Laravel 10/11 & below (if auto-discovery fails):
In your config/app.php file, add the service provider to the providers array:

// config/app.php

'providers' => [
    // ... other providers
    Mdkaif\ProUpdaterGit\ProUpdaterGitServiceProvider::class,
],

⚙️ Configuration & Setup
After installation, you need to set up the package by publishing its configuration and running the interactive setup command.

Run the Setup Artisan Command:
This command will publish the auto-updater.php configuration file to your config/ directory and run the necessary database migration for secure token storage.

php artisan pro-updater-git:setup

Follow the interactive prompts:

Git Repository URL: Enter the full URL to your project's Git repository (e.g., https://github.com/your-org/your-project.git).

Git Branch: Specify the branch to track for updates (e.g., main, master, production).

Local Project Path: This usually defaults to your Laravel project's base_path(). Confirm it is correct.

Version File: Provide the relative path to a simple text file in your project's repository (e.g., VERSION.txt) that contains only the current version number (e.g., 1.0.0). This file is crucial for version comparison.

Git Executable Path: Leave this empty if git is already in your system's PATH. Otherwise, provide the full path (e.g., /usr/bin/git or C:\Program Files\Git\bin\git.exe).

Private Repository (requires token)?: Answer yes or no. If yes, you will be prompted for your Git Personal Access Token. This token will be hashed and stored securely in your database to authenticate with private repositories.

Create VERSION.txt (in your main Laravel project):
Ensure you have a VERSION.txt file in the root of your main Laravel project (e.g., your-laravel-project/VERSION.txt), containing the current version number (e.g., 1.0.0). This file must be committed to your project's Git repository. The updater package relies on this file within the application's repository to determine its current version.

Register Middleware:
To enable automatic background update checks on web requests, add the package's middleware to your web middleware group in app/Http/Kernel.php:

// app/Http/Kernel.php

protected array $middlewareGroups = [
    'web' => [
        // ... other middleware
        \Mdkaif\ProUpdaterGit\Http\Middleware\CheckForUpdate::class,
    ],
    // ...
];

💡 Usage
The mdkaif/pro_updater_git package provides a simple Blade directive to integrate the update status display and modals into your application's frontend.

Include the Blade directive in any of your main Blade views, ideally in your layout file (e.g., resources/views/layouts/app.blade.php or resources/views/welcome.blade.php), typically near the closing </body> tag or where you want the update button to appear:

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Laravel App</title>
    <!-- Your application's main CSS/JS goes here -->
</head>
<body>
    <div id="app">
        <!-- Your application content -->
        <main class="py-4">
            @yield('content')
        </main>

        <!-- Pro_Updater_Git Update Button and Modals -->
        @proUpdaterButton
    </div>
</body>
</html>

When a new update is detected, a beautifully designed, inline-CSS styled pop-up modal will automatically appear (after the configured check interval) to notify the user.

Users can click "Update Now" within the modal to trigger the update process.

You can also manually trigger an update via an Artisan command at any time:

php artisan pro-updater-git:update

🌍 Environment Variables
The package configuration, once published, can be easily overridden using the following environment variables in your .env file:

PRO_UPDATER_GIT_REPO_URL=https://github.com/your-org/your-repo.git
PRO_UPDATER_GIT_REPO_BRANCH=main
PRO_UPDATER_GIT_VERSION_FILE=VERSION.txt
PRO_UPDATER_GIT_GIT_BIN_PATH=git
PRO_UPDATER_GIT_CHECK_INTERVAL=60 # in minutes, defines how often to check for updates

🤝 Contributing
Contributions are welcome! Please feel free to open issues or submit pull requests.

📄 License
The mdkaif/pro_updater_git package is open-sourced software licensed under the MIT license.