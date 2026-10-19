<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddUserTypeAndNifToUsersTable extends Migration
{
    /**
     * Legacy migration: user_type and nif are added elsewhere in the current schema.
     *
     * @return void
     */
    public function up()
    {
        // No-op for fresh installs (nif is added in 2025_03_30; user_type was removed).
    }

    /**
     * @return void
     */
    public function down()
    {
        // No-op.
    }
}
