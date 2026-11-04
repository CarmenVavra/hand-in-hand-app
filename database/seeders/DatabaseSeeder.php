<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Legt nur den Admin-Zugang an (ADMIN_EMAIL / ADMIN_PASSWORD aus der .env).
     * Fehlen diese Werte, fragt der Befehl interaktiv nach.
     */
    public function run(): void
    {
        $this->command->call('handinhand:admin');
    }
}
