<?php

use App\Models\Loot\Loot;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('loots', function (Blueprint $table) {
            if (!Schema::hasColumn('loots', 'id')) {
                $table->id()->first();
            }
            $table->string('rewardable_type')->nullable()->change();
            $table->string('rewardable_id')->nullable()->change();
        });
        $loots = Loot::where('rewardable_type', 'None')->get();
        foreach ($loots as $loot) {
            $loot->update([
                'rewardable_type' => null,
                'rewardable_id'   => null,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        $loots = Loot::where('rewardable_type', null)->get();
        foreach ($loots as $loot) {
            $loot->update([
                'rewardable_type' => 'None',
                'rewardable_id'   => 1,
            ]);
        }
        Schema::table('loots', function (Blueprint $table) {
            $table->string('rewardable_type')->change();
            $table->integer('rewardable_id')->unsigned()->change();
        });
    }
};
