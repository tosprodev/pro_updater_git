<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Git Repository Configuration
    |--------------------------------------------------------------------------
    |
    | These settings define the Git repository from which your project will
    | pull updates. For private repositories, ensure you have configured
    | a personal access token with appropriate read permissions.
    | Environment variables are preferred for sensitive data.
    |
    */
    'repository_url' => env('PRO_UPDATER_GIT_REPO_URL', 'https://github.com/your-org/your-repo.git'),
    'repository_branch' => env('PRO_UPDATER_GIT_REPO_BRANCH', 'main'),

    /*
    |--------------------------------------------------------------------------
    | Local Project Path
    |--------------------------------------------------------------------------
    |
    | This is the absolute path to your Laravel project's root directory.
    | The Git operations (clone, pull) will be performed within this directory.
    | By default, it uses Laravel's base_path() function, which is typically correct.
    |
    */
    'repository_path' => base_path(),

    /*
    |--------------------------------------------------------------------------
    | Version File Configuration
    |--------------------------------------------------------------------------
    |
    | This file is used to store and compare the current version of your
    | application. It should be a simple text file containing only the
    | version number (e.g., "1.0.0"). This file should exist in your
    | Git repository at the specified relative path.
    |
    */
    'version_file' => env('PRO_UPDATER_GIT_VERSION_FILE', 'VERSION.txt'),

    /*
    |--------------------------------------------------------------------------
    | Git Binary Path
    |--------------------------------------------------------------------------
    |
    | The path to your Git executable. In most cases, 'git' is sufficient
    | if Git is in your system's PATH. If not, provide the full path
    | (e.g., '/usr/bin/git' or 'C:\Program Files\Git\bin\git.exe').
    |
    */
    'git_bin_path' => env('PRO_UPDATER_GIT_GIT_BIN_PATH', 'git'),

    /*
    |--------------------------------------------------------------------------
    | Update Check Interval
    |--------------------------------------------------------------------------
    |
    | Defines how often (in minutes) the system will check for new updates
    | when a user accesses the web application. Setting this to a low value
    | (e.g., 0 for every request) is NOT recommended for performance reasons.
    |
    */
    'check_interval_minutes' => env('PRO_UPDATER_GIT_CHECK_INTERVAL', 60),
];