<?php
namespace Mdkaif\ProUpdaterGit\Models; // Updated vendor namespace

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UpdateToken extends Model
{
    use HasFactory;

    protected $table = 'pro_updater_git_tokens'; // Custom table name

    protected $fillable = [
        'repository_url',
        'token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'token',
    ];

    /**
     * Get the token without hashing for direct use (e.g., by GitService).
     *
     * IMPORTANT SECURITY NOTE:
     * In a real-world production application, you should *encrypt* the token
     * using Laravel's Crypt facade (e.g., `Crypt::encryptString($token)`) before storing,
     * and `Crypt::decryptString($this->attributes['token'])` when retrieving.
     * Hashing (Hash::make()) is one-way and cannot be unhashed. For GitService to use it,
     * it would need the original token. This property is currently a conceptual placeholder
     * and `GitService` would typically receive the unhashed token directly or decrypt it itself.
     * For this specific implementation, the token passed to GitService would be the raw input from setup.
     * This method (`getRawTokenAttribute`) is therefore not directly used by the current `GitService`.
     *
     * @return string|null
     */
    public function getRawTokenAttribute()
    {
        return null;
    }
}