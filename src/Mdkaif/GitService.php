<?php
namespace Mdkaif\ProUpdaterGit; // Updated vendor namespace

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class GitService
{
    protected $repositoryPath;
    protected $versionFile;
    protected $gitBinaryPath;

    /**
     * Constructor for GitService.
     *
     * @param string $repositoryPath The absolute path to the local Git repository.
     * @param string $versionFile The relative path to the version file within the repository.
     * @param string|null $gitBinaryPath The absolute path to the Git executable, or 'git' if in PATH.
     */
    public function __construct(string $repositoryPath, string $versionFile, ?string $gitBinaryPath = null)
    {
        $this->repositoryPath = $repositoryPath;
        $this->versionFile = $versionFile;
        $this->gitBinaryPath = $gitBinaryPath ?? 'git'; // Default to 'git' if not specified
    }

    /**
     * Execute a Git command within the specified repository path.
     *
     * @param string $command The Git command to execute (e.g., "pull origin main").
     * @return array An array containing the command output (string) and exit code (integer).
     */
    protected function executeGitCommand(string $command): array
    {
        $fullCommand = "{$this->gitBinaryPath} -C {$this->repositoryPath} {$command} 2>&1";
        Log::info("Executing Git command: " . $fullCommand);

        $output = [];
        $exitCode = 0;
        exec($fullCommand, $output, $exitCode);

        $output = implode("\n", $output);
        if ($exitCode !== 0) {
            Log::error("Git command failed: " . $fullCommand . "\nOutput: " . $output);
        } else {
            Log::info("Git command successful: " . $fullCommand . "\nOutput: " . $output);
        }

        return [$output, $exitCode];
    }

    /**
     * Execute a Git command without changing directory first (used for 'git clone' into a new directory).
     *
     * @param string $command The Git command to execute (e.g., "clone repo_url target_dir").
     * @return array An array containing the command output (string) and exit code (integer).
     */
    protected function executeGlobalGitCommand(string $command): array
    {
        $fullCommand = "{$this->gitBinaryPath} {$command} 2>&1";
        Log::info("Executing global Git command: " . $fullCommand);

        $output = [];
        $exitCode = 0;
        exec($fullCommand, $output, $exitCode);

        $output = implode("\n", $output);
        if ($exitCode !== 0) {
            Log::error("Global Git command failed: " . $fullCommand . "\nOutput: " . $output);
        } else {
            Log::info("Global Git command successful: " . $fullCommand . "\nOutput: " . $output);
        }

        return [$output, $exitCode];
    }

    /**
     * Checks if the current project directory is a Git repository.
     *
     * @return bool True if a .git directory exists, false otherwise.
     */
    public function isGitRepository(): bool
    {
        return File::isDirectory($this->repositoryPath . '/.git');
    }

    /**
     * Clones the repository into the specified repository path.
     *
     * @param string $repoUrl The URL of the Git repository to clone.
     * @param string $branch The branch to clone.
     * @param string|null $token Optional. Personal Access Token for private repositories.
     * @return bool True on successful clone, false otherwise.
     */
    public function cloneRepo(string $repoUrl, string $branch, ?string $token = null): bool
    {
        $authRepoUrl = $repoUrl;
        if ($token) {
            $parsedUrl = parse_url($repoUrl);
            if (isset($parsedUrl['host'])) {
                $authRepoUrl = str_replace(
                    "{$parsedUrl['scheme']}://",
                    "{$parsedUrl['scheme']}://oauth2:{$token}@",
                    $repoUrl
                );
            }
        }

        if (!File::isDirectory(dirname($this->repositoryPath))) {
            File::makeDirectory(dirname($this->repositoryPath), 0755, true);
        }

        $tempDir = $this->repositoryPath . '_temp_' . uniqid();
        $command = "clone --depth 1 -b {$branch} {$authRepoUrl} {$tempDir}";
        list($output, $exitCode) = $this->executeGlobalGitCommand($command);

        if ($exitCode !== 0) {
            Log::error("Failed to clone repository: " . $output);
            if (File::isDirectory($tempDir)) {
                File::deleteDirectory($tempDir);
            }
            return false;
        }

        if (File::isDirectory($this->repositoryPath)) {
            if (File::isDirectory($this->repositoryPath . '/.git')) {
                File::deleteDirectory($this->repositoryPath . '/.git');
            }
        } else {
            File::makeDirectory($this->repositoryPath, 0755, true);
        }

        if (function_exists('exec') && strtolower(PHP_OS_FAMILY) !== 'windows') {
            $rsyncCommand = "rsync -a --delete {$tempDir}/ {$this->repositoryPath}";
            exec($rsyncCommand, $rsyncOutput, $rsyncExitCode);
            if ($rsyncExitCode !== 0) {
                Log::error("Rsync failed to move files: " . implode("\n", $rsyncOutput));
                File::move($tempDir, $this->repositoryPath); // Fallback to PHP move
            }
        } else {
            File::copyDirectory($tempDir, $this->repositoryPath);
        }

        File::deleteDirectory($tempDir);

        return true;
    }


    /**
     * Pulls the latest changes from the repository.
     *
     * @param string $branch
     * @return bool
     */
    public function pullRepo(string $branch): bool
    {
        if (!$this->isGitRepository()) {
            Log::error("Cannot pull: {$this->repositoryPath} is not a Git repository.");
            return false;
        }

        $this->executeGitCommand("checkout {$branch}");
        $this->executeGitCommand("fetch --all");
        list($output, $exitCode) = $this->executeGitCommand("pull origin {$branch}");

        if ($exitCode !== 0) {
            Log::error("Failed to pull repository: " . $output);
            return false;
        }

        return true;
    }

    /**
     * Gets the current version from the local version file.
     *
     * @return string|null
     */
    public function getCurrentVersion(): ?string
    {
        $versionFilePath = $this->repositoryPath . '/' . $this->versionFile;
        if (File::exists($versionFilePath)) {
            return trim(File::get($versionFilePath));
        }
        return null;
    }

    /**
     * Gets the remote version by cloning to a temp directory and reading its version file.
     *
     * @param string $repoUrl
     * @param string $branch
     * @param string|null $token
     * @return string|null
     */
    public function getRemoteVersion(string $repoUrl, string $branch, ?string $token = null): ?string
    {
        $tempDir = sys_get_temp_dir() . '/Mdkaif_pro_updater_git_temp_' . uniqid(); // Updated temp dir name
        $version = null;

        try {
            $authRepoUrl = $repoUrl;
            if ($token) {
                $parsedUrl = parse_url($repoUrl);
                if (isset($parsedUrl['host'])) {
                    $authRepoUrl = str_replace(
                        "{$parsedUrl['scheme']}://",
                        "{$parsedUrl['scheme']}://oauth2:{$token}@",
                        $repoUrl
                    );
                }
            }

            $command = "clone --depth 1 -b {$branch} {$authRepoUrl} {$tempDir}";
            list($output, $exitCode) = $this->executeGlobalGitCommand($command);

            if ($exitCode === 0 && File::exists("{$tempDir}/{$this->versionFile}")) {
                $version = trim(File::get("{$tempDir}/{$this->versionFile}"));
            } else {
                Log::error("Failed to get remote version or version file not found in temp clone: " . $output);
            }
        } catch (\Exception $e) {
            Log::error("Error getting remote version: " . $e->getMessage());
        } finally {
            if (File::isDirectory($tempDir)) {
                File::deleteDirectory($tempDir);
            }
        }

        return $version;
    }

    /**
     * Sets the local version file to a new version.
     *
     * @param string $newVersion
     * @return bool
     */
    public function setLocalVersion(string $newVersion): bool
    {
        $versionFilePath = $this->repositoryPath . '/' . $this->versionFile;
        try {
            File::put($versionFilePath, $newVersion);
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to write new version to file: " . $e->getMessage());
            return false;
        }
    }
}