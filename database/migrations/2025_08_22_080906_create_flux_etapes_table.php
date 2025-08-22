<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('flux_etapes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grand_projet_id')->constrained('grand_projets')->cascadeOnDelete();
            $table->string('from_etat')->nullable();
            $table->string('to_etat');
            $table->timestamp('happened_at')->useCurrent();
            $table->foreignId('by_user')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('flux_etapes');
    }
};
