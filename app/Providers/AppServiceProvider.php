<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use App\Models\ProjectModel;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Blade::if('permission', function (string $permissionName) {
            if (auth()->user()->email == config('starter.super_admin_email')) {
                return true;
            }
            $roles = auth()->user()->roles;
            $permissions = [];
            foreach ($roles as $role) {
                foreach ($role->permissions as $permission) {
                    $permissions[] = $permission->name;
                }
            }
            $permissions = array_unique($permissions);
            return in_array($permissionName, $permissions);
        });
        Blade::if('onlydev', function () {
            $possibleEnvs = ['local', 'dev', 'development'];
            return in_array(config('app.env'), $possibleEnvs);
        });
        try {
            $detail = Cache::remember('project_brand_cache', 60, function () {
                if (!Schema::hasTable('projects_details') && !Schema::hasTable('project_models')) {
                    return null;
                }
                return ProjectModel::select(['project_name', 'logo'])->first();
            });
            $title = $detail->project_name ?? 'AdminLTE';
            $logoPath = (!empty($detail?->logo))
                ? 'storage/' . ltrim($detail->logo, '/')
                : 'vendor/adminlte/dist/img/logo.png';
            Config::set('adminlte.title', $title);
            Config::set('adminlte.logo', e($title));
            Config::set('adminlte.logo_img', $logoPath);
            Config::set('adminlte.logo_img_class', 'brand-image brand-image-custom');
            Config::set('adminlte.auth_logo.enabled', true);
            Config::set('adminlte.auth_logo.img.path', $logoPath);
            Config::set('adminlte.auth_logo.img.alt', $title);
            Config::set('adminlte.auth_logo.img.width', 80);
            Config::set('adminlte.auth_logo.img.height', 40);
            Config::set('adminlte.auth_logo.img.class', 'auth-logo-rounded');
            Config::set('adminlte.use_full_favicon', true);
            Config::set('adminlte.use_ico_only', true);
            Config::set('adminlte.preloader.enabled', true);
            Config::set('adminlte.preloader.img.path', $logoPath);
            Config::set('adminlte.preloader.img.alt', $title);
            Config::set('adminlte.preloader.img.width', 60);
            Config::set('adminlte.preloader.img.height', 60);
        } catch (\Throwable $e) {

        }
    }
}
