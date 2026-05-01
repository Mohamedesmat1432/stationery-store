<?php

namespace App\Imports;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class UsersImport implements SkipsOnError, SkipsOnFailure, ToCollection, WithChunkReading, WithHeadingRow, WithValidation
{
    use SkipsErrors, SkipsFailures;

    public function collection(Collection $rows)
    {
        // Cache all roles to avoid N+1 queries during import
        $availableRoles = Role::pluck('name')->toArray();

        DB::transaction(function () use ($rows, $availableRoles) {
            foreach ($rows as $row) {
                $user = User::create([
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'password' => Hash::make(Str::random(16)),
                    'email_verified_at' => now(),
                ]);

                if (! empty($row['roles'])) {
                    $requestedRoles = array_map('trim', explode(',', $row['roles']));

                    // Filter requested roles against available roles in system
                    $rolesToAssign = array_intersect($requestedRoles, $availableRoles);

                    if (! empty($rolesToAssign)) {
                        $user->assignRole($rolesToAssign);
                    }
                }
            }
        });
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
