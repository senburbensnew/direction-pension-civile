<?php

namespace Tests\Feature;

use App\Services\Form\FormUploadStashService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FormUploadStashTest extends TestCase
{
    use RefreshDatabase;

    public function test_stash_and_merge_restores_file_on_next_post(): void
    {
        Storage::fake('local');

        $service = app(FormUploadStashService::class);
        $session = $this->app['session.store'];
        $session->start();

        $route = (new Route('POST', '/demande-compte', []))->name('demandes.compte.store');

        $stashRequest = Request::create('/demande-compte', 'POST', [], [], [
            'piece_identite' => UploadedFile::fake()->create('carte.pdf', 100, 'application/pdf'),
        ]);
        $stashRequest->setLaravelSession($session);
        $stashRequest->setRouteResolver(fn () => $route);

        $service->stashFromRequest($stashRequest);

        $retryRequest = Request::create('/demande-compte', 'POST', [
            'email' => 'test@example.com',
        ]);
        $retryRequest->setLaravelSession($session);
        $retryRequest->setRouteResolver(fn () => $route);

        $this->assertSame(
            'carte.pdf',
            $service->displayNamesForRequest($retryRequest)['piece_identite'] ?? null
        );

        $payload = $session->get('form_upload_stash.demandes.compte.store');
        $storedPath = $payload['fields']['piece_identite'][0]['path'];
        $this->assertFileIsReadable(Storage::disk('local')->path($storedPath));

        $rebuild = new \ReflectionMethod($service, 'uploadedFilesFromMeta');
        $rebuild->setAccessible(true);
        $this->assertCount(
            1,
            $rebuild->invoke($service, $payload['fields']['piece_identite'])
        );

        $fieldsMethod = new \ReflectionMethod($service, 'fields');
        $fieldsMethod->setAccessible(true);
        $keyMethod = new \ReflectionMethod($service, 'keyForRequest');
        $keyMethod->setAccessible(true);
        $stashKey = $keyMethod->invoke($service, $retryRequest);
        $fieldsMap = $fieldsMethod->invoke($service, $stashKey, $retryRequest);
        $this->assertSame(
            json_encode($payload['fields']['piece_identite']),
            json_encode($fieldsMap['piece_identite'] ?? null)
        );
        $this->assertCount(1, $rebuild->invoke($service, $fieldsMap['piece_identite']));

        $this->assertTrue($service->mergeIntoRequest($retryRequest));
        $merged = $retryRequest->files->get('piece_identite');
        $this->assertInstanceOf(UploadedFile::class, $merged);
        $this->assertSame('carte.pdf', $merged->getClientOriginalName());
    }
}
