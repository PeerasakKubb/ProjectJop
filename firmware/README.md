# Smart Classroom — Firmware (ESP32 × 2)

| Sketch | บอร์ด | อุปกรณ์ |
|--------|--------|---------|
| **`esp32_a_hub/`** | ESP-A | RC522 + DHT22 + LCD 1602 I2C |
| **`esp32_b_lights/`** | ESP-B | LED × 6 + Relay 12V |
| **`HARDWARE.md`** | — | คู่มือต่อสายทีละขั้น |

โมดูลตั้งเวลา 12V ต่อทางสายไฟ (ไม่ใช่ GPIO) — รายละเอียดใน `HARDWARE.md`

---

## เริ่มใช้งานเร็ว

1. อ่าน **`HARDWARE.md`** แล้วต่อสายให้ครบ
2. แก้ WiFi / IP ในทั้งสองไฟล์ `.ino` ถ้าไม่ใช่ค่าเดิม:

```cpp
const char* WIFI_SSID   = "ELECLAB2";
const char* WIFI_PASS   = "171172173";
const char* SERVER_HOST = "10.5.200.126";
```

3. รัน Laravel:

```bash
php artisan serve --host=0.0.0.0 --port=8000
php artisan db:seed --class=LightsDevicesSeeder
```

4. อัปโหลด **ESP-A** ก่อน แล้วค่อย **ESP-B**
5. Serial Monitor ทั้งคู่ = **115200**

คัดลอกไป Arduino IDE:

```text
Documents\Arduino\esp32_a_hub\esp32_a_hub.ino
Documents\Arduino\esp32_b_lights\esp32_b_lights.ino
```

(เปิดด้วย File → Open เลือกไฟล์ `.ino` ในโฟลเดอร์นั้น)
