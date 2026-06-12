<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'fs_mortality';

    public function up(): void
    {
        Schema::connection('fs_mortality')->table('tree_individuals', function (Blueprint $table) {
            foreach (['spcode', 'qx', 'qy', 'subqx', 'subqy', 'qudx', 'qudy'] as $column) {
                if (Schema::connection('fs_mortality')->hasColumn('tree_individuals', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::connection('fs_mortality')->table('tree_individuals', function (Blueprint $table) {
            if (! Schema::connection('fs_mortality')->hasColumn('tree_individuals', 'spcode')) {
                $table->string('spcode', 50)->nullable()->index()->after('stemid');
            }
            if (! Schema::connection('fs_mortality')->hasColumn('tree_individuals', 'qx')) {
                $table->integer('qx')->nullable()->after('spcode');
            }
            if (! Schema::connection('fs_mortality')->hasColumn('tree_individuals', 'qy')) {
                $table->integer('qy')->nullable()->after('qx');
            }
            if (! Schema::connection('fs_mortality')->hasColumn('tree_individuals', 'subqx')) {
                $table->integer('subqx')->nullable()->after('qy');
            }
            if (! Schema::connection('fs_mortality')->hasColumn('tree_individuals', 'subqy')) {
                $table->integer('subqy')->nullable()->after('subqx');
            }
            if (! Schema::connection('fs_mortality')->hasColumn('tree_individuals', 'qudx')) {
                $table->decimal('qudx', 6, 2)->nullable()->after('subqy');
            }
            if (! Schema::connection('fs_mortality')->hasColumn('tree_individuals', 'qudy')) {
                $table->decimal('qudy', 6, 2)->nullable()->after('qudx');
            }
        });
    }
};
