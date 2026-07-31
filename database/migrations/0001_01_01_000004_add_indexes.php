<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('uid');
        });

        Schema::table('usermeta', function (Blueprint $table) {
            $table->index('uid');
        });

        Schema::table('useravatars', function (Blueprint $table) {
            $table->index('uid');
        });

        Schema::table('playermeta', function (Blueprint $table) {
            $table->index(['uid', 'meta_key']);
        });

        Schema::table('userworlds', function (Blueprint $table) {
            $table->index(['uid', 'type']);
        });

        if (Schema::hasTable('items')) {
            // the max length should be 56, but 64 offers breathing room
            DB::statement('CREATE INDEX items_name_index ON items (name(64))');

            Schema::table('items', function (Blueprint $table) {
                $table->index('code');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['uid']);
        });

        Schema::table('usermeta', function (Blueprint $table) {
            $table->dropIndex(['uid']);
        });

        Schema::table('useravatars', function (Blueprint $table) {
            $table->dropIndex(['uid']);
        });

        Schema::table('playermeta', function (Blueprint $table) {
            $table->dropIndex(['uid', 'meta_key']);
        });

        Schema::table('userworlds', function (Blueprint $table) {
            $table->dropIndex(['uid', 'type']);
        });

        if (Schema::hasTable('items')) {
            DB::statement('DROP INDEX items_name_index ON items');

            Schema::table('items', function (Blueprint $table) {
                $table->dropIndex(['code']);
            });
        }
    }
};
