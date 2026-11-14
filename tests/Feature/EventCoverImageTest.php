<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class EventCoverImageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array<int, string>
     */
    private array $createdFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }

        parent::tearDown();
    }

    private function createEvent(array $overrides = []): Event
    {
        return Event::create(array_merge([
            'codigo_evento' => 'evento-cover-001',
            'nombre' => 'Evento con Imagen Cover',
            'fecha' => '2032-06-10',
            'hora' => '19:00:00',
            'lugar' => 'Teatro Principal',
            'direccion' => 'Avenida Central 300',
            'precio' => 100,
        ], $overrides));
    }

    private function eventPayload(array $overrides = []): array
    {
        return array_merge([
            'codigo_evento' => 'evento-cover-admin',
            'nombre' => 'Evento Cover Administrado',
            'fecha' => '2032-07-15',
            'hora' => '20:30',
            'lugar' => 'Centro Cultural',
            'direccion' => 'Calle Cultural 400',
            'precio' => '125.50',
        ], $overrides);
    }

    public function test_admin_can_create_an_event_with_a_cover_image(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $file = UploadedFile::fake()->image('cover.jpg', 1200, 600);

        $this->actingAs($admin)
            ->post(route('admin.events.store'), $this->eventPayload([
                'imagen_cover' => $file,
            ]))
            ->assertRedirect();

        $event = Event::where('codigo_evento', 'evento-cover-admin')->firstOrFail();
        $this->createdFiles[] = public_path($event->imagen_cover);

        $this->assertNotNull($event->imagen_cover);
        $this->assertStringStartsWith('images/events/', $event->imagen_cover);
        $this->assertFileExists(public_path($event->imagen_cover));
        $this->assertSame(url('public/'.$event->imagen_cover), $event->imagen_cover_url);
    }

    public function test_admin_can_replace_and_delete_a_cover_image(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $event = $this->createEvent(['codigo_evento' => 'evento-cover-edit']);

        $oldPath = 'images/events/old-cover.jpg';
        $oldAbsolutePath = public_path($oldPath);

        if (! is_dir(dirname($oldAbsolutePath))) {
            mkdir(dirname($oldAbsolutePath), 0755, true);
        }

        file_put_contents($oldAbsolutePath, 'old cover');
        $this->createdFiles[] = $oldAbsolutePath;
        $event->update(['imagen_cover' => $oldPath]);

        $this->actingAs($admin)
            ->put(route('admin.events.update', $event), $this->eventPayload([
                'codigo_evento' => $event->codigo_evento,
                'imagen_cover' => UploadedFile::fake()->image('new-cover.jpg', 1200, 600),
            ]))
            ->assertRedirect(route('admin.events.show', $event));

        $event->refresh();
        $this->createdFiles[] = public_path($event->imagen_cover);

        $this->assertNotSame($oldPath, $event->imagen_cover);
        $this->assertFileExists(public_path($event->imagen_cover));
        $this->assertFileDoesNotExist($oldAbsolutePath);
    }

    public function test_admin_can_delete_a_cover_image_without_replacing_it(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $event = $this->createEvent(['codigo_evento' => 'evento-cover-delete']);

        $path = 'images/events/delete-cover.jpg';
        $absolutePath = public_path($path);

        if (! is_dir(dirname($absolutePath))) {
            mkdir(dirname($absolutePath), 0755, true);
        }

        file_put_contents($absolutePath, 'cover to delete');
        $this->createdFiles[] = $absolutePath;
        $event->update(['imagen_cover' => $path]);

        $this->actingAs($admin)
            ->put(route('admin.events.update', $event), $this->eventPayload([
                'codigo_evento' => $event->codigo_evento,
                'eliminar_imagen_cover' => '1',
            ]))
            ->assertRedirect(route('admin.events.show', $event));

        $this->assertNull($event->fresh()->imagen_cover);
        $this->assertFileDoesNotExist($absolutePath);
    }

    public function test_purchase_page_displays_the_cover_image_inside_the_detail_hero(): void
    {
        $event = $this->createEvent([
            'imagen_cover' => 'images/events/purchase-cover.jpg',
        ]);

        $this->get(route('events.public.purchase.create', $event))
            ->assertOk()
            ->assertSee('class="detail-hero', false)
            ->assertSee('class="detail-hero-img"', false)
            ->assertSee(url('public/images/events/purchase-cover.jpg'), false);
    }

    public function test_purchase_page_falls_back_to_the_event_image_without_a_cover_image(): void
    {
        $event = $this->createEvent([
            'imagen' => 'images/events/fallback-cover.jpg',
        ]);

        $this->get(route('events.public.purchase.create', $event))
            ->assertOk()
            ->assertSee(url('public/images/events/fallback-cover.jpg'), false);
    }

    public function test_create_and_edit_forms_include_the_cover_image_field(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $event = $this->createEvent();

        $this->actingAs($admin)
            ->get(route('admin.events.create'))
            ->assertOk()
            ->assertSee('name="imagen_cover"', false);

        $this->actingAs($admin)
            ->get(route('admin.events.edit', $event))
            ->assertOk()
            ->assertSee('name="imagen_cover"', false);
    }
}
