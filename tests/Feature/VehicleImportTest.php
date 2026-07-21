<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VehicleMaster;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class VehicleImportTest extends TestCase
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

    public function test_can_download_import_template(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.vehicle-masters.import-template'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition');
        $this->assertStringContainsString('vehicle_master_template.xls', $response->headers->get('Content-Disposition'));
    }

    public function test_can_import_valid_csv(): void
    {
        $csvContent = "variant_name,color_name,fuel_type,transmission,ex_showroom_price\n"
            . "Safari,Orcus White,Diesel,Manual,1600000.00\n"
            . "Nexon,Flame Red,Petrol,Automatic,1200000.00\n";

        $file = UploadedFile::fake()->createWithContent('vehicles.csv', $csvContent);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.vehicle-masters.import'), [
                'csv_file' => $file,
            ]);

        $response->assertRedirect(route('admin.vehicle-masters.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('vehicle_masters', [
            'variant_name' => 'Safari',
            'color_name' => 'Orcus White',
            'fuel_type' => 'Diesel',
            'transmission' => 'Manual',
            'ex_showroom_price' => 1600000.00,
        ]);

        $this->assertDatabaseHas('vehicle_masters', [
            'variant_name' => 'Nexon',
            'color_name' => 'Flame Red',
            'fuel_type' => 'Petrol',
            'transmission' => 'Automatic',
            'ex_showroom_price' => 1200000.00,
        ]);
    }

    public function test_import_skips_duplicates_in_database(): void
    {
        // Insert existing record
        VehicleMaster::create([
            'variant_name' => 'Safari',
            'color_name' => 'Orcus White',
            'fuel_type' => 'Diesel',
            'transmission' => 'Manual',
            'ex_showroom_price' => 1600000.00,
            'is_active' => true,
        ]);

        // CSV content containing duplicate and one new entry
        $csvContent = "variant_name,color_name,fuel_type,transmission,ex_showroom_price\n"
            . "safari,orcus white,Diesel,Manual,1600000.00\n" // lowercase duplicate
            . "Nexon,Flame Red,Petrol,Automatic,1200000.00\n";

        $file = UploadedFile::fake()->createWithContent('vehicles.csv', $csvContent);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.vehicle-masters.import'), [
                'csv_file' => $file,
            ]);

        $response->assertRedirect(route('admin.vehicle-masters.index'));
        $response->assertSessionHas('import_errors');

        $errors = session('import_errors');
        $this->assertCount(1, $errors);
        $this->assertStringContainsString("Duplicate combination 'safari' and 'orcus white' already exists in the database", $errors[0]);

        // NEXON should still be imported
        $this->assertDatabaseHas('vehicle_masters', [
            'variant_name' => 'Nexon',
            'color_name' => 'Flame Red',
        ]);

        // Only 2 total records (1 original + 1 imported Nexon, Safari wasn't duplicated)
        $this->assertEquals(2, VehicleMaster::count());
    }

    public function test_import_skips_duplicates_within_csv_file(): void
    {
        // CSV containing two of the same rows
        $csvContent = "variant_name,color_name,fuel_type,transmission,ex_showroom_price\n"
            . "Altroz,Blue,Petrol,Manual,800000.00\n"
            . "altroz,blue,Petrol,Manual,800000.00\n"; // duplicate in file

        $file = UploadedFile::fake()->createWithContent('vehicles.csv', $csvContent);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.vehicle-masters.import'), [
                'csv_file' => $file,
            ]);

        $response->assertRedirect(route('admin.vehicle-masters.index'));
        $response->assertSessionHas('import_errors');

        $errors = session('import_errors');
        $this->assertCount(1, $errors);
        $this->assertStringContainsString("Duplicate combination 'altroz' and 'blue' in the CSV file", $errors[0]);

        $this->assertEquals(1, VehicleMaster::count());
    }
}
