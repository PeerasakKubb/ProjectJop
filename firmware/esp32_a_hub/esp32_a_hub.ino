/*
 * ESP-A Hub — Fresh build (เวอร์ชันก่อนแก้ขา)
 * RC522 + DHT11 + OLED SSD1306 0.96"
 *
 * OLED: SDA=21 SCL=22
 * DHT11: DATA=32
 * RC522: SS=5 RST=17 SCK=18 MOSI=23 MISO=19
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>
#include <SPI.h>
#include <MFRC522.h>
#include <Wire.h>
#include <U8g2lib.h>
#include <DHT.h>
#include <time.h>
#include "soc/soc.h"
#include "soc/rtc_cntl_reg.h"

const char* WIFI_SSID   = "ELECLAB2";
const char* WIFI_PASS   = "171172173";

// ออนไลน์ Render (หลัก) — local เป็นสำรองถ้า Render ล้ม
const bool  USE_HTTPS_DEFAULT = true;
const char* RENDER_HOST = "education-app-myav.onrender.com";
const char* LOCAL_HOST  = "10.5.200.126";
const int   LOCAL_PORT  = 8000;

const char* RFID_API_KEY     = "demo-reader-key-12345";
const char* TEMP_API_KEY     = "sensor-temp-key";
const char* HUMIDITY_API_KEY = "sensor-humidity-key";

bool useRender = true;

#define PIN_OLED_SDA  21
#define PIN_OLED_SCL  22
#define PIN_DHT       32
#define PIN_RFID_SS   5
#define PIN_RFID_RST  17
#define PIN_RFID_SCK  18
#define PIN_RFID_MISO 19
#define PIN_RFID_MOSI 23

U8G2_SSD1306_128X64_NONAME_F_SW_I2C u8_ssd(
    U8G2_R0, PIN_OLED_SCL, PIN_OLED_SDA, U8X8_PIN_NONE);
U8G2_SH1106_128X64_NONAME_F_SW_I2C u8_sh(
    U8G2_R0, PIN_OLED_SCL, PIN_OLED_SDA, U8X8_PIN_NONE);
U8G2* oled = nullptr;

DHT dht(PIN_DHT, DHT11);
MFRC522 mfrc522(PIN_RFID_SS, PIN_RFID_RST);

const unsigned long SENSOR_MS = 15000;
const unsigned long UI_MS     = 1000;
const unsigned long RFID_COOLDOWN_MS = 2500;

unsigned long lastSensorMs = 0;
unsigned long lastUiMs = 0;
unsigned long lastRfidMs = 0;
unsigned long overlayUntil = 0;

float lastTemp = NAN;
float lastHum = NAN;
String myIp = "-";
bool oledOk = false;
bool rfidOk = false;
bool timeOk = false;
char lineTitle[22] = "";
char lineDetail[22] = "";

String baseUrl() {
  if (useRender) {
    return String("https://") + RENDER_HOST;
  }
  return String("http://") + LOCAL_HOST + ":" + LOCAL_PORT;
}

WiFiClient plainClient;
WiFiClientSecure secureClient;

void logln(const String& s) {
  Serial.println(s);
  Serial.flush();
}

void showOverlay(const char* title, const char* detail, unsigned long ms = 3500) {
  snprintf(lineTitle, sizeof(lineTitle), "%.21s", title);
  snprintf(lineDetail, sizeof(lineDetail), "%.21s", detail);
  overlayUntil = millis() + ms;
  logln(String("[UI] ") + title + " | " + detail);
}

void getClock(char* t, char* d) {
  struct tm ti;
  if (!getLocalTime(&ti, 40)) {
    strcpy(t, "--:--:--");
    strcpy(d, "----/--/--");
    return;
  }
  snprintf(t, 16, "%02d:%02d:%02d", ti.tm_hour, ti.tm_min, ti.tm_sec);
  snprintf(d, 16, "%02d/%02d/%04d", ti.tm_mday, ti.tm_mon + 1, ti.tm_year + 1900);
}

void drawUi() {
  if (!oledOk || !oled) return;
  char t[16], d[16], sens[28];
  getClock(t, d);
  if (isnan(lastTemp) || isnan(lastHum)) strcpy(sens, "T:--C  H:--%");
  else snprintf(sens, sizeof(sens), "T:%.0fC  H:%.0f%%", lastTemp, lastHum);

  oled->clearBuffer();
  oled->setFont(u8g2_font_6x12_tf);
  oled->drawStr(0, 10, "Smart Classroom");
  oled->drawHLine(0, 12, 128);

  if (millis() < overlayUntil) {
    oled->setFont(u8g2_font_logisoso16_tf);
    oled->drawStr(0, 36, lineTitle);
    oled->setFont(u8g2_font_6x12_tf);
    oled->drawStr(0, 54, lineDetail);
  } else {
    oled->setFont(u8g2_font_logisoso22_tf);
    oled->drawStr(0, 40, t);
    oled->setFont(u8g2_font_6x12_tf);
    oled->drawStr(0, 54, d);
    oled->drawStr(0, 64, sens);
    oled->drawStr(78, 54, rfidOk ? "Tap card" : "No RFID");
  }
  oled->sendBuffer();
}

bool startOled(U8G2& drv, const char* name, uint8_t addr7) {
  drv.setI2CAddress(addr7 << 1);
  if (!drv.begin()) return false;
  drv.setPowerSave(0);
  oled = &drv;
  oledOk = true;
  oled->clearBuffer();
  oled->setFont(u8g2_font_ncenB14_tr);
  oled->drawStr(20, 30, "HELLO");
  oled->setFont(u8g2_font_6x12_tf);
  oled->drawStr(10, 50, name);
  oled->sendBuffer();
  Serial.printf("[OLED] OK %s @ 0x%02X\n", name, addr7);
  delay(900);
  return true;
}

bool initOled() {
  logln("[OLED] SSD1306 0.96\" SDA=21 SCL=22");
  pinMode(PIN_OLED_SDA, INPUT_PULLUP);
  pinMode(PIN_OLED_SCL, INPUT_PULLUP);
  delay(80);
  Wire.begin(PIN_OLED_SDA, PIN_OLED_SCL);
  Wire.setClock(100000);
  delay(80);
  bool has3C = false, has3D = false;
  int hits = 0;
  for (uint8_t a = 1; a < 127; a++) {
    Wire.beginTransmission(a);
    if (Wire.endTransmission() == 0) {
      hits++;
      Serial.printf("  I2C 0x%02X\n", a);
      if (a == 0x3C) has3C = true;
      if (a == 0x3D) has3D = true;
    }
  }
  Serial.printf("[OLED] scan hits=%d\n", hits);
  Wire.end();
  delay(30);
  if (hits == 0) return false;
  uint8_t addrs[2];
  int n = 0;
  if (has3C) addrs[n++] = 0x3C;
  if (has3D) addrs[n++] = 0x3D;
  if (n == 0) { addrs[n++] = 0x3C; addrs[n++] = 0x3D; }
  for (int i = 0; i < n; i++) {
    if (startOled(u8_ssd, "SSD1306", addrs[i])) return true;
    if (startOled(u8_sh, "SH1106", addrs[i])) return true;
  }
  return false;
}

bool connectWifi() {
  showOverlay("WiFi", "Connecting...", 2000);
  drawUi();
  WiFi.persistent(false);
  WiFi.mode(WIFI_OFF);
  delay(150);
  WiFi.mode(WIFI_STA);
  WiFi.setSleep(false);
  WiFi.setTxPower(WIFI_POWER_11dBm);
  WiFi.begin(WIFI_SSID, WIFI_PASS);
  unsigned long t0 = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - t0 < 25000) {
    delay(400);
    Serial.print(".");
  }
  Serial.println();
  if (WiFi.status() != WL_CONNECTED) {
    showOverlay("WiFi FAIL", "Check SSID/pass");
    drawUi();
    return false;
  }
  myIp = WiFi.localIP().toString();
  showOverlay("WiFi OK", myIp.c_str(), 1500);
  drawUi();
  delay(800);
  overlayUntil = 0;
  return true;
}

void syncTime() {
  showOverlay("Time", "NTP sync...", 2000);
  drawUi();
  configTime(7 * 3600, 0, "pool.ntp.org", "time.nist.gov");
  struct tm ti;
  for (int i = 0; i < 20; i++) {
    if (getLocalTime(&ti, 500)) {
      timeOk = true;
      char t[16], d[16];
      getClock(t, d);
      showOverlay(t, d, 1200);
      drawUi();
      delay(700);
      overlayUntil = 0;
      return;
    }
  }
  timeOk = false;
  showOverlay("Time FAIL", "No NTP");
  drawUi();
}

bool postRaw(const String& path, const String& body,
             const char* hdrKey, const char* hdrVal, String& responseOut) {
  responseOut = "";
  if (WiFi.status() != WL_CONNECTED) return false;

  HTTPClient http;
  http.setTimeout(useRender ? 45000 : 8000);
  http.setReuse(false);

  String url = baseUrl() + path;
  Serial.println(url);

  bool okBegin = useRender
    ? http.begin(secureClient, url)
    : http.begin(plainClient, url);

  if (!okBegin) return false;

  http.addHeader("Content-Type", "application/json");
  if (hdrKey && hdrVal) http.addHeader(hdrKey, hdrVal);

  int code = http.POST(body);
  responseOut = http.getString();
  Serial.printf("POST %s -> %d\n", path.c_str(), code);
  http.end();

  if (code >= 200 && code < 300) {
    useRender = USE_HTTPS_DEFAULT;
    return true;
  }

  // Render ล่มชั่วคราว → ลอง local รอบถัดไป แล้วเด้งกลับ Render อีก
  if (useRender && (code < 0 || code >= 500)) {
    Serial.println("Render fail — next try LOCAL then RENDER again");
    useRender = false;
  } else if (!useRender) {
    Serial.println("Local fail — next try RENDER");
    useRender = true;
  }
  return false;
}

bool postReading(const char* key, float value) {
  String body = String("{\"api_key\":\"") + key + "\",\"value\":" + String(value, 1) + "}";
  String resp;
  return postRaw("/api/sensors/reading", body, nullptr, nullptr, resp);
}

bool readDht(float& t, float& h) {
  for (int i = 0; i < 4; i++) {
    h = dht.readHumidity();
    delay(60);
    t = dht.readTemperature();
    if (!isnan(t) && !isnan(h) && t >= 0 && t <= 60 && h >= 0 && h <= 100) return true;
    delay(1800);
  }
  return false;
}

void handleSensor() {
  float t, h;
  if (!readDht(t, h)) {
    showOverlay("Sensor", "DHT11 fail");
    drawUi();
    return;
  }
  lastTemp = t;
  lastHum = h;
  Serial.printf("DHT11 T=%.0f H=%.0f\n", t, h);
  postReading(TEMP_API_KEY, t);
  postReading(HUMIDITY_API_KEY, h);
}

String uidHex(MFRC522::Uid* uid) {
  String s;
  for (byte i = 0; i < uid->size; i++) {
    if (uid->uidByte[i] < 0x10) s += "0";
    s += String(uid->uidByte[i], HEX);
  }
  s.toUpperCase();
  return s;
}

String jsonStr(const String& json, const char* key) {
  String needle = String("\"") + key + "\":\"";
  int p = json.indexOf(needle);
  if (p < 0) return "";
  p += needle.length();
  int e = json.indexOf('"', p);
  if (e < 0) return "";
  return json.substring(p, e);
}

bool jsonBoolTrue(const String& json, const char* key) {
  String needle = String("\"") + key + "\":";
  int p = json.indexOf(needle);
  if (p < 0) return false;
  p += needle.length();
  while (p < (int)json.length() && (json[p] == ' ')) p++;
  return json.startsWith("true", p);
}

bool initRfid() {
  logln("[RFID] RC522 SS=5 RST=17 (3.3V only)");
  pinMode(PIN_RFID_SS, OUTPUT);
  digitalWrite(PIN_RFID_SS, HIGH);
  pinMode(PIN_RFID_RST, OUTPUT);
  digitalWrite(PIN_RFID_RST, LOW);
  delay(40);
  digitalWrite(PIN_RFID_RST, HIGH);
  delay(40);
  SPI.begin(PIN_RFID_SCK, PIN_RFID_MISO, PIN_RFID_MOSI, PIN_RFID_SS);
  SPI.setFrequency(1000000);
  mfrc522.PCD_Init();
  delay(80);
  byte ver = 0;
  for (int i = 0; i < 5; i++) {
    ver = mfrc522.PCD_ReadRegister(mfrc522.VersionReg);
    Serial.printf("  version=0x%02X\n", ver);
    if (ver != 0x00 && ver != 0xFF) break;
    mfrc522.PCD_Init();
    delay(80);
  }
  rfidOk = (ver != 0x00 && ver != 0xFF);
  if (!rfidOk) {
    showOverlay("RFID FAIL", "Check 3.3V/SPI");
    drawUi();
    return false;
  }
  showOverlay("RFID ready", "Tap student card", 1500);
  drawUi();
  delay(800);
  overlayUntil = 0;
  return true;
}

void handleRfid() {
  if (!rfidOk) return;
  if (millis() < overlayUntil) return; // อย่าอ่านบัตรใหม่ระหว่างโชว์ข้อความ/UID
  if (millis() - lastRfidMs < RFID_COOLDOWN_MS) return;
  if (!mfrc522.PICC_IsNewCardPresent() || !mfrc522.PICC_ReadCardSerial()) return;
  lastRfidMs = millis();
  String uid = uidHex(&mfrc522.uid);
  logln("RFID UID=" + uid);
  showOverlay("Scanning", uid.c_str(), 1500);
  drawUi();
  String body = String("{\"card_uid\":\"") + uid + "\"}";
  String resp;
  bool httpOk = postRaw("/api/rfid/scan", body, "X-API-Key", RFID_API_KEY, resp);
  mfrc522.PICC_HaltA();
  mfrc522.PCD_StopCrypto1();

  bool unknownCard = (!httpOk) &&
      ((resp.indexOf("success") >= 0 && resp.indexOf("false") >= 0) ||
       resp.indexOf("ไม่พบ") >= 0);

  if (!httpOk) {
    if (unknownCard) {
      // 1) บอกก่อนว่าไม่รู้จักบัตร
      showOverlay("Access Denied", "Unknown card", 2500);
      drawUi();
      delay(2500);
      // 2) แล้วค้างเลขบัตร 8 วินาที ให้คัดลอกลงทะเบียน
      char uidLine[22];
      snprintf(uidLine, sizeof(uidLine), "%.21s", uid.c_str());
      showOverlay("Card UID", uidLine, 8000);
      drawUi();
      lastRfidMs = millis(); // กันอ่านซ้ำระหว่างโชว์ UID
    } else {
      showOverlay("Server error", "Check network", 3500);
      drawUi();
    }
    return;
  }

  String name = jsonStr(resp, "name");
  String status = jsonStr(resp, "status");
  bool dup = jsonBoolTrue(resp, "duplicate");
  if (name.length() > 18) name = name.substring(0, 18);
  if (dup) showOverlay("Already in", name.c_str());
  else if (status == "late") showOverlay("LATE", name.length() ? name.c_str() : "Checked in");
  else if (status == "present") showOverlay("PRESENT", name.length() ? name.c_str() : "Checked in");
  else showOverlay("OK", name.length() ? name.c_str() : "Checked in");
  drawUi();
}

void setup() {
  WRITE_PERI_REG(RTC_CNTL_BROWN_OUT_REG, 0);
  Serial.begin(115200);
  delay(1200);
  logln("");
  logln("########################################");
  logln("# ESP-A Fresh: RFID + DHT11 + OLED    #");
  logln("########################################");
  initOled();
  pinMode(PIN_DHT, INPUT_PULLUP);
  dht.begin();
  delay(1500);
  float t, h;
  if (readDht(t, h)) {
    lastTemp = t;
    lastHum = h;
    char b[22];
    snprintf(b, sizeof(b), "T=%.0fC H=%.0f%%", t, h);
    showOverlay("DHT11 OK", b, 1200);
    drawUi();
    delay(700);
    overlayUntil = 0;
  }
  if (connectWifi()) {
    secureClient.setInsecure();
    useRender = USE_HTTPS_DEFAULT;
    syncTime();
  }
  initRfid();
  lastSensorMs = millis() - SENSOR_MS;
  lastUiMs = millis();
  drawUi();
  logln("---- SUMMARY ----");
  logln(String("OLED : ") + (oledOk ? "OK" : "FAIL"));
  logln(String("RFID : ") + (rfidOk ? "OK" : "FAIL"));
  logln(String("WiFi : ") + myIp);
  logln(String("Server: ") + baseUrl());
  logln("RFID mode = classroom attendance check-in");
  logln("-----------------");
}

unsigned long lastRenderRetryMs = 0;

void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    showOverlay("WiFi lost", "Reconnecting");
    drawUi();
    WiFi.reconnect();
    delay(1500);
    return;
  }
  // ทุก 60 วินาที บังคับยิง Render อีกครั้ง กันค้างที่เครื่องในแล็บ
  if (!useRender && millis() - lastRenderRetryMs >= 60000) {
    lastRenderRetryMs = millis();
    useRender = true;
    Serial.println("Retry RENDER host");
  }
  handleRfid();
  if (millis() - lastSensorMs >= SENSOR_MS) {
    lastSensorMs = millis();
    handleSensor();
  }
  if (millis() - lastUiMs >= UI_MS) {
    lastUiMs = millis();
    drawUi();
  }
}
