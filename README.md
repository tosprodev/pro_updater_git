# mdkaif/pro_updater_git - Laravel Git Auto-Updater

**mdkaif/pro_updater_git** is a robust Laravel package designed to provide seamless auto-updating functionality for your Laravel applications directly from a Git repository. It simplifies the process of deploying updates by checking for new versions in the background and offering a user-friendly interface to initiate updates.

---

## ✨ Features

- **Git-Based Updates**: Pulls the latest code directly from your configured Git repository.
- **Private Repository Support**: Securely stores personal access tokens (hashed in the database) for private repositories.
- **Version Tracking**: Compares local and remote `VERSION.txt` files to detect new updates, supporting semantic versioning.
- **Update Notifications**: Displays a beautiful, inline-CSS styled popup modal to notify users of available updates.
- **Background Checks**: Periodically checks for updates without impacting user experience, configurable via middleware.
- **Artisan Commands**: Provides convenient Artisan commands for initial setup and manual updates.
- **Cache Clearing**: Automatically clears relevant Laravel caches (`config`, `route`, `view`, `cache`) after a successful update.

---

## 🚀 Installation

Install the package using Composer:

```bash
composer require mdkaif/pro_updater_git
Laravel supports package auto-discovery (5.5+). If for some reason it doesn’t register automatically (Laravel 10/11 or below), add the service provider manually:

php
Copy
Edit
// config/app.php

'providers' => [
    // Other providers...
    Mdkaif\ProUpdaterGit\ProUpdaterGitServiceProvider::class,
],
## ⚙️ Configuration & Setup
1. Run the Setup Command
bash
Copy
Edit
php artisan pro-updater-git:setup
This command walks you through:

Setting your Git repo URL

Selecting branch (e.g., main)

Providing path to VERSION.txt

Git executable path (optional)

Saving your Git token (for private repos)

2. Create a VERSION.txt File
At the root of your Laravel app, create:

pgsql
Copy
Edit
VERSION.txt
With content like:

Copy
Edit
1.0.0
✅ Ensure this file is committed to your Git repo.

3. Register Middleware
Add the update checker middleware to your web group in:

php
Copy
Edit
// app/Http/Kernel.php

protected array $middlewareGroups = [
    'web' => [
        // Other middleware
        \Mdkaif\ProUpdaterGit\Http\Middleware\CheckForUpdate::class,
    ],
];
## 💡 Usage
Add the Blade directive to your layout:

blade
Copy
Edit
<!-- resources/views/layouts/app.blade.php -->

@proUpdaterButton
This will automatically inject a modal popup when updates are available, along with a button to trigger the update.

Manual Update
You can trigger an update at any time via Artisan:

bash
Copy
Edit
php artisan pro-updater-git:update
## 🌍 Environment Variables
Optional overrides via .env:

env
Copy
Edit
PRO_UPDATER_GIT_REPO_URL=https://github.com/your-org/your-repo.git
PRO_UPDATER_GIT_REPO_BRANCH=main
PRO_UPDATER_GIT_VERSION_FILE=VERSION.txt
PRO_UPDATER_GIT_GIT_BIN_PATH=git
PRO_UPDATER_GIT_CHECK_INTERVAL=60 # in minutes
## 🤝 Contributing
Feel free to contribute by opening issues or pull requests.

✅ Report bugs

## ✨ Suggest features

🛠️ Submit PRs

📄 License
This package is released under the MIT license.


---

### ✅ What to Do Next?

1. Open your project folder  
2. Create a file named `README.md`  
3. Paste the full content above  
4. Commit to your repo!

Would you like me to prepare this in a downloadable `.zip` or raw file instead?
