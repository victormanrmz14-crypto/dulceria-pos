<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProfileAvatarTest extends TestCase
{
    use RefreshDatabase;

    private array $temporaryFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $file) {
            @unlink($file);
        }
        parent::tearDown();
    }

    private function spoofedImage(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'avatar-test-');
        $this->temporaryFiles[] = $path;
        file_put_contents($path, '<?php echo "not an image";');

        return new UploadedFile($path, 'photo.png', 'image/png', null, true);
    }

    public function createApplication()
    {
        $app = parent::createApplication();
        if (! $app->environment('testing') || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:') {
            throw new \LogicException('Avatar tests require testing + SQLite :memory:.');
        }

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['inertia.pages.paths' => [resource_path('js/Pages')]]);
        Storage::fake('public');
    }

    public static function imageFormats(): array
    {
        return [['jpg'], ['png'], ['webp']];
    }

    #[DataProvider('imageFormats')]
    public function test_user_can_upload_a_valid_photo_and_only_url_is_shared(string $format): void
    {
        $user = User::factory()->create();
        $photo = $format === 'webp'
            ? UploadedFile::fake()->createWithContent('portrait.webp', base64_decode('UklGRjYAAABXRUJQVlA4ICoAAACQAQCdASoIAAgAAUAmJaACdLoAA5gA/vBsr+6L7UVQf/5Z/5/fF3owAAA='))
            : UploadedFile::fake()->image('portrait.'.$format);

        $this->actingAs($user)->from('/perfil')->post('/perfil/avatar', ['avatar' => $photo])
            ->assertRedirect('/perfil')->assertSessionHasNoErrors();

        $path = $user->fresh()->avatar_path;
        $this->assertTrue($user->ownsAvatarPath($path));
        Storage::disk('public')->assertExists($path);
        $this->get('/perfil')->assertInertia(fn (Assert $page) => $page
            ->component('Perfil/Index')->where('auth.user.avatar_url', '/storage/'.$path)
            ->missing('auth.user.avatar_path'));
        $this->assertArrayNotHasKey('avatar_path', $user->fresh()->toArray());
    }

    public function test_guest_cannot_view_upload_or_delete_avatar(): void
    {
        $this->get('/perfil')->assertRedirect('/login');
        $this->post('/perfil/avatar', ['avatar' => UploadedFile::fake()->image('photo.png')])->assertRedirect('/login');
        $this->delete('/perfil/avatar')->assertRedirect('/login');
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public static function invalidFiles(): array
    {
        return [['svg'], ['gif'], ['spoofed'], ['oversize']];
    }

    #[DataProvider('invalidFiles')]
    public function test_invalid_or_oversized_files_leave_existing_avatar_intact(string $kind): void
    {
        $user = User::factory()->create();
        $path = 'avatars/'.$user->id.'/'.str_repeat('a', 40).'.png';
        Storage::disk('public')->put($path, 'previous');
        $user->avatar_path = $path;
        $user->save();
        $photo = match ($kind) {
            'svg' => UploadedFile::fake()->createWithContent('photo.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>'),
            'gif' => UploadedFile::fake()->image('photo.gif'),
            'spoofed' => $this->spoofedImage(),
            'oversize' => UploadedFile::fake()->image('photo.jpg')->size(2049),
        };

        $this->actingAs($user)->post('/perfil/avatar', ['avatar' => $photo])->assertSessionHasErrors('avatar');
        $this->assertSame($path, $user->fresh()->avatar_path);
        Storage::disk('public')->assertExists($path);
        $this->assertCount(1, Storage::disk('public')->allFiles());
    }

    public function test_replacement_and_deletion_remove_only_the_previous_own_file(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/perfil/avatar', ['avatar' => UploadedFile::fake()->image('one.jpg')->size(2048)])
            ->assertSessionHasNoErrors();
        $old = $user->fresh()->avatar_path;
        $this->post('/perfil/avatar', ['avatar' => UploadedFile::fake()->image('../../untrusted.png')])
            ->assertSessionHasNoErrors();
        $new = $user->fresh()->avatar_path;
        $this->assertNotSame($old, $new);
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($new);
        $this->delete('/perfil/avatar')->assertSessionHasNoErrors();
        $this->assertNull($user->fresh()->avatar_path);
        Storage::disk('public')->assertMissing($new);
        $this->get('/perfil')->assertInertia(fn (Assert $page) => $page->where('auth.user.avatar_url', null));
    }

    public function test_avatar_persists_on_reload_logout_and_real_login(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/perfil/avatar', ['avatar' => UploadedFile::fake()->image('photo.jpg')]);
        $path = $user->fresh()->avatar_path;
        $this->get('/perfil')->assertInertia(fn (Assert $page) => $page->where('auth.user.avatar_url', '/storage/'.$path));
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->get('/perfil')->assertInertia(fn (Assert $page) => $page->where('auth.user.avatar_url', '/storage/'.$path));
        Storage::disk('public')->assertExists($path);
    }

    public function test_failed_storage_keeps_the_previous_photo(): void
    {
        $user = User::factory()->create();
        $path = 'avatars/'.$user->id.'/'.str_repeat('c', 40).'.png';
        $disk = Storage::disk('public');
        $disk->put($path, 'previous');
        $user->avatar_path = $path;
        $user->save();
        $failedDisk = \Mockery::mock(\Illuminate\Filesystem\FilesystemAdapter::class);
        $failedDisk->shouldReceive('putFileAs')->once()->andReturn(false);
        $failedDisk->shouldReceive('url')->with($path)->andReturn('/storage/'.$path);
        Storage::shouldReceive('disk')->with('public')->andReturn($failedDisk);

        $this->actingAs($user)->post('/perfil/avatar', ['avatar' => UploadedFile::fake()->image('new.png')])
            ->assertSessionHasErrors('avatar');
        $this->assertSame($path, $user->fresh()->avatar_path);
        $disk->assertExists($path);
    }

    public function test_failed_database_update_cleans_new_upload_and_keeps_previous_photo(): void
    {
        $user = User::factory()->create();
        $path = 'avatars/'.$user->id.'/'.str_repeat('d', 40).'.png';
        Storage::disk('public')->put($path, 'previous');
        $user->avatar_path = $path;
        $user->save();
        User::updating(function () { throw new \RuntimeException('Isolated avatar persistence failure'); });

        $this->actingAs($user)->post('/perfil/avatar', ['avatar' => UploadedFile::fake()->image('new.png')])->assertStatus(500);
        $this->assertSame($path, $user->fresh()->avatar_path);
        Storage::disk('public')->assertExists($path);
        $this->assertCount(1, Storage::disk('public')->allFiles());
    }

    public function test_user_id_and_path_cannot_target_someone_else_or_change_identity(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $original = $user->getAttributes();
        $this->actingAs($user)->post('/perfil/avatar', [
            'avatar' => UploadedFile::fake()->image('photo.jpg'), 'user_id' => $other->id,
        ])->assertSessionHasErrors('user_id');
        $this->delete('/perfil/avatar', ['avatar_path' => 'logos/business.png'])->assertSessionHasErrors('avatar_path');
        $this->post('/perfil/avatar', [
            'avatar' => UploadedFile::fake()->image('photo.jpg'), 'rol' => 'admin',
            'tenant_id' => 900, 'password' => 'changed',
        ])->assertSessionHasNoErrors();
        $this->assertNull($other->fresh()->avatar_path);
        foreach (['rol', 'tenant_id', 'password'] as $attribute) {
            $this->assertSame($original[$attribute] ?? null, $user->fresh()->getAttribute($attribute));
        }
    }

    public function test_delete_never_removes_paths_outside_own_avatar_directory(): void
    {
        $user = User::factory()->create();
        foreach (['logos/business.png', 'avatars/999/'.str_repeat('b', 40).'.png', 'avatars/'.$user->id.'/../outside.png'] as $path) {
            Storage::disk('public')->put($path, 'keep');
            $user->avatar_path = $path;
            $user->save();
            $this->actingAs($user)->delete('/perfil/avatar')->assertSessionHasNoErrors();
            $this->assertNull($user->fresh()->avatar_path);
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_cashier_profile_does_not_grant_administrative_settings_access(): void
    {
        $user = User::factory()->create(['rol' => 'cajero']);
        $this->actingAs($user)->get('/perfil')->assertOk();
        $this->get('/configuracion')->assertForbidden();
        foreach (['apariencia', 'negocio', 'tickets'] as $section) {
            $this->post('/configuracion/'.$section)->assertForbidden();
        }
        $admin = User::factory()->create(['rol' => 'admin']);
        $this->actingAs($admin)->get('/perfil')->assertOk();
        $this->get('/configuracion')->assertOk();
    }
}
