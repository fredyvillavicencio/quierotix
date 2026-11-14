<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_participants_table_has_email_sent_column()
    {
        // First create an event since participants require event_id
        $event = Event::create([
            'codigo_evento' => 'TEST-EVENT-1',
            'nombre' => 'Test Event',
            'descripcion' => 'Test Description',
            'fecha' => '2024-12-31',
            'hora' => '18:00:00',
            'lugar' => 'Test Location',
        ]);

        // Create a participant with default values
        $participant = Participant::create([
            'event_id' => $event->id,
            'nombre' => 'John',
            'apellido' => 'Doe',
            'email' => 'john@example.com',
            'pdf_paths' => null,
        ]);

        // Check that email_sent column exists and has default value false
        $this->assertDatabaseHas('participants', [
            'id' => $participant->id,
            'email_sent' => false,
        ]);

        // Update email_sent to true
        $participant->update(['email_sent' => true]);

        // Verify the update worked
        $this->assertDatabaseHas('participants', [
            'id' => $participant->id,
            'email_sent' => true,
        ]);
    }

    public function test_email_sent_column_is_boolean_type()
    {
        // First create an event since participants require event_id
        $event = Event::create([
            'codigo_evento' => 'TEST-EVENT-2',
            'nombre' => 'Test Event',
            'descripcion' => 'Test Description',
            'fecha' => '2024-12-31',
            'hora' => '18:00:00',
            'lugar' => 'Test Location',
        ]);

        // Create a participant
        $participant = Participant::create([
            'event_id' => $event->id,
            'nombre' => 'Jane',
            'apellido' => 'Smith',
            'email' => 'jane@example.com',
            'pdf_paths' => null,
        ]);

        // Check that email_sent is a boolean
        $participant->refresh();
        $this->assertIsBool($participant->email_sent);
        $this->assertFalse($participant->email_sent);

        // Set to true and verify
        $participant->email_sent = true;
        $participant->save();

        $participant->refresh();
        $this->assertTrue($participant->email_sent);
    }

    public function test_email_sent_is_mass_assignable()
    {
        // First create an event since participants require event_id
        $event = Event::create([
            'codigo_evento' => 'TEST-EVENT-3',
            'nombre' => 'Test Event',
            'descripcion' => 'Test Description',
            'fecha' => '2024-12-31',
            'hora' => '18:00:00',
            'lugar' => 'Test Location',
        ]);

        // Test that email_sent can be mass assigned
        $data = [
            'event_id' => $event->id,
            'nombre' => 'John',
            'apellido' => 'Doe',
            'email' => 'john@example.com',
            'pdf_paths' => null,
            'email_sent' => true,
        ];

        $participant = Participant::create($data);

        $this->assertDatabaseHas('participants', [
            'id' => $participant->id,
            'email_sent' => true,
        ]);
    }
}
