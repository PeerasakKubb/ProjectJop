# Smart Classroom — คู่มือฮาร์ดแวร์ (ESP32 × 2)

ของที่มี: **LED, โมดูลตั้งเวลา 12V, DHT22, LCD 1602 + I2C, RC522, Relay 12V, ESP32 สองตัว**

| บอร์ด | หน้าที่ | Sketch |
|------|---------|--------|
| **ESP-A** | RFID + อุณหภูมิ/ความชื้น + จอ LCD | `esp32_a_hub/esp32_a_hub.ino` |
| **ESP-B** | หลอด LED × 6 + รีเลย์ 12V | `esp32_b_lights/esp32_b_lights.ino` |

โมดูลตั้งเวลา 12V **ไม่ต่อเข้าขา ESP** — ต่อทางสายไฟ 12V คั่นก่อนรีเลย์ (ตั้งช่วงเวลาเปิดห้อง)

---

## ก่อนต่อสาย

1. ถอด USB ออกจาก ESP32 ก่อนเสียบ/ถอดสาย
2. **RC522 ใช้ 3.3V เท่านั้น** — อย่าต่อ 5V (พังได้)
3. DHT22 / LCD / Relay โมดูลส่วนใหญ่ใช้ **5V** ได้
4. GND ของทุกโมดูลต้องร่วมกับ GND ของ ESP32 ตัวนั้น

---

## ESP-A — สายต่อ

### 1) DHT22

| ขา DHT22 | ไปที่ |
|----------|--------|
| VCC | **5V** ของ ESP32 |
| DATA | **GPIO 4** |
| GND | GND |

ถ้าโมดูลไม่มีตัวต้านบนบอร์ด: ใส่ **4.7k–10k** ระหว่าง DATA กับ VCC

### 2) LCD 1602 + I2C

| ขา I2C | ไปที่ |
|--------|--------|
| VCC | **5V** |
| GND | GND |
| SDA | **GPIO 21** |
| SCL | **GPIO 22** |

ที่อยู่ I2C ในโค้ด: `0x27` (ถ้าจอไม่ขึ้น ลองแก้เป็น `0x3F`)

### 3) RC522 (RFID)

| ขา RC522 | ไปที่ |
|----------|--------|
| 3.3V / VCC | **3.3V** |
| GND | GND |
| SDA (SS) | **GPIO 5** |
| SCK | **GPIO 18** |
| MOSI | **GPIO 23** |
| MISO | **GPIO 19** |
| RST | **GPIO 17** |
| IRQ | ไม่ต่อ |

อย่าต่อ RST ไป GPIO 22 — ชนกับ LCD SCL

### แผนภาพสรุป ESP-A

```text
        ESP32-A
    ┌─────────────────┐
    │ 5V ── DHT22 VCC │     DHT22 DATA → GPIO4
    │ 5V ── LCD VCC   │     LCD SDA→21  SCL→22
    │ 3.3V─ RC522 VCC │     RC522 SS→5  SCK→18
    │                 │            MOSI→23 MISO→19 RST→17
    │ GND ── ร่วมทุกตัว│
    └─────────────────┘
```

---

## ESP-B — สายต่อ

### 1) LED × 6 (หลอดจำลองบน breadboard)

แต่ละหลอด: **GPIO → ตัวต้าน 220Ω → ขา (+) LED → ขา (−) LED → GND**

| หลอด | GPIO |
|------|------|
| LED 1 | **13** |
| LED 2 | **14** |
| LED 3 | **16** |
| LED 4 | **17** |
| LED 5 | **25** |
| LED 6 | **26** |

ตรงกับ `led-1-key` … `led-6-key` บนเว็บ

### 2) Relay 12V

| ขาโมดูลรีเลย์ | ไปที่ |
|---------------|--------|
| VCC | **5V** (หรือตามที่โมดูลระบุ — บางตัวต้อง 5V) |
| GND | GND |
| IN / Signal | **GPIO 27** |

ฝั่งโหลด 12V (ตัวอย่างหลอด/พัดลม 12V):

```text
12V+ ──[โมดูลตั้งเวลา 12V]── COM ของรีเลย์
NO ของรีเลย์ ── โหลด(+) 
โหลด(−) ── 12V− (GND ของแหล่ง 12V)
```

- ตั้งเวลาบนโมดูล = ช่วงที่ **มีไฟ 12V พร้อมจ่าย**
- ESP เปิดรีเลย์เมื่อเว็บสั่งเปิดหลอดอย่างน้อย 1 ดวง
- ถ้าโมดูลรีเลย์เปิดกลับกัน (ติดตอน IN=LOW) แก้ในโค้ด `RELAY_ACTIVE_HIGH = false`

### 3) โมดูลตั้งเวลา 12V

