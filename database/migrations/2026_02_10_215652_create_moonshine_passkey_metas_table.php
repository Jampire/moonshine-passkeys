<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(config('passkeys.table_names.metas'), function (Blueprint $table) {
            $table->id();
            $table->morphs('personable');
            $table->uuid('public_id')->unique();
            $table->boolean('is_active')->index()->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('passkeys.table_names.metas'));
    }
};
