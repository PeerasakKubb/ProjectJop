<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('courses')
            ->where('subject', 'ฟิสิกส์')
            ->orWhere('title', 'like', '%ฟิสิกส์%')
            ->update([
                'title' => 'ไฟฟ้าและสนามไฟฟ้าระดับวุฒิ ปวส.',
                'subject' => 'ไฟฟ้าและสนามไฟฟ้า',
                'grade_level' => 'ปวส.',
                'description' => 'เนื้อหาไฟฟ้า กระแสไฟฟ้า แม่เหล็กไฟฟ้า และสนามไฟฟ้า สำหรับระดับ ปวส.',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('courses')
            ->where('subject', 'ไฟฟ้าและสนามไฟฟ้า')
            ->where('title', 'ไฟฟ้าและสนามไฟฟ้าระดับวุฒิ ปวส.')
            ->update([
                'title' => 'ฟิสิกส์ ม.4',
                'subject' => 'ฟิสิกส์',
                'grade_level' => 'ม.4',
                'description' => 'หลักการพื้นฐานด้านแรง การเคลื่อนที่ และพลังงาน',
                'updated_at' => now(),
            ]);
    }
};
