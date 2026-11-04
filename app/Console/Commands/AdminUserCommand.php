<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class AdminUserCommand extends Command
{
    protected $signature = 'handinhand:admin
        {--name= : Anzeigename (Standard: HIH_AUNT_NAME)}
        {--email= : E-Mail-Adresse (Standard: ADMIN_EMAIL)}
        {--password= : Passwort (Standard: ADMIN_PASSWORD; sonst Abfrage)}';

    protected $description = 'Legt den Admin-Zugang (Tante) an oder setzt das Passwort neu';

    public function handle(): int
    {
        $email = $this->option('email') ?: config('handinhand.admin.email') ?: text('E-Mail-Adresse', required: true);
        $name = $this->option('name') ?: config('handinhand.admin.name') ?: config('handinhand.aunt_name');
        $secret = $this->option('password') ?: config('handinhand.admin.password') ?: password('Passwort (mind. 10 Zeichen)', required: true);

        $validator = Validator::make(
            ['email' => $email, 'password' => $secret],
            ['email' => ['required', 'email'], 'password' => ['required', 'string', 'min:10']],
        );

        if ($validator->fails()) {
            $this->components->error($validator->errors()->first());

            return self::FAILURE;
        }

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => $secret],
        );

        $this->components->info("Admin „{$user->name}“ ({$user->email}) ist bereit.");

        return self::SUCCESS;
    }
}
