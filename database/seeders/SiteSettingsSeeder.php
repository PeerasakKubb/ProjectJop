<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Hero
            ['key' => 'hero_badge', 'value' => 'IoT · RFID · AI Ready', 'group' => 'hero', 'label' => 'ป้าย Hero', 'type' => 'text'],
            ['key' => 'hero_title_1', 'value' => 'ห้องเรียน', 'group' => 'hero', 'label' => 'หัวข้อบรรทัด 1', 'type' => 'text'],
            ['key' => 'hero_title_highlight', 'value' => 'อัจฉริยะ', 'group' => 'hero', 'label' => 'หัวข้อเน้น (gradient)', 'type' => 'text'],
            ['key' => 'hero_title_2', 'value' => 'ยุคใหม่', 'group' => 'hero', 'label' => 'หัวข้อบรรทัด 2', 'type' => 'text'],
            ['key' => 'hero_subtitle', 'value' => 'เช็คชื่อ RFID · ควบคุม IoT · เซนเซอร์ Real-time · แจ้งเตือน · ข้อสอบออนไลน์ — ครบในที่เดียว', 'group' => 'hero', 'label' => 'คำอธิบาย Hero', 'type' => 'textarea'],

            // Stats (displayed on front)
            ['key' => 'stat_students', 'value' => '500+', 'group' => 'stats', 'label' => 'จำนวนนักเรียน (แสดง)', 'type' => 'text'],
            ['key' => 'stat_courses', 'value' => '20+', 'group' => 'stats', 'label' => 'จำนวนคอร์ส (แสดง)', 'type' => 'text'],
            ['key' => 'stat_teachers', 'value' => '15+', 'group' => 'stats', 'label' => 'จำนวนครู (แสดง)', 'type' => 'text'],
            ['key' => 'stat_satisfaction', 'value' => '98%', 'group' => 'stats', 'label' => 'ความพึงพอใจ', 'type' => 'text'],

            // About / Contact
            ['key' => 'school_name', 'value' => 'Smart Classroom Platform', 'group' => 'contact', 'label' => 'ชื่อโรงเรียน/องค์กร', 'type' => 'text'],
            ['key' => 'contact_email', 'value' => 'admin@school.local', 'group' => 'contact', 'label' => 'อีเมลติดต่อ', 'type' => 'text'],
            ['key' => 'contact_phone', 'value' => '02-xxx-xxxx', 'group' => 'contact', 'label' => 'เบอร์โทร', 'type' => 'text'],
            ['key' => 'about_intro', 'value' => 'Smart Classroom Platform เป็นระบบห้องเรียนอัจฉริยะที่รวม RFID, IoT, เซนเซอร์, แจ้งเตือน และ LMS ไว้ในแพลตฟอร์มเดียว', 'group' => 'about', 'label' => 'ข้อความเกี่ยวกับ (หน้าบ้าน)', 'type' => 'textarea'],
        ];

        foreach ($settings as $setting) {
            SiteSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
