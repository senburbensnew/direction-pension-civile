<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var list<string> */
    private array $tables = [
        'actualite_images',
        'actualites',
        'affiliation_pensions',
        'agent_delegations',
        'annees_fiscales',
        'assignments',
        'beneficiaire_relation_demandes',
        'beneficiaire_relation_events',
        'beneficiaires',
        'carousels',
        'civil_statuses',
        'contact_subjects',
        'contacts',
        'demande_circuit_snapshots',
        'demande_creation_compte_histories',
        'demande_creation_comptes',
        'demande_histories',
        'demande_interactions',
        'demande_messages',
        'demandes',
        'direction_departementales',
        'faq_items',
        'flux_transition_logs',
        'formalites',
        'genders',
        'glossaire_terms',
        'holidays',
        'institution_images',
        'institutions',
        'media',
        'mediatheque_items',
        'newsletter_campaigns',
        'newsletter_deliveries',
        'newsletter_suppressions',
        'newsletters',
        'notifications',
        'officials',
        'partenaires',
        'pension_categories',
        'pension_mandats',
        'pension_types',
        'pensionnaires_reference',
        'permissions',
        'publication_types',
        'publications',
        'rdv_agent_availabilities',
        'reports',
        'required_circuit_services',
        'roles',
        'service_sla',
        'services',
        'site_visits',
        'step_required_documents',
        'types_demandes',
        'user_beneficiaire_links',
        'user_categories',
        'user_category_assignments',
        'user_login_events',
        'user_session_end_events',
        'users',
        'workflow_step_transitions',
        'workflow_steps',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }

            Schema::table($table, function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }

            Schema::table($table, function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
