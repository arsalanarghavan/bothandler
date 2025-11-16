<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bots', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('github_repo_url');
            $table->string('github_branch')->default('main');
            $table->string('service_type')->default('generic'); // e.g. generic, telegram-bot, website, cron
            $table->string('domain')->nullable()->unique();
            $table->json('environment')->nullable();
            $table->text('deploy_command')->nullable(); // optional custom deploy command (e.g. docker compose up -d)
            $table->string('status')->default('inactive');
            $table->timestamp('last_deployed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bots');
    }
};


