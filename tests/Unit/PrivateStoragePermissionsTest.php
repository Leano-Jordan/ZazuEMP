<?php

namespace Tests\Unit;

use App\Support\PrivateStoragePermissions;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class PrivateStoragePermissionsTest extends TestCase
{
    public function test_private_staging_tree_is_owner_only_on_posix_systems(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Windows uses inherited NTFS ACLs rather than POSIX mode bits.');
        }

        $root = sys_get_temp_dir().'/zazu-private-stage-'.Str::uuid();
        $nested = $root.'/nested';
        $secret = $nested.'/database-dump.sql';
        $outside = $root.'-outside';
        $link = $root.'/linked-secret';
        $permissions = app(PrivateStoragePermissions::class);

        try {
            $this->assertTrue($permissions->createDirectory($root));
            $this->assertTrue(mkdir($nested, 0755));
            $this->assertNotFalse(file_put_contents($secret, 'sensitive installation data'));
            $this->assertTrue($permissions->restrictDirectoryTree($root));

            $this->assertSame(0700, fileperms($root) & 0777);
            $this->assertSame(0700, fileperms($nested) & 0777);
            $this->assertSame(0600, fileperms($secret) & 0777);

            $this->assertNotFalse(file_put_contents($outside, 'outside sensitive data'));
            $this->assertTrue(symlink($outside, $link));
            $this->assertFalse($permissions->restrictDirectoryTree($root));
            unlink($link);
            unlink($outside);
        } finally {
            if (is_link($link)) {
                unlink($link);
            }
            if (is_file($outside)) {
                unlink($outside);
            }
            File::deleteDirectory($root);
        }
    }
}
