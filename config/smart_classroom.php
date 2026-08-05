<?php

/**
 * โครงสร้างโมดูลตาม Diagram ระบบ Smart Classroom (หลังบ้าน /admin)
 *
 * ชั้น Hub → Input → Control → Learning → Admin
 */
return [

    'layers' => [
        'hub' => [
            'label' => 'ศูนย์กลาง',
            'subtitle' => 'Dashboard & ภาพรวม',
            'color' => 'violet',
        ],
        'input' => [
            'label' => 'ข้อมูลเข้า',
            'subtitle' => 'RFID · เซนเซอร์',
            'color' => 'cyan',
        ],
        'control' => [
            'label' => 'ควบคุม & แจ้งเตือน',
            'subtitle' => 'IoT · แจ้งเตือน',
            'color' => 'pink',
        ],
        'learning' => [
            'label' => 'การเรียนรู้',
            'subtitle' => 'คอร์ส · ข้อสอบ',
            'color' => 'amber',
        ],
        'admin' => [
            'label' => 'จัดการระบบ',
            'subtitle' => 'Admin · Teacher',
            'color' => 'emerald',
        ],
    ],

    'modules' => [
        'dashboard' => [
            'layer' => 'hub',
            'label' => 'Dashboard',
            'description' => 'ภาพรวมสถิติ กราฟ และสถานะทุกโมดูล',
            'icon' => '📊',
            'route' => 'admin.dashboard',
            'patterns' => ['admin.dashboard'],
            'roles' => null,
        ],
        'attendance' => [
            'layer' => 'input',
            'label' => 'เช็คชื่อ RFID',
            'description' => 'บันทึกเวลาเข้าเรียนอัตโนมัติ',
            'icon' => '📋',
            'route' => 'admin.attendance.index',
            'patterns' => ['admin.attendance.*'],
            'roles' => null,
        ],
        'sensors' => [
            'layer' => 'input',
            'label' => 'เซนเซอร์',
            'description' => 'อุณหภูมิ ความชื้น สภาพแวดล้อม',
            'icon' => '🌡️',
            'route' => 'admin.sensors.index',
            'patterns' => ['admin.sensors.*'],
            'roles' => null,
        ],
        'devices' => [
            'layer' => 'control',
            'label' => 'อุปกรณ์ IoT',
            'description' => 'เปิด/ปิดไฟ พัดลม ผ่านเว็บและ Bot',
            'icon' => '💡',
            'route' => 'admin.devices.index',
            'patterns' => ['admin.devices.*'],
            'roles' => null,
        ],
        'notifications' => [
            'layer' => 'control',
            'label' => 'แจ้งเตือน',
            'description' => 'แจ้งเตือนอัตโนมัติ Telegram / LINE',
            'icon' => '🔔',
            'route' => 'admin.notifications.index',
            'patterns' => ['admin.notifications.*'],
            'roles' => ['admin', 'teacher'],
        ],
        'courses' => [
            'layer' => 'learning',
            'label' => 'คอร์สเรียน',
            'description' => 'บทเรียน ข้อสอบ ติดตามความคืบหน้า',
            'icon' => '📚',
            'route' => 'admin.courses.index',
            'patterns' => ['admin.courses.*', 'admin.lessons.*', 'admin.exams.*', 'admin.progress.*'],
            'roles' => null,
        ],
        'rfid' => [
            'layer' => 'admin',
            'label' => 'บัตร RFID',
            'description' => 'ลงทะเบียนบัตรนักเรียน',
            'icon' => '🪪',
            'route' => 'admin.rfid.index',
            'patterns' => ['admin.rfid.*'],
            'roles' => ['admin', 'teacher'],
        ],
        'architecture' => [
            'layer' => 'hub',
            'label' => 'แผนภาพระบบ',
            'description' => 'โครงสร้าง Diagram ทั้งระบบ',
            'icon' => '🗺️',
            'route' => 'admin.architecture',
            'patterns' => ['admin.architecture'],
            'roles' => null,
        ],
        'site_settings' => [
            'layer' => 'admin',
            'label' => 'ตั้งค่าเว็บไซต์',
            'description' => 'แก้ไข Hero, สถิติ, ข้อมูลติดต่อหน้าบ้าน',
            'icon' => '⚙️',
            'route' => 'admin.settings.index',
            'patterns' => ['admin.settings.*'],
            'roles' => ['admin'],
        ],
        'users' => [
            'layer' => 'admin',
            'label' => 'จัดการผู้ใช้',
            'description' => 'เพิ่ม/แก้ไข Admin, ครู, นักเรียน',
            'icon' => '👥',
            'route' => 'admin.users.index',
            'patterns' => ['admin.users.*'],
            'roles' => ['admin'],
        ],
    ],

    'flow' => [
        ['from' => 'attendance', 'to' => 'dashboard'],
        ['from' => 'sensors', 'to' => 'dashboard'],
        ['from' => 'sensors', 'to' => 'notifications'],
        ['from' => 'devices', 'to' => 'dashboard'],
        ['from' => 'notifications', 'to' => 'dashboard'],
        ['from' => 'courses', 'to' => 'dashboard'],
    ],

];
