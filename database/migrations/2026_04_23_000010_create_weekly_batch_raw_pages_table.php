<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeeklyBatchRawPagesTable extends Migration
{
    public function up()
    {
        Schema::create('weekly_batch_raw_pages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id');
            $table->string('console', 10);      // switch-1, switch-2
            $table->string('list_type', 10);    // new, upcoming
            $table->unsignedTinyInteger('page_number');
            $table->text('raw_content');
            $table->timestamp('parsed_at')->nullable();
            $table->timestamps();

            $table->foreign('batch_id')->references('id')->on('weekly_batches')->onDelete('cascade');
            $table->unique(['batch_id', 'console', 'list_type', 'page_number'], 'wbrp_batch_console_list_page_unique');
            $table->index(['batch_id', 'console', 'list_type'], 'wbrp_batch_console_list_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('weekly_batch_raw_pages');
    }
}
