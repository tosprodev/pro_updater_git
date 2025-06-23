<?php
namespace Mdkaif\ProUpdaterGit\Console; // Updated vendor namespace

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Mdkaif\ProUpdaterGit\Models\UpdateToken; // Updated namespace
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class SetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pro-updater-git:setup'; // Updated signature

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets up the Pro_Updater_Git package configuration.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting Pro_Updater_Git Setup...');

        // 1. Publish Config
        $this->info('Publishing configuration file...');
        Artisan::call('vendor:publish', [
            '--tag' => 'pro-updater-git-config',
            '--force' => true
        ]);
        $this->info(Artisan::output());

        // 2. Configure Repository
        $repoUrl = $this->ask('Enter the Git repository URL (e.g., https://github.com/your-org/your-repo.git)');
        $branch = $this->ask('Enter the Git branch to track (e.g., main, master, production)', 'main');
        $repoPath = $this->ask('Enter the local path to your project root (e.g., ' . base_path() . ')', base_path());
        $versionFile = $this->ask('Enter the relative path to your version file (e.g., VERSION.txt)', 'VERSION.txt');
        $gitBinPath = $this->ask('Enter the path to your git executable (leave empty for default "git")', 'git');

        // Update config file
        $configPath = config_path('auto-updater.php');
        $configContent = File::get($configPath);

        $configContent = preg_replace("/'repository_url' => '(.*?)'/", "'repository_url' => '{$repoUrl}'", $configContent);
        $configContent = preg_replace("/'repository_branch' => '(.*?)'/", "'repository_branch' => '{$branch}'", $configContent);
        $configContent = preg_replace("/'repository_path' => '(.*?)'/", "'repository_path' => '{$repoPath}'", $configContent);
        $configContent = preg_replace("/'version_file' => '(.*?)'/", "'version_file' => '{$versionFile}'", $configContent);
        $configContent = preg_replace("/'git_bin_path' => '(.*?)'/", "'git_bin_path' => '{$gitBinPath}'", $configContent);

        File::put($configPath, $configContent);
        $this->info('Configuration updated in ' . $configPath);
        Artisan::call('config:clear');
        $this->info('Configuration cache cleared.');

        // 3. Run Migrations
        $this->info('Running migrations for update token storage...');
        Artisan::call('migrate');
        $this->info(Artisan::output());

        // 4. Handle Private Repository Token
        if ($this->confirm('Is this a private repository that requires a token?', true)) {
            $token = $this->secret('Please enter your Git personal access token/app password (it will be hashed and stored)');
            if ($token) {
                UpdateToken::truncate();
                UpdateToken::create([
                    'repository_url' => $repoUrl,
                    'token' => Hash::make($token),
                ]);
                $this->info('Git token saved securely.');
            } else {
                $this->warn('No token provided. Private repository updates might fail.');
            }
        }

        // 5. Create initial VERSION file if it doesn't exist
        $initialVersion = '1.0.0';
        $fullVersionFilePath = $repoPath . '/' . $versionFile;
        if (!File::exists($fullVersionFilePath)) {
            $this->info("Creating initial version file at `{$fullVersionFilePath}` with version `{$initialVersion}`.");
            File::put($fullVersionFilePath, $initialVersion);
        } else {
            $this->info("Version file already exists at `{$fullVersionFilePath}`. Current version: " . File::get($fullVersionFilePath));
        }

        $this->info('Pro_Updater_Git setup complete!');
        $this->warn('Remember to add `\Mdkaif\ProUpdaterGit\Http\Middleware\CheckForUpdate::class` to your `web` middleware group in `app/Http/Kernel.php` to enable automatic update checks.'); // Updated class name
        $this->warn('Also, consider running `php artisan pro-updater-git:update` manually once to ensure initial synchronization.');

        return Command::SUCCESS;
    }
}