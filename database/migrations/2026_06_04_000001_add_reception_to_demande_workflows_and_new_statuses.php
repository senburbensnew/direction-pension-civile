<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demande_workflows', function (Blueprint $table) {
            $table->enum('reception_status', ['pending', 'accepted', 'refused'])
                ->default('accepted')
                ->after('commentaire');
            $table->text('reception_motif')->nullable()->after('reception_status');
            $table->timestamp('reception_at')->nullable()->after('reception_motif');
            $table->foreignId('reception_by_user_id')
                ->nullable()
                ->after('reception_at')
                ->constrained('users')
                ->nullOnDelete();
        });

        // Mark all existing workflow records as already accepted (legacy data)
        DB::table('demande_workflows')->update(['reception_status' => 'accepted']);

        // New statuses for the pending-transfer flow
        DB::table('statuses')->insertOrIgnore([
            [
                'code'        => 'TRANSFERT_EN_ATTENTE',
                'label'       => 'Transfert en attente de réception',
                'description' => 'Le dossier a été transféré vers un service qui n\'a pas encore confirmé la réception',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'code'        => 'TRANSFERT_REFUSE',
                'label'       => 'Transfert refusé',
                'description' => 'Le service destinataire a refusé la réception du dossier — il est retourné au service expéditeur',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::table('demande_workflows', function (Blueprint $table) {
            $table->dropForeign(['reception_by_user_id']);
            $table->dropColumn(['reception_status', 'reception_motif', 'reception_at', 'reception_by_user_id']);
        });

        DB::table('statuses')->whereIn('code', ['TRANSFERT_EN_ATTENTE', 'TRANSFERT_REFUSE'])->delete();
    }
};
