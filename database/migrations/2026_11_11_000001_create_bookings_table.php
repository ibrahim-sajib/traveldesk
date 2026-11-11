
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('hotel_external_id');
            $table->string('hotel_name');

            $table->string('guest_name');
            $table->string('guest_email');

            $table->date('check_in');
            $table->date('check_out');

            $table->unsignedTinyInteger('guests');
            $table->decimal('total_price', 12, 2);

            $table->string('status')->default('confirmed');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('hotel_external_id');
            $table->index('status');
            $table->index(['guest_email', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
