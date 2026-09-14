<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 30)->unique();
            $table->foreignId('producer_id')->nullable()->constrained('producers')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('category', [
                'precio',
                'pesaje',
                'calidad',
                'pago',
                'atencion',
                'ruta',
                'otro'
            ]);
            $table->string('subject');
            $table->text('description');
            $table->text('evidence_files')->nullable();
            $table->enum('priority', ['baja', 'normal', 'alta', 'urgente'])->default('normal');
            $table->enum('status', ['abierto', 'en_revision', 'respondido', 'cerrado', 'rechazado'])->default('abierto');
            $table->text('staff_response')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 20)->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_answered')->default(false);
            $table->foreignId('answered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('answer')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('contact_messages');
    }
};
