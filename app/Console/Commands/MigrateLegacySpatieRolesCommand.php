<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class MigrateLegacySpatieRolesCommand extends Command
{
    protected $signature = 'dpc:roles-migrate-legacy {--dry-run : Afficher les changements sans les appliquer}';

    protected $description = 'Réassigne les rôles Spatie legacy vers les noms canoniques (config/dpc_roles.php)';

    public function handle(): int
    {
        $aliases = config('dpc_roles.legacy_aliases', []);
        $dryRun = (bool) $this->option('dry-run');

        foreach ($aliases as $legacy => $canonical) {
            $legacyRole = Role::query()->where('name', $legacy)->first();

            if (! $legacyRole) {
                continue;
            }

            if ($canonical === null) {
                $count = DB::table('model_has_roles')->where('role_id', $legacyRole->id)->count();
                $this->line("Legacy « {$legacy} » : {$count} assignation(s) — aucun remplaçant (conservé).");

                continue;
            }

            $targetRole = Role::query()->firstOrCreate([
                'name' => $canonical,
                'guard_name' => 'web',
            ]);

            $rows = DB::table('model_has_roles')
                ->where('role_id', $legacyRole->id)
                ->get();

            foreach ($rows as $row) {
                $exists = DB::table('model_has_roles')
                    ->where('role_id', $targetRole->id)
                    ->where('model_type', $row->model_type)
                    ->where('model_id', $row->model_id)
                    ->exists();

                if ($dryRun) {
                    $this->line("User #{$row->model_id} : {$legacy} → {$canonical}".($exists ? ' (doublon ignoré)' : ''));

                    continue;
                }

                if (! $exists) {
                    DB::table('model_has_roles')->insert([
                        'role_id' => $targetRole->id,
                        'model_type' => $row->model_type,
                        'model_id' => $row->model_id,
                    ]);
                }

                DB::table('model_has_roles')
                    ->where('role_id', $legacyRole->id)
                    ->where('model_type', $row->model_type)
                    ->where('model_id', $row->model_id)
                    ->delete();
            }

            if (! $dryRun && DB::table('model_has_roles')->where('role_id', $legacyRole->id)->doesntExist()) {
                $legacyRole->delete();
                $this->info("Rôle legacy « {$legacy} » supprimé après migration vers « {$canonical} ».");
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->info($dryRun ? 'Simulation terminée.' : 'Migration des rôles terminée.');

        return self::SUCCESS;
    }
}
