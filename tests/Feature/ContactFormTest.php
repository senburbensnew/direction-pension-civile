<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\ContactSubject;
use App\Models\User;
use App\Notifications\ContactFormAcknowledgmentNotification;
use App\Notifications\ContactFormReceivedNotification;
use Database\Seeders\ContactSubjectSeeder;
use Database\Seeders\DirectionDepartementaleSeeder;
use Database\Seeders\ParametersSeeder;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class ContactFormTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ContactSubjectSeeder::class);
        $this->seed(ServiceSeeder::class);
        $this->seed(DirectionDepartementaleSeeder::class);
        $this->seed(ParametersSeeder::class);
        RateLimiter::clear('127.0.0.1');
    }

    /** @test */
    public function contact_page_renders_form_sections_and_service_phone_lines(): void
    {
        $line = config('contact.service_phones.0');

        $this->get('/contact')
            ->assertOk()
            ->assertSee('Nos coordonnées', false)
            ->assertSee('Envoyer un message', false)
            ->assertSee('Numéros de contact', false)
            ->assertSee($line['display'], false)
            ->assertSee('Nos Directions Départementales', false)
            ->assertSee('Nos Services', false)
            ->assertDontSee('Pour votre sécurité', false)
            ->assertDontSee('Nous joindre', false);
    }

    /** @test */
    public function contact_form_end_to_end_submission_shows_success_and_persists_message(): void
    {
        Notification::fake();

        $this->get('/contact')->assertOk();

        $this->from('/contact')->post('/contact', $this->payload([
            'first_name' => 'Marie',
            'last_name' => 'Joseph',
            'email' => 'marie.joseph@example.com',
            'telephone' => '+50937001234',
            'subject' => 'rendezvous',
            'message' => 'Je souhaite prendre rendez-vous au service accueil.',
        ]))
            ->assertRedirect(route('contact'))
            ->assertSessionHas('success');

        $this->get('/contact')
            ->assertOk()
            ->assertSee('Votre message a été enregistré', false);

        $this->assertDatabaseHas('contacts', [
            'first_name' => 'Marie',
            'last_name' => 'Joseph',
            'email' => 'marie.joseph@example.com',
            'telephone' => '+50937001234',
            'subject' => 'rendezvous',
            'message' => 'Je souhaite prendre rendez-vous au service accueil.',
            'status' => Contact::STATUS_NOUVEAU,
        ]);

        Notification::assertSentOnDemand(ContactFormReceivedNotification::class);
        Notification::assertSentOnDemand(ContactFormAcknowledgmentNotification::class);
    }

    /** @test */
    public function contact_form_requires_all_mandatory_fields(): void
    {
        $this->from('/contact')->post('/contact', [])
            ->assertRedirect('/contact')
            ->assertSessionHasErrors(['first_name', 'last_name', 'email', 'telephone', 'subject', 'message']);

        $this->assertSame(0, Contact::count());
    }

    /** @test */
    public function contact_form_rejects_invalid_email_and_subject(): void
    {
        $this->from('/contact')->post('/contact', $this->payload(['email' => 'not-an-email']))
            ->assertRedirect('/contact')
            ->assertSessionHasErrors('email');

        $this->from('/contact')->post('/contact', $this->payload(['subject' => 'invalid-slug']))
            ->assertRedirect('/contact')
            ->assertSessionHasErrors('subject');

        $this->assertSame(0, Contact::count());
    }

    /** @test */
    public function contact_page_includes_telephone_field(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertSee('name="telephone"', false)
            ->assertSee('Téléphone');
    }

    /** @test */
    public function contact_form_requires_a_valid_haitian_telephone(): void
    {
        $this->from('/contact')->post('/contact', $this->payload(['telephone' => '']))
            ->assertRedirect('/contact')
            ->assertSessionHasErrors('telephone');

        $this->from('/contact')->post('/contact', $this->payload(['telephone' => '38123456']))
            ->assertRedirect('/contact')
            ->assertSessionHasErrors('telephone');

        $this->assertSame(0, Contact::count());
    }

    /** @test */
    public function contact_page_includes_message_limit(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertDontSee('name="destinataire"', false)
            ->assertSee('maxlength="'.Contact::MESSAGE_MAX_LENGTH.'"', false)
            ->assertSee('Entre 10 et '.Contact::MESSAGE_MAX_LENGTH.' caractères');
    }

    /** @test */
    public function contact_form_rejects_a_message_shorter_than_ten_characters(): void
    {
        $this->from('/contact')->post('/contact', $this->payload(['message' => 'Court']))
            ->assertRedirect('/contact')
            ->assertSessionHasErrors('message');

        $this->assertSame(0, Contact::count());
    }

    /** @test */
    public function contact_form_rejects_a_message_over_the_limit(): void
    {
        $this->from('/contact')->post('/contact', $this->payload([
            'message' => str_repeat('a', Contact::MESSAGE_MAX_LENGTH + 1),
        ]))
            ->assertRedirect('/contact')
            ->assertSessionHasErrors('message');

        $this->assertSame(0, Contact::count());
    }

    /** @test */
    public function contact_form_stores_nouveau_status(): void
    {
        Notification::fake();

        $this->from('/contact')->post('/contact', $this->payload())
            ->assertRedirect('/contact')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('contacts', [
            'email' => 'jean@example.com',
            'telephone' => '+50938123456',
            'destinataire' => null,
            'status' => Contact::STATUS_NOUVEAU,
            'read' => false,
        ]);

        Notification::assertSentOnDemand(ContactFormReceivedNotification::class);
        Notification::assertSentOnDemand(ContactFormAcknowledgmentNotification::class);
    }

    /** @test */
    public function contact_form_is_throttled_after_five_submissions_per_minute(): void
    {
        Notification::fake();

        for ($i = 0; $i < 5; $i++) {
            $this->from('/contact')->post('/contact', $this->payload([
                'email' => "jean{$i}@example.com",
            ]))->assertRedirect('/contact');
        }

        $this->from('/contact')->post('/contact', $this->payload([
            'email' => 'sixieme@example.com',
        ]))->assertStatus(429);
    }

    /** @test */
    public function contact_page_offers_a_custom_subject_option(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertSee('Autre (préciser le sujet)')
            ->assertSee('name="custom_subject"', false);
    }

    /** @test */
    public function custom_subject_is_required_when_autre_is_selected(): void
    {
        $this->from('/contact')->post('/contact', $this->payload([
            'subject' => 'autre',
            'custom_subject' => '',
        ]))
            ->assertRedirect('/contact')
            ->assertSessionHasErrors('custom_subject');

        $this->assertSame(0, Contact::count());
    }

    /** @test */
    public function contact_form_stores_a_custom_subject(): void
    {
        Notification::fake();

        $this->from('/contact')->post('/contact', $this->payload([
            'subject' => 'autre',
            'custom_subject' => 'Demande d’information sur le PRAP',
        ]))
            ->assertRedirect('/contact')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('contacts', [
            'email' => 'jean@example.com',
            'subject' => 'Demande d’information sur le PRAP',
        ]);
    }

    /** @test */
    public function contact_page_only_shows_active_admin_subjects(): void
    {
        ContactSubject::where('slug', 'documents')->update(['is_active' => false]);
        ContactSubject::create([
            'slug' => 'prap',
            'label' => 'Question sur le PRAP',
            'position' => 10,
            'is_active' => true,
            'allows_custom' => false,
        ]);

        $this->get('/contact')
            ->assertOk()
            ->assertSee('Question sur les pensions')
            ->assertSee('Question sur le PRAP')
            ->assertDontSee('Demande de documents', false);
    }

    /** @test */
    public function admin_can_add_and_remove_a_contact_subject(): void
    {
        $this->seedRoles();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->from(route('admin.contact-parameters.index'))
            ->post(route('admin.contact-subjects.store'), [
                'label' => 'Réclamation de paiement',
                'position' => 5,
                'is_active' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('contact_subjects', [
            'label' => 'Réclamation de paiement',
            'slug' => 'reclamation-de-paiement',
        ]);

        $this->get('/contact')->assertSee('Réclamation de paiement');

        $subject = ContactSubject::where('slug', 'reclamation-de-paiement')->first();

        $this->actingAs($admin)
            ->delete(route('admin.contact-subjects.destroy', $subject))
            ->assertRedirect();

        $this->assertSoftDeleted('contact_subjects', ['slug' => 'reclamation-de-paiement']);
        $this->get('/contact')->assertDontSee('Réclamation de paiement');
    }

    /** @test */
    public function opening_a_nouveau_contact_in_admin_moves_it_to_en_cours(): void
    {
        $this->seedRoles();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $contact = Contact::create([
            'first_name' => 'Marie',
            'last_name' => 'Dupont',
            'email' => 'marie@example.com',
            'telephone' => '+50938123456',
            'subject' => 'pension',
            'message' => 'Message de test suffisamment long.',
            'status' => Contact::STATUS_NOUVEAU,
            'read' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.contacts.show', $contact))
            ->assertOk();

        $contact->refresh();
        $this->assertSame(Contact::STATUS_EN_COURS, $contact->status);
        $this->assertFalse($contact->read);
    }

    /** @test */
    public function admin_can_mark_a_contact_as_traite(): void
    {
        $this->seedRoles();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $contact = Contact::create([
            'first_name' => 'Paul',
            'last_name' => 'Martin',
            'email' => 'paul@example.com',
            'telephone' => '+50938123456',
            'subject' => 'pension',
            'message' => 'Message de test suffisamment long.',
            'status' => Contact::STATUS_EN_COURS,
            'read' => false,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.contacts.show', $contact))
            ->post(route('admin.contacts.markTraite', $contact))
            ->assertRedirect();

        $contact->refresh();
        $this->assertSame(Contact::STATUS_TRAITE, $contact->status);
        $this->assertTrue($contact->read);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Jean',
            'last_name' => 'Pierre',
            'email' => 'jean@example.com',
            'telephone' => '+50938123456',
            'subject' => 'pension',
            'message' => 'Bonjour, j’ai une question sur ma pension.',
        ], $overrides);
    }
}
