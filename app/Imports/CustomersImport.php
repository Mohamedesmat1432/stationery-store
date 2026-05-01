<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CustomersImport implements SkipsOnError, SkipsOnFailure, ToCollection, WithChunkReading, WithHeadingRow, WithValidation
{
    use SkipsErrors, SkipsFailures;

    public function collection(Collection $rows)
    {
        // Bulk fetch users to avoid N+1 queries
        $emails = $rows->pluck('email')->filter()->toArray();
        $users = User::whereIn('email', $emails)->get()->keyBy('email');

        // Cache customer groups for performance
        $groups = CustomerGroup::pluck('id', 'slug')->toArray();
        $groupNames = CustomerGroup::pluck('id', 'name')->toArray();

        DB::transaction(function () use ($rows, $users, $groups, $groupNames) {
            foreach ($rows as $row) {
                // Find user by email from bulk-loaded collection
                $user = $users->get($row['email']);

                if (! $user) {
                    continue;
                }

                // Find or map customer group
                $groupId = null;
                if (! empty($row['group'])) {
                    $groupId = $groups[$row['group']] ?? $groupNames[$row['group']] ?? null;
                }

                Customer::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'customer_group_id' => $groupId,
                        'phone' => $row['phone'] ?? null,
                        'company_name' => $row['company_name'] ?? null,
                        'tax_number' => $row['tax_number'] ?? null,
                        'total_spent' => (float) ($row['total_spent'] ?? 0),
                        'orders_count' => (int) ($row['orders_count'] ?? 0),
                    ]
                );
            }
        });
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'group' => ['nullable', 'string'],
            'total_spent' => ['nullable', 'numeric'],
            'orders_count' => ['nullable', 'integer'],
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
