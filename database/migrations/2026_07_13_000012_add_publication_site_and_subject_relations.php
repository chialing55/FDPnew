<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_web';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        if (! $schema->hasTable('publication_site')) {
            $schema->create('publication_site', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('publication_id')->constrained('publications')->cascadeOnDelete();
                $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['publication_id', 'site_id']);
            });
        }

        if (! $schema->hasTable('publication_subject')) {
            $schema->create('publication_subject', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('publication_id')->constrained('publications')->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['publication_id', 'subject_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('publication_subject');
        Schema::connection($this->connection)->dropIfExists('publication_site');
    }
};
