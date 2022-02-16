<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nova_media_library', function (Blueprint $table) {
            $table->id();
            $table->string('title')->index()->nullable();
            $table->string('original_name')->nullable();
            $table->timestamp('created')->index()->useCurrent();
            $table->string('type')->index();
            $table->string('folder')->index();
            $table->string('name');
            $table->boolean('private')->default(0);
            $table->boolean('lp')->default(0);
            $table->text('options')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nova_media_library');
    }
};
