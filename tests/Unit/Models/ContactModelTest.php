<?php

namespace Tests\Unit\Models;

use App\Models\Contact;
use Database\Seeders\ContactSubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ContactSubjectSeeder::class);
    }

    /** @test */
    public function subject_label_uses_admin_label_for_known_slug(): void
    {
        $contact = new Contact(['subject' => 'pension']);

        $this->assertSame('Question sur les pensions', $contact->subjectLabel());
    }

    /** @test */
    public function subject_label_falls_back_to_raw_value_for_custom_text(): void
    {
        $contact = new Contact(['subject' => 'Demande PRAP']);

        $this->assertSame('Demande PRAP', $contact->subjectLabel());
    }

    /** @test */
    public function is_custom_subject_is_true_when_slug_is_unknown(): void
    {
        $contact = new Contact(['subject' => 'Demande PRAP']);

        $this->assertTrue($contact->isCustomSubject());
    }

    /** @test */
    public function is_custom_subject_is_false_for_catalog_slug(): void
    {
        $contact = new Contact(['subject' => 'documents']);

        $this->assertFalse($contact->isCustomSubject());
    }

    /** @test */
    public function status_label_maps_known_statuses(): void
    {
        $this->assertSame('Nouveau', (new Contact(['status' => Contact::STATUS_NOUVEAU]))->statusLabel());
        $this->assertSame('En cours', (new Contact(['status' => Contact::STATUS_EN_COURS]))->statusLabel());
        $this->assertSame('Traité', (new Contact(['status' => Contact::STATUS_TRAITE]))->statusLabel());
    }

    /** @test */
    public function transition_to_traite_marks_message_as_read(): void
    {
        $contact = Contact::create([
            'first_name' => 'Alice',
            'last_name' => 'Test',
            'email' => 'alice@example.com',
            'telephone' => '+50938123456',
            'subject' => 'pension',
            'message' => 'Message suffisamment long.',
            'status' => Contact::STATUS_EN_COURS,
            'read' => false,
        ]);

        $contact->transitionTo(Contact::STATUS_TRAITE);

        $contact->refresh();
        $this->assertSame(Contact::STATUS_TRAITE, $contact->status);
        $this->assertTrue($contact->read);
    }

    /** @test */
    public function transition_to_ignores_unknown_status(): void
    {
        $contact = Contact::create([
            'first_name' => 'Bob',
            'last_name' => 'Test',
            'email' => 'bob@example.com',
            'telephone' => '+50938123456',
            'subject' => 'pension',
            'message' => 'Message suffisamment long.',
            'status' => Contact::STATUS_NOUVEAU,
            'read' => false,
        ]);

        $contact->transitionTo('archive');

        $contact->refresh();
        $this->assertSame(Contact::STATUS_NOUVEAU, $contact->status);
        $this->assertFalse($contact->read);
    }
}
