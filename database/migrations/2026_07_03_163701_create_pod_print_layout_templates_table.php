<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pod_print_layout_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->string('scope')->default('system');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('mailing_format')->default('letter');
            $table->string('slot')->default('letter_file');
            $table->string('status')->default('active');
            $table->mediumText('html_shell');
            $table->mediumText('css')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'scope']);
            $table->index(['mailing_format', 'slot', 'status']);
        });

        DB::table('pod_print_layout_templates')->insert([
            'scope' => 'system',
            'name' => 'Basic Letter',
            'slug' => 'basic-letter',
            'mailing_format' => 'letter',
            'slot' => 'letter_file',
            'status' => 'active',
            'html_shell' => <<<'HTML'
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        {{ css }}
    </style>
</head>
<body>
    {{ content }}
</body>
</html>
HTML,
            'css' => <<<'CSS'
@page {
    size: letter;
    margin: 0.5in;
}

body {
    color: #111827;
    font-family: Georgia, "Times New Roman", serif;
    font-size: 11pt;
    line-height: 1.45;
}

.page {
    break-after: page;
    min-height: 9.75in;
}

.page:last-child {
    break-after: auto;
}
CSS,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pod_print_layout_templates');
    }
};
