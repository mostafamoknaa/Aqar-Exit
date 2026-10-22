<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Deal;
use App\Models\Developer;
use App\Models\Evaluation;
use App\Models\Interest;
use App\Models\Listing;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Staff — login at /admin/login
        $admin = User::factory()->create(['name' => 'Aqar Admin', 'email' => 'admin@aqarexit.test', 'phone' => '01000000001', 'role' => UserRole::Admin]);
        $manager = User::factory()->create(['name' => 'Case Manager', 'email' => 'manager@aqarexit.test', 'phone' => '01000000002', 'role' => UserRole::Manager]);

        // Mobile users (password = "password")
        $seller = User::factory()->create(['name' => 'Ahmed Seller', 'email' => 'seller@aqarexit.test', 'phone' => '01018595959', 'city' => 'New Cairo']);
        $buyer = User::factory()->create(['name' => 'Mona Buyer', 'email' => 'buyer@aqarexit.test', 'phone' => '01018595960', 'city' => '6th of October']);
        User::factory(6)->create();

        $catalog = [
            ['Emaar Misr', 'إعمار مصر', [['Marassi', 'المراسي', 'North Coast', 'Marina'], ['Mivida', 'ميفيدا', 'New Cairo', '5th Settlement']]],
            ['Palm Hills', 'بالم هيلز', [['Palm Hills New Cairo', 'بالم هيلز القاهرة الجديدة', 'New Cairo', '5th Settlement'], ['The Crown', 'ذا كراون', '6th of October', 'Palm Hills October']]],
            ['Ora Developers', 'أورا', [['ZED East', 'زيد إيست', 'New Cairo', '5th Settlement'], ['Silversands', 'سيلفر ساندز', 'North Coast', 'Sidi Henish']]],
        ];

        $projects = collect();
        foreach ($catalog as [$name, $nameAr, $items]) {
            $developer = Developer::create(['name' => $name, 'name_ar' => $nameAr, 'slug' => Str::slug($name), 'description' => "{$name} projects on Aqar Exit.", 'assignment_notes' => 'Assignment at developer office, both parties present, ID + contract + payment receipts.']);
            foreach ($items as [$pName, $pAr, $city, $district]) {
                $projects->push(Project::create(['developer_id' => $developer->id, 'name' => $pName, 'name_ar' => $pAr, 'slug' => Str::slug($pName), 'city' => $city, 'district' => $district]));
            }
        }

        foreach ($projects as $i => $project) {
            Listing::factory()->create(['seller_id' => $seller->id, 'project_id' => $project->id, 'developer_id' => $project->developer_id, 'city' => $project->city, 'district' => $project->district, 'is_featured' => $i < 3, 'verified_by' => $admin->id, 'verified_at' => now()]);
            Listing::factory()->pending()->create(['seller_id' => $seller->id, 'project_id' => $project->id, 'developer_id' => $project->developer_id, 'city' => $project->city]);
        }

        $listing = Listing::published()->first();
        $interest = Interest::factory()->create(['listing_id' => $listing->id, 'buyer_id' => $buyer->id, 'status' => 'qualified', 'cash_available' => $listing->paid_amount]);
        Deal::factory()->create(['listing_id' => $listing->id, 'interest_id' => $interest->id, 'buyer_id' => $buyer->id, 'case_manager_id' => $manager->id, 'stage' => 'documents']);
        $buyer->buyerPreference()->create(['cities' => ['New Cairo'], 'unit_types' => ['apartment'], 'max_cash' => 2_500_000, 'max_installment' => 60_000, 'min_bedrooms' => 2]);

        Evaluation::factory(4)->create();
        Evaluation::factory()->create(['name' => $seller->name, 'phone' => $seller->phone, 'user_id' => $seller->id, 'total_price' => 10_000_000, 'paid_amount' => 2_000_000]);
    }
}
