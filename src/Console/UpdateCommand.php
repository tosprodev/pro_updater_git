<?php
namespace Mdkaif\ProUpdaterGit\Console; // Updated vendor namespace

use Illuminate\Console\Command;
use Mdkaif\ProUpdaterGit\GitService; // Updated namespace
use Mdkaif\ProUpdaterGit\Models\UpdateToken; // Updated namespace
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class UpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pro-updater-git:update'; // Updated signature

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the project to the latest version from the Git repository.';

    protected $gitService;

    public function __construct(GitService $gitService)
    {
        parent::__construct();
        $this->gitService = $gitService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $repoUrl = config('auto-updater.repository_url');
        $branch = config('auto-updater.repository_branch');
        $token = UpdateToken::first()->token ?? null;

        if (!$repoUrl || !$branch) {
            $this->error('Repository URL or branch is not configured. Please run `php artisan pro-updater-git:setup`.');
            return Command::FAILURE;
        }

        $this->info("Checking for updates from {$repoUrl} on branch {$branch}...");

        $currentVersion = $this->gitService->getCurrentVersion();
        $remoteVersion = $this->gitService->getRemoteVersion($repoUrl, $branch, $token);

        if ($currentVersion === null) {
            $this->warn("Local version file not found. Assuming first installation or misconfiguration.");
        }

        $this->info("Current version: " . ($currentVersion ?? 'N/A'));
        $this->info("Remote version: " . ($remoteVersion ?? 'N/A'));

        if ($remoteVersion && version_compare($remoteVersion, $currentVersion, '>')) {
            $this->info("New update available: {$remoteVersion}. Updating...");

            if (!$this->gitService->isGitRepository()) {
                $this->warn("Local directory is not a Git repository. Attempting to clone.");
                if (!$this->gitService->cloneRepo($repoUrl, $branch, $token)) {
                    $this->error("Failed to clone the repository. Check configuration and permissions.");
                    return Command::FAILURE;
                }
                $this->info("Repository cloned successfully.");
            } else {
                $this->info("Pulling latest changes...");
                if (!$this->gitService->pullRepo($branch)) {
                    $this->error("Failed to pull updates. Check permissions and Git status.");
                    return Command::FAILURE;
                }
                $this->info("Updates pulled successfully.");
            }

            if ($this->gitService->setLocalVersion($remoteVersion)) {
                $this->info("Local version updated to {$remoteVersion}.");
                Artisan::call('cache:clear');
                Artisan::call('config:clear');
                Artisan::call('route:clear');
                Artisan::call('view:clear');
                $this->info("Cache, config, route, and view caches cleared.");
            } else {
                $this->error("Failed to update local version file.");
                return Command::FAILURE;
            }

            $this->info("Project updated to version {$remoteVersion} successfully!");
            return Command::SUCCESS;
        } elseif ($remoteVersion === null) {
            $this->error("Could not retrieve remote version. Check repository URL, branch, and token.");
            return Command::FAILURE;
        } else {
            $this->info("Project is already up to date (Version: {$currentVersion}).");
            return Command::SUCCESS;
        }
    }
}
