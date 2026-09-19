<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'id' => 2,
                'name' => 'Admin',
                'nik' => null,
                'email' => 'admin@mail.com',
                'email_verified_at' => null,
                'password' => '$2y$12$wo9kxm5kcUboUmIASwTWMOrsVn9mmp/sdqrOHP5NiNkN.bbbGcSh.',
                'is_active' => 1,
                'remember_token' => null,
                'created_at' => '2026-03-08 11:26:00',
                'updated_at' => '2026-03-08 11:26:00',
            ],
            [
                'id' => 3,
                'name' => 'Purchasing',
                'nik' => null,
                'email' => 'purchasing@mail.com',
                'email_verified_at' => null,
                'password' => '$2y$12$trE4lgLd.cMLBwNV/i0nWuUlPVMrnpA2TlFzNuzqqSmwASUSVSDey',
                'is_active' => 1,
                'remember_token' => null,
                'created_at' => '2026-03-08 11:26:00',
                'updated_at' => '2026-03-08 11:26:00',
            ],
            [
                'id' => 4,
                'name' => 'Warehouse',
                'nik' => null,
                'email' => 'warehouse@mail.com',
                'email_verified_at' => null,
                'password' => '$2y$12$Hqts3kvXoTwonUHhl6Ilfus/n1IPDpfcZi6CLqeZTUuQiDdmbVH7a',
                'is_active' => 1,
                'remember_token' => null,
                'created_at' => '2026-03-08 11:26:00',
                'updated_at' => '2026-03-08 11:26:00',
            ],
            [
                'id' => 6,
                'name' => 'Administrator ERP',
                'nik' => '1',
                'email' => 'admin@erp.local',
                'email_verified_at' => null,
                'password' => '$2y$12$aRJ8BoaDCIkXL2R8jnl4Mu5nbsIHn4YRV4pGkIW7vmjMNcuUEelFm',
                'is_active' => 1,
                'remember_token' => null,
                'created_at' => '2026-09-18 19:08:52',
                'updated_at' => '2026-09-18 23:33:25',
            ],
            [
                'id' => 7,
                'name' => 'Staff ERP',
                'nik' => '10000002',
                'email' => 'staff@erp.local',
                'email_verified_at' => null,
                'password' => '$2y$12$U2btFaV6t5CbS0ExngmVweZSYETex3a0PKgm8CoHTrrjBaPdSXcQu',
                'is_active' => 1,
                'remember_token' => null,
                'created_at' => '2026-09-18 19:08:53',
                'updated_at' => '2026-09-18 23:33:26',
            ],
            [
                'id' => 8,
                'name' => 'Supervisor ERP',
                'nik' => '10000003',
                'email' => 'supervisor@erp.local',
                'email_verified_at' => null,
                'password' => '$2y$12$ovBKE8681vFo1SlRAsrgpOgE33kjRGeFKJ8AaKL3bpw9i.fmDH0MO',
                'is_active' => 1,
                'remember_token' => null,
                'created_at' => '2026-09-18 19:08:53',
                'updated_at' => '2026-09-18 23:33:26',
            ],
        ]);
    }
}
