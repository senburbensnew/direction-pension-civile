<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\StoreContactRequest;
use App\Models\Contact;
use Database\Seeders\ContactSubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreContactRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ContactSubjectSeeder::class);
    }

    /** @test */
    public function rules_accept_a_valid_payload(): void
    {
        $validator = Validator::make($this->validPayload(), (new StoreContactRequest)->rules());

        $this->assertFalse($validator->fails(), implode(', ', $validator->errors()->all()));
    }

    /** @test */
    public function rules_require_first_name_last_name_email_telephone_subject_and_message(): void
    {
        $validator = Validator::make([], (new StoreContactRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertEqualsCanonicalizing(
            ['first_name', 'last_name', 'email', 'telephone', 'subject', 'message'],
            array_keys($validator->errors()->toArray())
        );
    }

    /** @test */
    public function rules_reject_an_unknown_subject_slug(): void
    {
        $validator = Validator::make(
            $this->validPayload(['subject' => 'inexistant']),
            (new StoreContactRequest)->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('subject', $validator->errors()->toArray());
    }

    /** @test */
    public function rules_reject_a_message_longer_than_the_model_limit(): void
    {
        $validator = Validator::make(
            $this->validPayload(['message' => str_repeat('a', Contact::MESSAGE_MAX_LENGTH + 1)]),
            (new StoreContactRequest)->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('message', $validator->errors()->toArray());
    }

    /** @test */
    public function validated_for_storage_replaces_autre_subject_with_trimmed_custom_label(): void
    {
        $request = $this->resolveValidatedRequest([
            'subject' => 'autre',
            'custom_subject' => '  Demande PRAP  ',
        ]);

        $stored = $request->validatedForStorage();

        $this->assertSame('Demande PRAP', $stored['subject']);
        $this->assertArrayNotHasKey('custom_subject', $stored);
    }

    /** @test */
    public function validated_for_storage_keeps_preset_subject_slug(): void
    {
        $request = $this->resolveValidatedRequest(['subject' => 'pension']);

        $stored = $request->validatedForStorage();

        $this->assertSame('pension', $stored['subject']);
        $this->assertArrayNotHasKey('custom_subject', $stored);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
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

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function resolveValidatedRequest(array $overrides = []): StoreContactRequest
    {
        $request = StoreContactRequest::createFrom(
            Request::create(route('contact.store'), 'POST', $this->validPayload($overrides))
        );
        $request->setContainer($this->app);
        $request->setRedirector($this->app->make('redirect'));
        $request->setRouteResolver(fn () => $this->app->make('router')->getRoutes()->getByName('contact.store'));
        $request->validateResolved();

        return $request;
    }
}
