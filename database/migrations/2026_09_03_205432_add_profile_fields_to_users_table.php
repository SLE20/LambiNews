<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('slug')
                ->nullable()
                ->unique()
                ->after('name');

            $table->string('job_title')
                ->nullable()
                ->after('role');

            $table->text('bio')
                ->nullable()
                ->after('job_title');

            $table->string('photo')
                ->nullable()
                ->after('bio');

            $table->string('facebook_url')
                ->nullable()
                ->after('photo');

            $table->string('instagram_url')
                ->nullable()
                ->after('facebook_url');

            $table->string('tiktok_url')
                ->nullable()
                ->after('instagram_url');
        });

        DB::table('users')
            ->whereNull('slug')
            ->orderBy('id')
            ->eachById(function ($user) {
                $baseSlug = Str::slug($user->name);

                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'slug' => $baseSlug.'-'.$user->id,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['slug']);

            $table->dropColumn([
                'slug',
                'job_title',
                'bio',
                'photo',
                'facebook_url',
                'instagram_url',
                'tiktok_url',
            ]);
        });
    }
};