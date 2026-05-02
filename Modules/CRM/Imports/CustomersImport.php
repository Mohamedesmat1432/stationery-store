<?php

namespace Modules\CRM\Imports;

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
        // Map headings to support both English and Arabic slugified keys
        $mappedRows = $rows->map(function ($row) {
            return [
                'email' => $row['email'] ?? $row['albrid_alktroni'] ?? $row['البريد_الإلكتروني'] ?? null,
                'group' => $row['group'] ?? $row['almjmoaa'] ?? $row['المجموعة'] ?? null,
                'phone' => $row['phone'] ?? $row['althatf'] ?? $row['الهاتف'] ?? null,
                'company_name' => $row['company_name'] ?? $row['asm_alshrka'] ?? $row['اسم_الشركة'] ?? null,
                'tax_number' => $row['tax_number'] ?? $row['alrqm_aldryby'] ?? $row['الرقم_الضريبي'] ?? null,
                'total_spent' => $row['total_spent'] ?? $row['ajmaly_almnfq'] ?? $row['إجمالي_المنفق'] ?? $row['spent'] ?? $row['almbly_almnfq'] ?? 0,
                'orders_count' => $row['orders_count'] ?? $row['aad_altlbat'] ?? $row['عدد_الطلبات'] ?? 0,
            ];
        });

        $emails = $mappedRows->pluck('email')->filter()->toArray();
        $users = User::whereIn('email', $emails)->get()->keyBy('email');

        $groups = CustomerGroup::pluck('id', 'slug')->toArray();
        $groupNames = CustomerGroup::pluck('id', 'name')->toArray();

        DB::transaction(function () use ($mappedRows, $users, $groups, $groupNames) {
            foreach ($mappedRows as $row) {
                if (empty($row['email'])) {
                    continue;
                }

                $user = $users->get($row['email']);

                if (! $user) {
                    continue;
                }

                $groupId = null;
                if (! empty($row['group'])) {
                    $groupId = $groups[$row['group']] ?? $groupNames[$row['group']] ?? null;
                }

                Customer::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'customer_group_id' => $groupId,
                        'phone' => $row['phone'],
                        'company_name' => $row['company_name'],
                        'tax_number' => $row['tax_number'],
                        'total_spent' => (float) $row['total_spent'],
                        'orders_count' => (int) $row['orders_count'],
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

    public function customValidationAttributes(): array
    {
        return [
            'email' => __('Email'),
            'group' => __('Group'),
            'total_spent' => __('Total Spent'),
            'orders_count' => __('Orders Count'),
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
