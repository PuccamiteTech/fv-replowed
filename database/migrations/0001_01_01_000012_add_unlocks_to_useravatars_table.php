<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('useravatars', 'unlocks')) {
            return;
        }

        Schema::table('useravatars', function (Blueprint $table) {
            $table->mediumText('unlocks')->nullable()->after('value');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('useravatars', 'unlocks')) {
            return;
        }

        Schema::table('useravatars', function (Blueprint $table) {
            $table->dropColumn('unlocks');
        });
    }
};
