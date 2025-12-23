<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPromptLimits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('prompts', function (Blueprint $table) {
            //prompt limit on how many times a user can submit
            $table->integer('limit')->nullable()->default(null);
            //length of time for which a prompt can be submited
            $table->enum('limit_period', ['Hour', 'Day', 'Week', 'Month', 'Year'])->nullable()->default(null);
            //per character limit? it won't keep track of which characters are being submitted due to
            //conflicts arising if a character is cameo'd, but will limit a person's submissions
            //based on how many they own. EX: you own Char-A and Char-B and you can submit
            //three times a month per character. If per character is on, you can submit 6 times total,
            //though a user will be able to submit using char-A the entire time. Still, same number of rewards...
            $table->boolean('limit_character')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prompts', function (Blueprint $table) {
            //
            $table->dropColumn('limit');
            $table->dropColumn('limit_period');
            $table->dropColumn('limit_character');
        });
    }
}