| ใช้ทำอะไร | วิธีต่อ |
|-----------|---------|
| จำกัดช่วงเวลาเปิดห้อง | ต่อ **อนุกรม** กับสาย 12V+ ก่อนเข้า COM ของรีเลย์ |
| ไม่ต้องต่อขา GPIO | ตั้งเวลาบนตัวโมดูลเอง (dial / จอ ตามรุ่น) |

---

## ไฟเลี้ยงแนะนำ

| ส่วน | ไฟ |
|------|-----|
| ESP32 ทั้งสอง | USB จากคอมพิวเตอร์ หรือ 5V ภายนอก |
| RC522 | 3.3V จาก ESP-A |
| DHT22 + LCD | 5V จาก ESP-A |
| LED บน breadboard | จาก GPIO (ไม่กินไฟมาก) |
| โหลด 12V + รีเลย์ฝั่งคอนแทค | **แหล่ง 12V แยก** (อย่าดูดจากขา ESP) |

GND ของแหล่ง 12V กับ GND ของ ESP-B **ควรต่อร่วมกัน** ถ้าโมดูลรีเลย์เป็นแบบออปโตคัปเปิลที่ไม่แยกกราวด์ — ตามคู่มือโมดูล

---

## Arduino IDE

1. Board: **ESP32 Dev Module**
2. Libraries:
   - ESP-A: `MFRC522`, `DHT sensor library`, `Adafruit Unified Sensor`, `LiquidCrystal I2C`
   - ESP-B: ไม่ต้องเพิ่ม library
3. เปิดโฟลเดอร์ sketch (ไฟล์เดียวต่อโฟลเดอร์):
   - `Documents\Arduino\esp32_a_hub\esp32_a_hub.ino`
   - `Documents\Arduino\esp32_b_lights\esp32_b_lights.ino`
4. Serial Monitor = **115200** → กด EN/RESET หลังอัปโหลด

---

## Laravel (คอมกับ ESP ต้อง WiFi เดียวกัน)

```bash
cd ~/projects/education-app
php artisan serve --host=0.0.0.0 --port=8000
```

Windows ต้องมี portproxy `:8000` → WSL และ `SERVER_HOST` ในโค้ด = IP Windows (ตอนนี้ `10.5.200.126`)

Seed หลอด 6 ดวง (ถ้ายังไม่มี):

```bash
php artisan db:seed --class=LightsDevicesSeeder
```

### API keys

| อุปกรณ์ | Key |
|---------|-----|
| RFID reader | `demo-reader-key-12345` |
| อุณหภูมิ | `sensor-temp-key` |
| ความชื้น | `sensor-humidity-key` |
| สถานีหลอด (ESP-B) | `lights-station-key` |
| หลอด 1–6 | `led-1-key` … `led-6-key` |

### ทดสอบโดยไม่มีฮาร์ดแวร์

```bash
curl -X POST http://10.5.200.126:8000/api/sensors/reading \
  -H "Content-Type: application/json" \
  -d '{"api_key":"sensor-temp-key","value":28}'

curl -X POST http://10.5.200.126:8000/api/rfid/scan \
  -H "Content-Type: application/json" \
  -H "X-API-Key: demo-reader-key-12345" \
  -d '{"card_uid":"CARD0001"}'

curl http://10.5.200.126:8000/api/devices/poll-lights \
  -H "X-API-Key: lights-station-key"
```

---

## ลำดับทดสอบหลังต่อสาย

1. **ESP-A** อัปโหลด → Serial ขึ้น `WiFi OK` + `RC522 ready`
2. รอ ~15 วินาที → เห็น `DHT22 T=...` และ `HTTP 200` → เปิดหน้าเซ็นเซอร์บนเว็บ
3. แตะบัตร → Serial ขึ้น UID → ไป Admin **บัตร RFID** ลงทะเบียน UID นั้น (รหัสจริง ไม่ใช่ CARD0001)
4. **ESP-B** อัปโหลด → Serial ขึ้น `poll-lights → HTTP 200`
5. เว็บเปิดหลอด 1–6 → LED ติด + รีเลย์คลิก (ถ้าอยู่ในช่วงเวลาที่โมดูลตั้งเวลาอนุญาต)

---

## แก้ปัญหาเร็ว

| อาการ | ตรวจ |
|-------|------|
| Serial ว่าง | baud 115200 / พอร์ต COM / กด EN |
| DHT22 fail | VCC=5V, DATA=GPIO4, GND, pull-up |
| LCD ว่าง | ที่อยู่ 0x27↔0x3F, SDA21 SCL22, ไฟ 5V |
| RFID อ่านไม่ได้ | 3.3V, สาย SPI, RST=17 |
| LED ไม่ติดตามเว็บ | Laravel รันอยู่, `lights-station-key`, seed หลอด, WiFi เดียวกัน |
| รีเลย์คลิกแต่โหลดไม่ติด | โมดูลตั้งเวลาปิดช่วงนั้น / สาย COM-NO / แหล่ง 12V |
