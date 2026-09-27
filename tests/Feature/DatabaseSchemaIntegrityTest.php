<?php

namespace Tests\Feature;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSchemaIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_repository_migration_created_table_exists_after_fresh_migration(): void
    {
        $expectedTables = [];

        foreach (File::files(database_path('migrations')) as $file) {
            preg_match_all(
                '/Schema::create\(\s*[\'\"]([^\'\"]+)[\'\"]/',
                $file->getContents(),
                $matches
            );

            foreach ($matches[1] ?? [] as $table) {
                $expectedTables[] = $table;
            }
        }

        $expectedTables = array_values(array_unique($expectedTables));
        $this->assertNotEmpty($expectedTables);

        $missing = array_values(array_filter(
            $expectedTables,
            static fn (string $table): bool => !Schema::hasTable($table)
        ));

        $this->assertSame([], $missing);
    }

    public function test_every_business_owned_model_uses_business_isolation_guard(): void
    {
        $missing = [];

        foreach (File::allFiles(app_path('Models')) as $file) {
            if ($file->getFilename() === 'BelongsToBusiness.php' || $file->getExtension() !== 'php') {
                continue;
            }

            $source = $file->getContents();
            $namespace = preg_match('/namespace\s+([^;]+);/', $source, $namespaceMatch)
                ? $namespaceMatch[1]
                : null;
            $class = preg_match('/\bclass\s+(\w+)/', $source, $classMatch)
                ? $classMatch[1]
                : null;

            if (!$namespace || !$class) {
                continue;
            }

            $className = $namespace.'\\'.$class;

            if (!class_exists($className) || !is_subclass_of($className, Model::class)) {
                continue;
            }

            $reflection = new \ReflectionClass($className);

            if ($reflection->isAbstract()) {
                continue;
            }

            /** @var Model $model */
            $model = $reflection->newInstance();

            if (!Schema::hasColumn($model->getTable(), 'business_id')) {
                continue;
            }

            $usesGuard = in_array(
                BelongsToBusiness::class,
                class_uses_recursive($className),
                true
            );

            if (!$usesGuard) {
                $missing[] = $file->getRelativePathname();
            }
        }

        $this->assertSame([], $missing);
    }

    public function test_every_concrete_application_model_points_to_an_existing_table(): void
    {
        $missing = [];

        foreach (File::allFiles(app_path('Models')) as $file) {
            if ($file->getFilename() === 'BelongsToBusiness.php' || $file->getExtension() !== 'php') {
                continue;
            }

            $source = $file->getContents();
            $namespace = preg_match('/namespace\s+([^;]+);/', $source, $namespaceMatch)
                ? $namespaceMatch[1]
                : null;
            $class = preg_match('/\bclass\s+(\w+)/', $source, $classMatch)
                ? $classMatch[1]
                : null;

            if (!$namespace || !$class) {
                continue;
            }

            $className = $namespace.'\\'.$class;

            if (!class_exists($className) || !is_subclass_of($className, Model::class)) {
                continue;
            }

            $reflection = new \ReflectionClass($className);

            if ($reflection->isAbstract()) {
                continue;
            }

            /** @var Model $model */
            $model = $reflection->newInstance();
            $table = $model->getTable();

            if (!Schema::hasTable($table)) {
                $missing[] = $file->getRelativePathname().' -> '.$table;
            }
        }

        $this->assertSame([], $missing);
    }
}
