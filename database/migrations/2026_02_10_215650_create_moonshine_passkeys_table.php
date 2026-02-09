<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(config('passkeys.table_names.passkeys'), function (Blueprint $table) {
            $table->id();
            $table->morphs('personable');
            $table->string('name');
            // This field participates in selection,
            // but it's impossible to set binary index on MySQL table
            $table->binary('credential_id');
            $table->json('data');
            $table->unsignedBigInteger('counter')->default(0);
            $table->json('transports')->nullable();
            $table->timestamps();

            $table->unique(['personable_type', 'personable_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('passkeys.table_names.passkeys'));
    }
};
