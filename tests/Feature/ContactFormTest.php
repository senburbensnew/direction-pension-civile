<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\ContactSubject;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\ContactSubjectSeeder;
use Database\Seeders\DirectionDepartementaleSeeder;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
    public function contact_page_includes_destinataire_and_message_limit(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertSee('Destinataire')
            ->assertSee('name="destinataire"', false)
            ->assertSee('maxlength="' . Contact::MESSAGE_MAX_LENGTH . '"', false)
            ->assertSee('Maximum ' . Contact::MESSAGE_MAX_LENGTH . ' caractères');
    }

    /** @test */
    public function contact_form_requires_a_destinataire(): void
    {
        $this->from('/contact')->post('/contact', $this->payload(['destinataire' => '']))
            ->assertRedirect('/contact')
            ->assertSessionHasErrors('destinataire');

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
    public function contact_form_stores_destinataire(): void
    {
        $this->from('/contact')->post('/contact', $this->payload())
            ->assertRedirect('/contact')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('contacts', [
            'email' => 'jean@example.com',
            'telephone' => '+50938123456',
            'destinataire' => Contact::destinataireKey('service', Service::SECRETARIAT),
        ]);
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

        $this->assertDatabaseMissing('contact_subjects', ['slug' => 'reclamation-de-paiement']);
        $this->get('/contact')->assertDontSee('Réclamation de paiement');
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Jean',
            'last_name' => 'Pierre',
            'email' => 'jean@example.com',
            'telephone' => '+50938123456',
            'destinataire' => Contact::destinataireKey('service', Service::SECRETARIAT),
            'subject' => 'pension',
            'message' => 'Bonjour, j’ai une question sur ma pension.',
        ], $overrides);
    }
}
