<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\Course;
use App\Models\Device;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\RfidCard;
use App\Models\RfidReader;
use App\Models\Room;
use App\Models\Sensor;
use App\Models\SensorReading;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (User::query()->exists()) {
            $this->call(SiteSettingsSeeder::class);

            return;
        }

        $admin = User::create([
            'name' => 'ผู้ดูแลระบบ',
            'email' => 'admin@school.local',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $teacher = User::create([
            'name' => 'อาจารย์สมชาย',
            'email' => 'teacher@school.local',
            'password' => Hash::make('password'),
            'role' => 'teacher',
        ]);

        $students = collect([
            ['name' => 'นักเรียน ก', 'email' => 'student1@school.local'],
            ['name' => 'นักเรียน ข', 'email' => 'student2@school.local'],
            ['name' => 'นักเรียน ค', 'email' => 'student3@school.local'],
            ['name' => 'นักเรียน ง', 'email' => 'student4@school.local'],
            ['name' => 'นักเรียน จ', 'email' => 'student5@school.local'],
        ])->map(fn ($s) => User::create([
            ...$s,
            'password' => Hash::make('password'),
            'role' => 'student',
        ]));

        $room = Room::create([
            'name' => 'ห้อง 101',
            'building' => 'อาคาร A',
            'floor' => 1,
            'capacity' => 40,
        ]);

        $reader = RfidReader::create([
            'name' => 'RFID Reader หน้าห้อง 101',
            'room_id' => $room->id,
            'location' => 'ประตูหน้าห้อง',
            'api_key' => 'demo-reader-key-12345',
        ]);

        $students->each(function ($student, $index) use ($reader, $room) {
            RfidCard::create([
                'user_id' => $student->id,
                'card_uid' => 'CARD' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
            ]);

            if ($index < 3) {
                AttendanceRecord::create([
                    'user_id' => $student->id,
                    'rfid_reader_id' => $reader->id,
                    'room_id' => $room->id,
                    'scanned_at' => Carbon::today()->setTime(7, 45 + ($index * 10)),
                    'type' => 'in',
                    'status' => $index === 2 ? 'late' : 'present',
                ]);
            }
        });

        Device::create(['name' => 'หลอดไฟห้องเรียน 1', 'type' => 'light', 'room_id' => $room->id, 'is_on' => false, 'api_key' => 'led-1-key', 'mqtt_topic' => 'classroom/101/led1']);
        Device::create(['name' => 'หลอดไฟห้องเรียน 2', 'type' => 'light', 'room_id' => $room->id, 'is_on' => false, 'api_key' => 'led-2-key', 'mqtt_topic' => 'classroom/101/led2']);
        Device::create(['name' => 'หลอดไฟห้องเรียน 3', 'type' => 'light', 'room_id' => $room->id, 'is_on' => false, 'api_key' => 'led-3-key', 'mqtt_topic' => 'classroom/101/led3']);
        Device::create(['name' => 'หลอดไฟห้องเรียน 4', 'type' => 'light', 'room_id' => $room->id, 'is_on' => false, 'api_key' => 'led-4-key', 'mqtt_topic' => 'classroom/101/led4']);
        Device::create(['name' => 'หลอดไฟห้องเรียน 5', 'type' => 'light', 'room_id' => $room->id, 'is_on' => false, 'api_key' => 'led-5-key', 'mqtt_topic' => 'classroom/101/led5']);
        Device::create(['name' => 'หลอดไฟห้องเรียน 6', 'type' => 'light', 'room_id' => $room->id, 'is_on' => false, 'api_key' => 'led-6-key', 'mqtt_topic' => 'classroom/101/led6']);

        $tempSensor = Sensor::create([
            'name' => 'อุณหภูมิ',
            'type' => 'temperature',
            'room_id' => $room->id,
            'unit' => '°C',
            'max_threshold' => 30,
            'api_key' => 'sensor-temp-key',
        ]);

        $humiditySensor = Sensor::create([
            'name' => 'ความชื้น',
            'type' => 'humidity',
            'room_id' => $room->id,
            'unit' => '%',
            'max_threshold' => 80,
            'api_key' => 'sensor-humidity-key',
        ]);

        for ($i = 23; $i >= 0; $i--) {
            SensorReading::create([
                'sensor_id' => $tempSensor->id,
                'value' => 26 + rand(0, 50) / 10,
                'recorded_at' => Carbon::now()->subHours($i),
            ]);
            SensorReading::create([
                'sensor_id' => $humiditySensor->id,
                'value' => 55 + rand(0, 200) / 10,
                'recorded_at' => Carbon::now()->subHours($i),
            ]);
        }

        $course = Course::create([
            'teacher_id' => $teacher->id,
            'title' => 'ฟิสิกส์ ม.4',
            'subject' => 'ฟิสิกส์',
            'grade_level' => 'ม.4',
            'description' => 'หลักการพื้นฐานด้านแรง การเคลื่อนที่ และพลังงาน',
            'price' => 0,
            'is_published' => true,
        ]);

        $lesson1 = Lesson::create([
            'course_id' => $course->id,
            'title' => 'บทที่ 1: แรงและการเคลื่อนที่',
            'content' => "แรงคือสิ่งที่ทำให้วัตถุเปลี่ยนแปลงสภาพการเคลื่อนที่\n\nกฎของนิวตัน:\n1. วัตถุจะอยู่นิ่งหรือเคลื่อนที่ด้วยความเร็วคงที่ หากไม่มีแรงกระทำ\n2. F = ma\n3. แรงกระทำและแรงปฏิกิริยามีขนาดเท่ากัน ทิศตรงข้าม",
            'order' => 1,
        ]);

        Lesson::create([
            'course_id' => $course->id,
            'title' => 'บทที่ 2: พลังงาน',
            'content' => "พลังงานคือความสามารถในการทำงาน\n\nพลังงานจลน์: Ek = ½mv²\nพลังงานศักย์: Ep = mgh",
            'order' => 2,
        ]);

        $students->each(fn ($s) => Enrollment::create([
            'user_id' => $s->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ]));

        $exam = Exam::create([
            'course_id' => $course->id,
            'title' => 'แบบทดสอบบทที่ 1',
            'description' => 'ทดสอบความรู้เรื่องแรงและการเคลื่อนที่',
            'type' => 'multiple_choice',
            'is_published' => true,
            'time_limit_minutes' => 30,
        ]);

        $q1 = Question::create([
            'exam_id' => $exam->id,
            'question_text' => 'กฎข้อที่ 1 ของนิวตัน กล่าวว่าอะไร?',
            'type' => 'multiple_choice',
            'explanation' => 'วัตถุจะคงสภาพการเคลื่อนที่เดิม หากไม่มีแรงภายนอกกระทำ',
            'order' => 1,
        ]);
        QuestionOption::create(['question_id' => $q1->id, 'option_text' => 'F = ma', 'is_correct' => false]);
        QuestionOption::create(['question_id' => $q1->id, 'option_text' => 'วัตถุจะอยู่นิ่งหรือเคลื่อนที่ด้วยความเร็วคงที่ หากไม่มีแรงกระทำ', 'is_correct' => true]);
        QuestionOption::create(['question_id' => $q1->id, 'option_text' => 'แรงกระทำเท่ากับแรงปฏิกิริยา', 'is_correct' => false]);

        $q2 = Question::create([
            'exam_id' => $exam->id,
            'question_text' => 'สูตรคำนวณแรงตามกฎข้อที่ 2 ของนิวตันคือ?',
            'type' => 'multiple_choice',
            'explanation' => 'F = ma โดย F คือแรง, m คือมวล, a คือความเร่ง',
            'order' => 2,
        ]);
        QuestionOption::create(['question_id' => $q2->id, 'option_text' => 'F = mv', 'is_correct' => false]);
        QuestionOption::create(['question_id' => $q2->id, 'option_text' => 'F = ma', 'is_correct' => true]);
        QuestionOption::create(['question_id' => $q2->id, 'option_text' => 'F = mg', 'is_correct' => false]);

        $this->call(SiteSettingsSeeder::class);
    }
}
