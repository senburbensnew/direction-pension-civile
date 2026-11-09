<?php

namespace Tests\Unit\Observers;

use App\Models\Demande;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Notifications\DemandeStatusChangedNotification;
use App\Observers\DemandeObserver;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DemandeObserverTest extends TestCase
{
    /** @test */
    public function updated_does_nothing_when_workflow_step_did_not_change(): void
    {
        Notification::fake();

        $demande = new class extends Demande
        {
            public function wasChanged($attributes = null): bool
            {
                return $attributes === 'current_step_id' ? false : parent::wasChanged($attributes);
            }
        };

        (new DemandeObserver)->updated($demande);

        Notification::assertNothingSent();
    }

    /** @test */
    public function updated_skips_owner_notification_for_brouillon_step(): void
    {
        Notification::fake();

        $owner = User::factory()->make(['id' => 42, 'email' => 'owner@example.com']);
        $step = new WorkflowStep(['code' => 'BROUILLON']);

        $demande = new class extends Demande
        {
            public function wasChanged($attributes = null): bool
            {
                return $attributes === 'current_step_id';
            }

            public function load($relations)
            {
                return $this;
            }
        };
        $demande->setRelation('user', $owner);
        $demande->setRelation('currentStep', $step);

        (new DemandeObserver)->updated($demande);

        Notification::assertNothingSent();
    }

    /** @test */
    public function updated_notifies_owner_on_non_draft_status_change(): void
    {
        Notification::fake();

        $owner = User::factory()->make(['id' => 7, 'email' => 'owner@example.com']);
        $step = new WorkflowStep(['code' => 'EN_TRAITEMENT']);

        $demande = new class extends Demande
        {
            public function wasChanged($attributes = null): bool
            {
                return $attributes === 'current_step_id';
            }

            public function load($relations)
            {
                return $this;
            }
        };
        $demande->setRelation('user', $owner);
        $demande->setRelation('currentStep', $step);

        (new DemandeObserver)->updated($demande);

        Notification::assertSentTo($owner, DemandeStatusChangedNotification::class);
    }
}
