<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('examens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grand_projet_id')->constrained('grand_projets')->cascadeOnDelete();
            $table->unsignedInteger('numero_examen'); // 1, 2, 3...
            $table->date('date_commission')->nullable();
            $table->enum('avis', ['favorable','defavorable','ajourne','sans_avis'])->default('sans_avis');
            $table->text('observations')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['grand_projet_id','numero_examen']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('examens');
    }
};
