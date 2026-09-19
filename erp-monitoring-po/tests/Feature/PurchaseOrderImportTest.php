<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class PurchaseOrderImportTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $role = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator']);
        $user = User::factory()->create();
        DB::table('user_roles')->insert(['user_id' => $user->id, 'role_id' => $role->id]);

        return $user;
    }

    private function seedData(): void
    {
        DB::table('suppliers')->insert(['supplier_code' => 'SUP001', 'supplier_name' => 'Supplier A', 'status' => 1, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('suppliers')->insert(['supplier_code' => 'SUP002', 'supplier_name' => 'Supplier B', 'status' => 1, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('suppliers')->insert(['supplier_code' => 'SUP003', 'supplier_name' => 'Supplier Inactive', 'status' => 0, 'created_at' => now(), 'updated_at' => now()]);

        $unitId = DB::table('units')->insertGetId(['unit_code' => 'PCS', 'unit_name' => 'Pieces', 'created_at' => now(), 'updated_at' => now()]);

        DB::table('items')->insert(['item_code' => 'ITM001', 'item_name' => 'Item Alpha', 'unit_id' => $unitId, 'active' => 1, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('items')->insert(['item_code' => 'ITM002', 'item_name' => 'Item Beta', 'unit_id' => $unitId, 'active' => 1, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('items')->insert(['item_code' => 'ITM003', 'item_name' => 'Item Inactive', 'unit_id' => $unitId, 'active' => 0, 'created_at' => now(), 'updated_at' => now()]);
    }

    private function createFlatFile(array $dataRows, string $extension): string
    {
        $columns = ['po_number', 'po_date', 'supplier_code', 'currency', 'notes', 'item_code', 'ordered_qty', 'unit_price', 'etd_date', 'remarks'];

        if ($extension === 'xlsx') {
            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();

            foreach ($columns as $index => $col) {
                $sheet->setCellValueByColumnAndRow($index + 1, 1, $col);
            }

            $rowNum = 2;
            foreach ($dataRows as $data) {
                foreach ($columns as $colIndex => $colName) {
                    $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowNum, $data[$colName] ?? '');
                }
                $rowNum++;
            }

            $path = storage_path('app/temp/test_import_'.uniqid('', true).'.xlsx');
            $writer = new Xlsx($spreadsheet);
            $writer->save($path);

            return $path;
        }

        $path = storage_path('app/temp/test_import_'.uniqid('', true).'.csv');
        $fp = fopen($path, 'w');
        fputcsv($fp, $columns);
        foreach ($dataRows as $data) {
            fputcsv($fp, array_map(fn ($v) => (string) ($v ?? ''), $data));
        }
        fclose($fp);

        return $path;
    }

    public function test_import_template_download(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get('/po/import-template')
            ->assertSuccessful()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_import_xlsx_success(): void
    {
        $this->seedData();
        $user = $this->adminUser();

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile(
            [
                [
                    'po_number' => '',
                    'po_date' => $dateStr,
                    'supplier_code' => 'SUP001',
                    'currency' => 'IDR',
                    'notes' => 'Test PO',
                    'item_code' => 'ITM001',
                    'ordered_qty' => 100,
                    'unit_price' => 50000,
                    'etd_date' => '',
                    'remarks' => 'Test',
                ],
                [
                    'po_number' => '',
                    'po_date' => $dateStr,
                    'supplier_code' => 'SUP001',
                    'currency' => 'IDR',
                    'notes' => 'Test PO',
                    'item_code' => 'ITM002',
                    'ordered_qty' => 50,
                    'unit_price' => 100000,
                    'etd_date' => '',
                    'remarks' => 'Test 2',
                ],
            ],
            'xlsx'
        );

        $file = new UploadedFile($path, 'test_import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 0, true);

        $this->actingAs($user)
            ->post('/po/import', ['file' => $file])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_orders', [
            'po_number' => 'PO-'.date('Ymd').'-0001',
            'supplier_id' => DB::table('suppliers')->where('supplier_code', 'SUP001')->value('id'),
            'status' => 'PO Issued',
        ]);

        $poId = DB::table('purchase_orders')->value('id');
        $this->assertDatabaseHas('purchase_order_items', ['purchase_order_id' => $poId, 'item_id' => DB::table('items')->where('item_code', 'ITM001')->value('id'), 'ordered_qty' => 100, 'received_qty' => 0, 'outstanding_qty' => 100]);
        $this->assertDatabaseHas('purchase_order_items', ['purchase_order_id' => $poId, 'item_id' => DB::table('items')->where('item_code', 'ITM002')->value('id'), 'ordered_qty' => 50, 'received_qty' => 0, 'outstanding_qty' => 50]);
        $this->assertDatabaseHas('po_status_histories', ['purchase_order_id' => $poId, 'to_status' => 'PO Issued']);
    }

    public function test_import_csv_success(): void
    {
        $this->seedData();
        $user = $this->adminUser();

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile(
            [
                [
                    'po_number' => '',
                    'po_date' => $dateStr,
                    'supplier_code' => 'SUP001',
                    'currency' => 'IDR',
                    'notes' => 'CSV PO',
                    'item_code' => 'ITM001',
                    'ordered_qty' => 100,
                    'unit_price' => 50000,
                    'etd_date' => '',
                    'remarks' => 'CSV Test',
                ],
                [
                    'po_number' => '',
                    'po_date' => $dateStr,
                    'supplier_code' => 'SUP001',
                    'currency' => 'IDR',
                    'notes' => 'CSV PO',
                    'item_code' => 'ITM002',
                    'ordered_qty' => 50,
                    'unit_price' => 100000,
                    'etd_date' => '',
                    'remarks' => 'CSV Test 2',
                ],
            ],
            'csv'
        );

        $file = new UploadedFile($path, 'test_import.csv', 'text/csv', 0, true);

        $this->actingAs($user)
            ->post('/po/import', ['file' => $file])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_orders', [
            'po_number' => 'PO-'.date('Ymd').'-0001',
            'supplier_id' => DB::table('suppliers')->where('supplier_code', 'SUP001')->value('id'),
            'status' => 'PO Issued',
        ]);

        $poId = DB::table('purchase_orders')->value('id');
        $this->assertDatabaseHas('purchase_order_items', ['purchase_order_id' => $poId, 'item_id' => DB::table('items')->where('item_code', 'ITM001')->value('id'), 'ordered_qty' => 100, 'received_qty' => 0, 'outstanding_qty' => 100]);
    }

    public function test_import_xlsx_multiple_po(): void
    {
        $this->seedData();
        $user = $this->adminUser();

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile(
            [
                [
                    'po_number' => 'PO-'.date('Ymd').'-0001',
                    'po_date' => $dateStr,
                    'supplier_code' => 'SUP001',
                    'currency' => 'IDR',
                    'notes' => 'PO 1',
                    'item_code' => 'ITM001',
                    'ordered_qty' => 100,
                    'unit_price' => 50000,
                    'etd_date' => '',
                    'remarks' => '',
                ],
                [
                    'po_number' => 'PO-'.date('Ymd').'-0002',
                    'po_date' => $dateStr,
                    'supplier_code' => 'SUP002',
                    'currency' => 'IDR',
                    'notes' => 'PO 2',
                    'item_code' => 'ITM002',
                    'ordered_qty' => 50,
                    'unit_price' => 100000,
                    'etd_date' => '',
                    'remarks' => '',
                ],
            ],
            'xlsx'
        );

        $file = new UploadedFile($path, 'test_multi.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 0, true);

        $this->actingAs($user)
            ->post('/po/import', ['file' => $file])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_orders', ['po_number' => 'PO-'.date('Ymd').'-0001', 'supplier_id' => DB::table('suppliers')->where('supplier_code', 'SUP001')->value('id')]);
        $this->assertDatabaseHas('purchase_orders', ['po_number' => 'PO-'.date('Ymd').'-0002', 'supplier_id' => DB::table('suppliers')->where('supplier_code', 'SUP002')->value('id')]);
    }

    public function test_import_invalid_supplier(): void
    {
        $this->seedData();
        $user = $this->adminUser();

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile(
            [
                [
                    'po_number' => 'PO-'.date('Ymd').'-0001',
                    'po_date' => $dateStr,
                    'supplier_code' => 'INVALID',
                    'currency' => 'IDR',
                    'notes' => '',
                    'item_code' => 'ITM001',
                    'ordered_qty' => 100,
                    'unit_price' => 50000,
                    'etd_date' => '',
                    'remarks' => '',
                ],
            ],
            'xlsx'
        );

        $file = new UploadedFile($path, 'test_invalid_supplier.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 0, true);

        $this->actingAs($user)
            ->post('/po/import', ['file' => $file])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('purchase_orders', ['po_number' => 'PO-'.date('Ymd').'-0001']);
    }

    public function test_import_inactive_supplier(): void
    {
        $this->seedData();
        $user = $this->adminUser();

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile(
            [
                [
                    'po_number' => 'PO-'.date('Ymd').'-0001',
                    'po_date' => $dateStr,
                    'supplier_code' => 'SUP003',
                    'currency' => 'IDR',
                    'notes' => '',
                    'item_code' => 'ITM001',
                    'ordered_qty' => 100,
                    'unit_price' => 50000,
                    'etd_date' => '',
                    'remarks' => '',
                ],
            ],
            'xlsx'
        );

        $file = new UploadedFile($path, 'test_inactive_supplier.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 0, true);

        $this->actingAs($user)
            ->post('/po/import', ['file' => $file])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('purchase_orders', ['po_number' => 'PO-'.date('Ymd').'-0001']);
    }

    public function test_import_invalid_item(): void
    {
        $this->seedData();
        $user = $this->adminUser();

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile(
            [
                [
                    'po_number' => 'PO-'.date('Ymd').'-0001',
                    'po_date' => $dateStr,
                    'supplier_code' => 'SUP001',
                    'currency' => 'IDR',
                    'notes' => '',
                    'item_code' => 'INVALID',
                    'ordered_qty' => 100,
                    'unit_price' => 50000,
                    'etd_date' => '',
                    'remarks' => '',
                ],
            ],
            'xlsx'
        );

        $file = new UploadedFile($path, 'test_invalid_item.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 0, true);

        $this->actingAs($user)
            ->post('/po/import', ['file' => $file])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('purchase_orders', ['po_number' => 'PO-'.date('Ymd').'-0001']);
    }

    public function test_import_inactive_item(): void
    {
        $this->seedData();
        $user = $this->adminUser();

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile(
            [
                [
                    'po_number' => 'PO-'.date('Ymd').'-0001',
                    'po_date' => $dateStr,
                    'supplier_code' => 'SUP001',
                    'currency' => 'IDR',
                    'notes' => '',
                    'item_code' => 'ITM003',
                    'ordered_qty' => 100,
                    'unit_price' => 50000,
                    'etd_date' => '',
                    'remarks' => '',
                ],
            ],
            'xlsx'
        );

        $file = new UploadedFile($path, 'test_inactive_item.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 0, true);

        $this->actingAs($user)
            ->post('/po/import', ['file' => $file])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('purchase_orders', ['po_number' => 'PO-'.date('Ymd').'-0001']);
    }

    public function test_import_qty_zero(): void
    {
        $this->seedData();
        $user = $this->adminUser();

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile(
            [
                [
                    'po_number' => 'PO-'.date('Ymd').'-0001',
                    'po_date' => $dateStr,
                    'supplier_code' => 'SUP001',
                    'currency' => 'IDR',
                    'notes' => '',
                    'item_code' => 'ITM001',
                    'ordered_qty' => 0,
                    'unit_price' => 50000,
                    'etd_date' => '',
                    'remarks' => '',
                ],
            ],
            'xlsx'
        );

        $file = new UploadedFile($path, 'test_qty_zero.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 0, true);

        $this->actingAs($user)
            ->post('/po/import', ['file' => $file])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('purchase_orders', ['po_number' => 'PO-'.date('Ymd').'-0001']);
    }

    public function test_import_negative_qty(): void
    {
        $this->seedData();
        $user = $this->adminUser();

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile(
            [
                [
                    'po_number' => 'PO-'.date('Ymd').'-0001',
                    'po_date' => $dateStr,
                    'supplier_code' => 'SUP001',
                    'currency' => 'IDR',
                    'notes' => '',
                    'item_code' => 'ITM001',
                    'ordered_qty' => -5,
                    'unit_price' => 50000,
                    'etd_date' => '',
                    'remarks' => '',
                ],
            ],
            'xlsx'
        );

        $file = new UploadedFile($path, 'test_neg_qty.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 0, true);

        $this->actingAs($user)
            ->post('/po/import', ['file' => $file])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('purchase_orders', ['po_number' => 'PO-'.date('Ymd').'-0001']);
    }

    public function test_import_same_po_number_with_different_items_allowed(): void
    {
        $this->seedData();
        $user = $this->adminUser();

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile(
            [
                [
                    'po_number' => 'PO-'.date('Ymd').'-0001',
                    'po_date' => $dateStr,
                    'supplier_code' => 'SUP001',
                    'currency' => 'IDR',
                    'notes' => '',
                    'item_code' => 'ITM001',
                    'ordered_qty' => 100,
                    'unit_price' => 50000,
                    'etd_date' => '',
                    'remarks' => '',
                ],
                [
                    'po_number' => 'PO-'.date('Ymd').'-0001',
                    'po_date' => $dateStr,
                    'supplier_code' => 'SUP002',
                    'currency' => 'IDR',
                    'notes' => '',
                    'item_code' => 'ITM002',
                    'ordered_qty' => 50,
                    'unit_price' => 100000,
                    'etd_date' => '',
                    'remarks' => '',
                ],
            ],
            'xlsx'
        );

        $file = new UploadedFile($path, 'test_same_po_diff_items.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 0, true);

        $this->actingAs($user)
            ->post('/po/import', ['file' => $file])
            ->assertSessionHas('success');

        $this->assertDatabaseCount('purchase_orders', 1);
        $this->assertDatabaseCount('purchase_order_items', 2);
    }

    public function test_import_same_item_in_same_po_with_different_qty_allowed(): void
    {
        $this->seedData();
        $user = $this->adminUser();

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile(
            [
                [
                    'po_number' => 'PO-'.date('Ymd').'-0001',
                    'po_date' => $dateStr,
                    'supplier_code' => 'SUP001',
                    'currency' => 'IDR',
                    'notes' => '',
                    'item_code' => 'ITM001',
                    'ordered_qty' => 100,
                    'unit_price' => 50000,
                    'etd_date' => '',
                    'remarks' => '',
                ],
                [
                    'po_number' => 'PO-'.date('Ymd').'-0001',
                    'po_date' => $dateStr,
                    'supplier_code' => 'SUP001',
                    'currency' => 'IDR',
                    'notes' => '',
                    'item_code' => 'ITM001',
                    'ordered_qty' => 50,
                    'unit_price' => 100000,
                    'etd_date' => '',
                    'remarks' => '',
                ],
            ],
            'xlsx'
        );

        $file = new UploadedFile($path, 'test_same_item_diff_qty.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 0, true);

        $this->actingAs($user)
            ->post('/po/import', ['file' => $file])
            ->assertSessionHas('success');

        $this->assertDatabaseCount('purchase_orders', 1);
        $this->assertDatabaseCount('purchase_order_items', 2);
    }

    public function test_import_duplicate_item_same_qty_in_same_po_blocked(): void
    {
        $this->seedData();
        $user = $this->adminUser();

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile(
            [
                [
                    'po_number' => 'PO-'.date('Ymd').'-0001',
                    'po_date' => $dateStr,
                    'supplier_code' => 'SUP001',
                    'currency' => 'IDR',
                    'notes' => '',
                    'item_code' => 'ITM001',
                    'ordered_qty' => 100,
                    'unit_price' => 50000,
                    'etd_date' => '',
                    'remarks' => '',
                ],
                [
                    'po_number' => 'PO-'.date('Ymd').'-0001',
                    'po_date' => $dateStr,
                    'supplier_code' => 'SUP001',
                    'currency' => 'IDR',
                    'notes' => '',
                    'item_code' => 'ITM001',
                    'ordered_qty' => 100,
                    'unit_price' => 100000,
                    'etd_date' => '',
                    'remarks' => '',
                ],
            ],
            'xlsx'
        );

        $file = new UploadedFile($path, 'test_dup_item_same_qty.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 0, true);

        $this->actingAs($user)
            ->post('/po/import', ['file' => $file])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('purchase_orders', 0);
    }

    public function test_import_rollback_on_mixed_valid_invalid(): void
    {
        $this->seedData();
        $user = $this->adminUser();

        $dateStr = now()->toDateString();
        $path = $this->createFlatFile(
            [
                [
                    'po_number' => 'PO-'.date('Ymd').'-0001',
                    'po_date' => $dateStr,
                    'supplier_code' => 'SUP001',
                    'currency' => 'IDR',
                    'notes' => '',
                    'item_code' => 'ITM001',
                    'ordered_qty' => 100,
                    'unit_price' => 50000,
                    'etd_date' => '',
                    'remarks' => '',
                ],
                [
                    'po_number' => 'PO-'.date('Ymd').'-0002',
                    'po_date' => $dateStr,
                    'supplier_code' => 'SUP002',
                    'currency' => 'IDR',
                    'notes' => '',
                    'item_code' => 'INVALID',
                    'ordered_qty' => 50,
                    'unit_price' => 100000,
                    'etd_date' => '',
                    'remarks' => '',
                ],
            ],
            'xlsx'
        );

        $file = new UploadedFile($path, 'test_rollback.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 0, true);

        $this->actingAs($user)
            ->post('/po/import', ['file' => $file])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('purchase_orders', 0);
        $this->assertDatabaseCount('purchase_order_items', 0);
    }

    public function test_import_empty_file(): void
    {
        $this->seedData();
        $user = $this->adminUser();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $columns = ['po_number', 'po_date', 'supplier_code', 'currency', 'notes', 'item_code', 'ordered_qty', 'unit_price', 'etd_date', 'remarks'];
        foreach ($columns as $index => $col) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $col);
        }

        $path = storage_path('app/temp/test_empty_'.uniqid('', true).'.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        $file = new UploadedFile($path, 'test_empty.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 0, true);

        $this->actingAs($user)
            ->post('/po/import', ['file' => $file])
            ->assertSessionHas('error');
    }

    public function test_import_invalid_extension(): void
    {
        $this->seedData();
        $user = $this->adminUser();

        $path = storage_path('app/temp/test.txt');
        file_put_contents($path, 'test');

        $file = new UploadedFile($path, 'test.txt', 'text/plain', 0, true);

        $this->actingAs($user)
            ->post('/po/import', ['file' => $file])
            ->assertSessionHas('error');
    }
}
