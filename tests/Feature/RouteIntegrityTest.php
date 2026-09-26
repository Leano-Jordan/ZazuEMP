<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteIntegrityTest extends TestCase
{
    public function test_expected_application_routes_are_registered(): void
    {
        $expected = [
            'login',
            'login.store',
            'owner.login',
            'password.request',
            'password.email',
            'password.reset',
            'password.update',
            'register',
            'register.store',
            'owner.dashboard',
            'logout',
            'business.switch',
            'dashboard',
            'calendar.index',
            'suppliers.index',
            'inventory.index',
            'assets.index',
            'reports.index',
            'settings.index',
            'quotes.index',
            'quotes.show',
            'quotes.versions.store',
            'work.index',
            'work.create',
            'work.store',
            'work.show',
            'work.edit',
            'work.update',
            'work.destroy',
            'work.travel.index',
            'work.travel.create',
            'work.travel.store',
            'work.quotes.index',
            'work.quotes.create',
            'work.quotes.store',
            'work.requirements.index',
            'work.requirements.create',
            'work.requirements.store',
            'customers.index',
            'customers.create',
            'customers.store',
            'customers.show',
            'customers.edit',
            'customers.update',
            'customers.contacts.create',
            'customers.contacts.store',
            'customers.contacts.edit',
            'customers.contacts.update',
            'customers.contacts.destroy',
            'capabilities.index',
            'capabilities.create',
            'capabilities.store',
            'capabilities.edit',
            'capabilities.update',
        ];

        $missing = collect($expected)
            ->filter(fn ($name) => !Route::has($name))
            ->values()
            ->all();

        $this->assertSame([], $missing);
    }

    public function test_controller_routes_resolve_to_existing_controller_methods(): void
    {
        $invalid = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => $route->getControllerClass() !== null)
            ->filter(function ($route) {
                $class = $route->getControllerClass();
                $method = $route->getActionMethod();

                if ($method === $class) {
                    $method = '__invoke';
                }

                return !class_exists($class) || !method_exists($class, $method);
            })
            ->map(fn ($route) => [
                'name' => $route->getName(),
                'action' => $route->getActionName(),
            ])
            ->values()
            ->all();

        $this->assertSame([], $invalid);
    }

    public function test_blade_named_route_calls_point_to_registered_routes(): void
    {
        $registered = collect(Route::getRoutes()->getRoutes())
            ->map(fn ($route) => $route->getName())
            ->filter()
            ->flip();

        $missing = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            preg_match_all(
                '/route\(\s*[\'\"]([^\'\"]+)[\'\"]/',
                $file->getContents(),
                $matches
            );

            foreach (array_unique($matches[1] ?? []) as $name) {
                if (!isset($registered[$name])) {
                    $missing[$file->getRelativePathname()][] = $name;
                }
            }
        }

        $this->assertSame([], $missing);
    }
}
