<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
//laravel permission
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    // use HasProfilePhoto;
    use Notifiable;
    // use TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'users';
    protected $fillable = [
        'name',
        'email',
        'password',
        'knowledge_area_id',
        'institution_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function isSuperAdmin(): bool
    {
        return false;
    }

    public function getRolesArray(): Collection
    {
        return $this->roles()->get()->mapWithKeys(function ($role) {
            return [$role['name'] => true];
        });
    }

    public function getRole(?int $roleId = null, ?string $roleName = null): bool
    {
        if ($roleId) {
            $role = Role::find($roleId);
            if ($role) {
                $roleName = $role->name;
            } else {
                return false;
            }
        }
        return $roleName ? $this->hasRole($roleName) : false;
    }

    public function getPermissionArray(): Collection
    {
        return $this->getAllPermissions()->mapWithKeys(function ($permission) {
            return [$permission['name'] => true];
        });
    }

    public function canPermission(string $permissionName): bool
    { 
        if (Permission::where('name', $permissionName)->exists()) {
            return $this->hasPermissionTo($permissionName);
        }
        return false;
    }

    public function knowledgeArea()
    {
        return $this->belongsTo(KnowledgeArea::class, 'knowledge_area_id');
    }
    public function mainArea()
    {
        return $this->knowledgeArea?->parent ?? $this->knowledgeArea; // esto te da el área principal si el user está en subárea
    }
   public function subArea()
{
    return $this->knowledgeArea?->parent ? $this->knowledgeArea : null;
}
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function file()
    {
        return $this->morphOne(File::class, 'fileable');
    }

    public function paymentVoucher()
    {
        return $this->hasMany(PaymentVoucher::class);
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'postulant_id');
    }
    // Un revisor (usuario) puede tener muchas evaluaciones
    public function evaluations()
    {
        return $this->hasMany(ArticleEvaluation::class, 'user_id');
    }

    public function articleReviews()
{
    return $this->hasMany(ArticleReview::class, 'reviewer_id');
}

}
