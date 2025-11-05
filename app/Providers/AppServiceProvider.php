<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
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

        // 🔸 Custom Blade directives
        Blade::if('permission', function (string $permissionName) {
            if (auth()->check() && auth()->user()->email == config('starter.super_admin_email')) {
                return true;
            }

            $roles = auth()->user()->roles ?? [];
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

        // 🔹 Dynamic project branding configuration
        try {
            $detail = Cache::remember('project_brand_cache', 60, function () {
                if (!Schema::hasTable('projects_details') && !Schema::hasTable('project_models')) {
                    return null;
                }
                return ProjectModel::select(['project_name', 'logo'])->first();
            });

            $title = $detail->project_name ?? 'Rent2Recover';
            $logoPath = (!empty($detail?->logo))
                ? 'storage/' . ltrim($detail->logo, '/')
                : 'vendor/adminlte/dist/img/logo.png';

            // 🔹 Default Brand & Logo setup (for dashboard/admin)
            Config::set('adminlte.title', $title);
            Config::set('adminlte.logo', e($title));
            Config::set('adminlte.logo_img', $logoPath);
            Config::set('adminlte.logo_img_class', 'brand-image brand-image-custom');

            // 🔹 Auth Page Logo setup (login/register/etc.)
            Config::set('adminlte.auth_logo.enabled', true);
            Config::set('adminlte.auth_logo.img.path', $logoPath);
            Config::set('adminlte.auth_logo.img.alt', $title);
            Config::set('adminlte.auth_logo.img.width', 200);
            Config::set('adminlte.auth_logo.img.height', 100);
            Config::set('adminlte.auth_logo.img.class', 'auth-logo-rounded mx-auto d-block mb-3');

            // 🔹 Preloader setup
            Config::set('adminlte.preloader.enabled', true);
            Config::set('adminlte.preloader.img.path', $logoPath);
            Config::set('adminlte.preloader.img.alt', $title);
            Config::set('adminlte.preloader.img.width', 60);
            Config::set('adminlte.preloader.img.height', 60);

            // 🔹 Favicon setup
            Config::set('adminlte.use_ico_only', false);
            Config::set('adminlte.use_full_favicon', true);
            Config::set('adminlte.favicon.path', $logoPath);
            Config::set('adminlte.favicon.alt', $title);

            // 🧠 Remove company name ONLY on auth-related pages
            $path = Request::path();
            if (preg_match('/^(login|register|password|forgot)/', $path)) {
                Config::set('adminlte.logo', '');  // no text below logo
                Config::set('adminlte.title', ''); // no page title
            }

        } catch (\Throwable $e) {
            // silently ignore errors to prevent breaking boot
        }
    }
}
