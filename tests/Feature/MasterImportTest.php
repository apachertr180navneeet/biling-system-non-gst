<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SparePart;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MasterImportTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);
        $role = Role::where('name', 'admin')->first();

        // Create the admin user
        $this->admin = User::create([
            'first_name' => 'Project',
            'last_name' => 'Admin',
            'full_name' => 'Project Admin',
            'slug' => 'project-admin',
            'email' => 'projectadmin@mailinator.com',
            'password' => bcrypt('123456'),
            'phone' => '8000000000',
            'role' => 'admin',
            'role_id' => $role->id,
            'address' => '115 Pitt Street, Sydney NSW, Australia',
            'area' => '115 Pitt St',
            'city' => 'Sydney',
            'state' => 'NSW',
            'country' => 'Australia',
            'country_code' => '61',
            'zipcode' => '2000',
            'latitude' => '-33.8664701',
            'longitude' => '151.2081952',
            'status' => 'active',
        ]);
    }

    public function test_can_download_spare_parts_import_template(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.spare-parts.import-template'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition');
        $this->assertStringContainsString('spare_part_template.xls', $response->headers->get('Content-Disposition'));
    }

    public function test_can_import_valid_spare_parts(): void
    {
        $csvContent = "part_no,name,selling_price,mrp,unit\n"
            . "P-101,Brake Pads,450.00,500.00,Piece\n"
            . "P-102,Air Filter,250.00,300.00,Piece\n";

        $file = UploadedFile::fake()->createWithContent('parts.csv', $csvContent);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.spare-parts.import'), [
                'csv_file' => $file,
            ]);

        $response->assertRedirect(route('admin.spare-parts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('spare_parts', [
            'part_no' => 'P-101',
            'name' => 'Brake Pads',
            'selling_price' => 450.00,
            'mrp' => 500.00,
            'unit' => 'Piece',
        ]);
    }

    public function test_can_download_suppliers_import_template(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.suppliers.import-template'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition');
        $this->assertStringContainsString('supplier_template.xls', $response->headers->get('Content-Disposition'));
    }

    public function test_can_import_valid_suppliers(): void
    {
        $csvContent = "name,gstin,address,contact_person,phone,email\n"
            . "Bosch,,Germany,,9876543210,bosch@example.com\n"
            . "Tata Motors,,,Tata Person,8888888888,\n";

        $file = UploadedFile::fake()->createWithContent('suppliers.csv', $csvContent);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.suppliers.import'), [
                'csv_file' => $file,
            ]);

        $response->assertRedirect(route('admin.suppliers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('suppliers', [
            'name' => 'Bosch',
            'phone' => '9876543210',
        ]);
    }

    public function test_can_download_customers_import_template(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.customers.import-template'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition');
        $this->assertStringContainsString('customer_template.xls', $response->headers->get('Content-Disposition'));
    }

    public function test_can_import_valid_customers(): void
    {
        $csvContent = "type,name,company_name,phone,email,address,state,gstin,pan_no,aadhaar_no\n"
            . "individual,Jane Smith,,9876543210,jane@example.com,,State,,,\n"
            . "corporate,ACME Inc,,8888888888,acme@example.com,,,,,\n"
            . "individual,12 ARTY BDE,,,,,,,,,\n";

        $file = UploadedFile::fake()->createWithContent('customers.csv', $csvContent);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.customers.import'), [
                'csv_file' => $file,
            ]);

        $response->assertRedirect(route('admin.customers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('customers', [
            'name' => 'Jane Smith',
            'phone' => '9876543210',
            'type' => 'individual',
        ]);

        $this->assertDatabaseHas('customers', [
            'name' => '12 ARTY BDE',
            'phone' => null,
            'type' => 'individual',
        ]);
    }
}
