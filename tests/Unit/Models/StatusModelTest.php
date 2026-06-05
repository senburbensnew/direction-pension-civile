<?php

namespace Tests\Unit\Models;

use App\Models\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsRequiredData;

class StatusModelTest extends TestCase
{
    use RefreshDatabase, SeedsRequiredData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStatuses();
    }

    // ─── Static helpers ──────────────────────────────────────────────────────

    /** @test */
    public function getStatusPending_returns_en_attente_status(): void
    {
        $status = Status::getStatusPending();
        $this->assertEquals('EN_ATTENTE', $status->code);
    }

    /** @test */
    public function getStatusApproved_returns_approuvee_status(): void
    {
        $status = Status::getStatusApproved();
        $this->assertEquals('APPROUVEE', $status->code);
    }

    /** @test */
    public function getStatusInProgress_returns_en_cours_status(): void
    {
        $status = Status::getStatusInProgress();
        $this->assertEquals('EN_COURS', $status->code);
    }

    /** @test */
    public function getStatusRejected_returns_rejetee_status(): void
    {
        $status = Status::getStatusRejected();
        $this->assertEquals('REJETEE', $status->code);
    }

    /** @test */
    public function getStatusCompleted_returns_finalisee_status(): void
    {
        $status = Status::getStatusCompleted();
        $this->assertEquals('FINALISEE', $status->code);
    }

    /** @test */
    public function getStatusCanceled_returns_annulee_status(): void
    {
        $status = Status::getStatusCanceled();
        $this->assertEquals('ANNULEE', $status->code);
    }

    /** @test */
    public function getStatusComplementRequis_returns_complement_requis_status(): void
    {
        $status = Status::getStatusComplementRequis();
        $this->assertEquals('COMPLEMENT_REQUIS', $status->code);
    }

    // ─── getStatusStyle ──────────────────────────────────────────────────────

    /** @test */
    public function getStatusStyle_returns_correct_css_classes(): void
    {
        $this->assertStringContainsString('yellow', Status::getStatusStyle(Status::STATUS_PENDING));
        $this->assertStringContainsString('blue', Status::getStatusStyle(Status::STATUS_APPROVED));
        $this->assertStringContainsString('purple', Status::getStatusStyle(Status::STATUS_IN_PROGRESS));
        $this->assertStringContainsString('red', Status::getStatusStyle(Status::STATUS_REJECTED));
        $this->assertStringContainsString('green', Status::getStatusStyle(Status::STATUS_COMPLETED));
        $this->assertStringContainsString('orange', Status::getStatusStyle(Status::STATUS_COMPLEMENT_REQUIS));
        $this->assertStringContainsString('sky', Status::getStatusStyle(Status::STATUS_TRANSFERT_EN_ATTENTE));
    }

    /** @test */
    public function getStatusStyle_returns_gray_for_unknown_code(): void
    {
        $style = Status::getStatusStyle('UNKNOWN_STATUS');
        $this->assertStringContainsString('gray', $style);
    }

    // ─── Constants ───────────────────────────────────────────────────────────

    /** @test */
    public function status_constants_have_correct_values(): void
    {
        $this->assertEquals('EN_ATTENTE',           Status::STATUS_PENDING);
        $this->assertEquals('APPROUVEE',            Status::STATUS_APPROVED);
        $this->assertEquals('EN_COURS',             Status::STATUS_IN_PROGRESS);
        $this->assertEquals('REJETEE',              Status::STATUS_REJECTED);
        $this->assertEquals('FINALISEE',            Status::STATUS_COMPLETED);
        $this->assertEquals('ANNULEE',              Status::STATUS_CANCELED);
        $this->assertEquals('COMPLEMENT_REQUIS',    Status::STATUS_COMPLEMENT_REQUIS);
        $this->assertEquals('TRANSFERT_EN_ATTENTE', Status::STATUS_TRANSFERT_EN_ATTENTE);
        $this->assertEquals('TRANSFERT_REFUSE',     Status::STATUS_TRANSFERT_REFUSE);
    }
}
