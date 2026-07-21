<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF');

            DB::statement('CREATE TABLE users_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                name VARCHAR NOT NULL,
                email VARCHAR NOT NULL UNIQUE,
                password VARCHAR NOT NULL,
                role VARCHAR CHECK ("role" IN (\'admin\', \'authorized_user\')) NOT NULL DEFAULT \'authorized_user\',
                facility_id INTEGER,
                email_verified_at DATETIME,
                remember_token VARCHAR,
                created_at DATETIME,
                updated_at DATETIME
            )');

            DB::statement("INSERT INTO users_new (id, name, email, password, role, facility_id, email_verified_at, remember_token, created_at, updated_at)
                SELECT id, name, email, password,
                       CASE WHEN role = 'admin' THEN 'admin' ELSE 'authorized_user' END,
                       facility_id, email_verified_at, remember_token, created_at, updated_at
                  FROM users");

            DB::statement('DROP TABLE users');
            DB::statement('ALTER TABLE users_new RENAME TO users');

            DB::statement('PRAGMA foreign_keys=ON');
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['admin', 'authorized_user'])->default('authorized_user')->change();
            });

            DB::table('users')->where('role', '!=', 'admin')->update(['role' => 'authorized_user']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rolling back this migration would require reconstructing previous role values,
        // which is not safe when roles were normalized to a generic authorized_user.
    }
};
