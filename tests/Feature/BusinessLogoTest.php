<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BusinessLogoTest extends TestCase
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

    private function invalidImage(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'logo-test-');
        $this->temporaryFiles[] = $path;
        file_put_contents($path, 'not an image');

        return new UploadedFile($path, 'logo.jpg', 'image/jpeg', null, true);
    }

    public function createApplication()
    {
        $app = parent::createApplication();
        if (! $app->environment('testing') || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:') {
            throw new \LogicException('Logo tests require testing + SQLite :memory:.');
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

    private function adminWithLogo(): array
    {
        $tenant = Tenant::create(['nombre' => 'Negocio de prueba', 'activo' => true]);
        $path = "logos/{$tenant->id}/previous.png";
        Storage::disk('public')->put($path, 'previous logo');
        $tenant->update(['configuracion' => ['logo' => $path, 'negocio' => ['nombre_mostrar' => 'Prueba']]]);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'rol' => 'admin']);
        $this->actingAs($user);

        return [$tenant, $path];
    }

    public static function formats(): array
    {
        return [['jpg'], ['png'], ['webp'], ['gif'], ['bmp']];
    }

    private function image(string $format): UploadedFile
    {
        if ($format === 'webp') {
            return UploadedFile::fake()->createWithContent('logo.webp', base64_decode('UklGRjYAAABXRUJQVlA4ICoAAACQAQCdASoIAAgAAUAmJaACdLoAA5gA/vBsr+6L7UVQf/5Z/5/fF3owAAA='));
        }
        if ($format === 'bmp') {
            $image = imagecreatetruecolor(10, 10);
            ob_start();
            imagebmp($image);
            $content = ob_get_clean();
            imagedestroy($image);

            return UploadedFile::fake()->createWithContent('logo.bmp', $content);
        }

        return UploadedFile::fake()->image('logo.'.$format);
    }

    #[DataProvider('formats')]
    public function test_valid_formats_replace_logo_and_persist_on_reload(string $format): void
    {
        [$tenant, $previous] = $this->adminWithLogo();
        $this->from('/configuracion')->post('/configuracion/apariencia', ['logo' => $this->image($format)])
            ->assertRedirect('/configuracion')->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Apariencia y logotipo guardados correctamente.');
        $new = $tenant->fresh()->configuracion['logo'];
        $this->assertNotSame($previous, $new);
        Storage::disk('public')->assertExists($new);
        Storage::disk('public')->assertMissing($previous);
        $this->assertCount(1, Storage::disk('public')->allFiles());
        // A new HTTP request resolves a fresh authenticated user's relations.
        auth()->user()->unsetRelation('tenant');
        $this->get('/configuracion')->assertInertia(fn (Assert $page) => $page
            ->component('Configuracion/Index')->where('logoUrl', '/storage/'.$new));
        $this->assertSame('Prueba', $tenant->fresh()->configuracion['negocio']['nombre_mostrar']);
    }

    public static function rejectedFiles(): array
    {
        return [['invalid', 'Selecciona una imagen JPG, PNG, WebP, GIF o BMP válida.'],
            ['oversize', 'El logotipo debe pesar como máximo 2 MB.'],
            ['php-limit', 'El logotipo debe pesar como máximo 2 MB.'],
            ['partial', 'La carga del logotipo se interrumpió. Selecciona el archivo e inténtalo de nuevo.']];
    }

    #[DataProvider('rejectedFiles')]
    public function test_rejected_upload_preserves_previous_logo_and_has_spanish_error(string $kind, string $message): void
    {
        [$tenant, $previous] = $this->adminWithLogo();
        $photo = match ($kind) {
            'invalid' => $this->invalidImage(),
            'oversize' => UploadedFile::fake()->image('logo.jpg')->size(2049),
            default => new UploadedFile(__FILE__, 'logo.jpg', 'image/jpeg',
                $kind === 'php-limit' ? UPLOAD_ERR_INI_SIZE : UPLOAD_ERR_PARTIAL, true),
        };
        $this->post('/configuracion/apariencia', ['logo' => $photo, 'eliminar_logo' => true])
            ->assertSessionHasErrors(['logo' => $message])->assertSessionMissing('success');
        $this->assertSame($previous, $tenant->fresh()->configuracion['logo']);
        Storage::disk('public')->assertExists($previous);
        $this->assertCount(1, Storage::disk('public')->allFiles());
    }

    public function test_exact_limit_is_accepted_and_deletion_persists(): void
    {
        [$tenant] = $this->adminWithLogo();
        $this->post('/configuracion/apariencia', ['logo' => UploadedFile::fake()->image('logo.jpg')->size(2048)])
            ->assertSessionHasNoErrors();
        $new = $tenant->fresh()->configuracion['logo'];
        $this->post('/configuracion/apariencia', ['eliminar_logo' => true])
            ->assertSessionHasNoErrors()->assertSessionHas('success', 'Logotipo eliminado correctamente.');
        $this->assertNull($tenant->fresh()->configuracion['logo']);
        Storage::disk('public')->assertMissing($new);
        auth()->user()->unsetRelation('tenant');
        $this->get('/configuracion')->assertInertia(fn (Assert $page) => $page->where('logoUrl', null));
    }

    public function test_palette_only_does_not_remove_logo(): void
    {
        [$tenant, $previous] = $this->adminWithLogo();
        $this->post('/configuracion/apariencia', ['paleta' => ['primario' => '#15803d']])->assertSessionHasNoErrors();
        $this->assertSame($previous, $tenant->fresh()->configuracion['logo']);
        $this->assertSame('#15803d', $tenant->fresh()->configuracion['colores']['primario']);
        Storage::disk('public')->assertExists($previous);
    }

    public static function storageFailures(): array
    {
        return [[false], [true]];
    }

    #[DataProvider('storageFailures')]
    public function test_storage_failure_preserves_logo(bool $throws): void
    {
        [$tenant, $previous] = $this->adminWithLogo();
        $disk = Storage::disk('public');
        $failed = \Mockery::mock(FilesystemAdapter::class);
        $write = $failed->shouldReceive('putFileAs')->once();
        if ($throws) {
            $write->andThrow(new \RuntimeException('Isolated write failure'));
        } else {
            $write->andReturn(false);
        }
        $failed->shouldReceive('url')->with($previous)->andReturn('/storage/'.$previous);
        Storage::shouldReceive('disk')->with('public')->andReturn($failed);
        $this->post('/configuracion/apariencia', ['logo' => $this->image('png')])
            ->assertSessionHasErrors('logo')->assertSessionMissing('success');
        $this->assertSame($previous, $tenant->fresh()->configuracion['logo']);
        $disk->assertExists($previous);
    }

    public function test_database_failure_cleans_new_upload_and_preserves_previous_logo(): void
    {
        [$tenant, $previous] = $this->adminWithLogo();
        Tenant::updating(function () { throw new \RuntimeException('Isolated persistence failure'); });
        $this->post('/configuracion/apariencia', ['logo' => $this->image('jpg')])
            ->assertSessionHasErrors('logo')->assertSessionMissing('success');
        $this->assertSame($previous, $tenant->fresh()->configuracion['logo']);
        Storage::disk('public')->assertExists($previous);
        $this->assertCount(1, Storage::disk('public')->allFiles());
    }
}
