/*
 * ESP-B Lights — LED x6 + Relay 12V
 *
 * สาย LED: GPIO → 220Ω → LED(+) ; LED(−) → GND
 *   LED1–6 → 13, 14, 16, 17, 25, 26
 * Relay IN → GPIO 27
 *
 * Board: ESP32 Dev Module | Serial: 115200
 *
 * ลำดับ: ทดสอบ LED → WiFi → poll local → ถ้าไม่ได้ค่อยลอง Render
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>
#include "soc/soc.h"
#include "soc/rtc_cntl_reg.h"

const char* WIFI_SSID = "ELECLAB2";
const char* WIFI_PASS = "171172173";
const char* LIGHTS_API_KEY = "lights-station-key";

// Primary = local Laravel (ตอนไฟเคยติด)
const char* LOCAL_HOST = "10.5.200.126";
const int   LOCAL_PORT = 8000;

// Fallback = Render ออนไลน์
const char* RENDER_HOST = "education-app-myav.onrender.com";

const int LED_PINS[6] = {13, 14, 16, 17, 25, 26};
#define RELAY_PIN 27

// true = HIGH = เปิด | false = LOW = เปิด (relay บางตัว active-low)
bool RELAY_ACTIVE_HIGH = true;

const unsigned long POLL_MS = 2000;
const unsigned long HTTP_TIMEOUT_MS = 20000;

unsigned long lastPollMs = 0;
bool ledOn[6] = {false, false, false, false, false, false};
bool syncedOnce = false;
int failStreak = 0;
bool useRender = false;

WiFiClient plainClient;
WiFiClientSecure secureClient;

String pollUrl() {
  if (useRender) {
    return String("https://") + RENDER_HOST + "/api/devices/poll-lights";
  }
  return String("http://") + LOCAL_HOST + ":" + LOCAL_PORT + "/api/devices/poll-lights";
}

void allOutputsOff() {
  for (int i = 0; i < 6; i++) {
    digitalWrite(LED_PINS[i], LOW);
  }
  digitalWrite(RELAY_PIN, RELAY_ACTIVE_HIGH ? LOW : HIGH);
}

void applyOutputs() {
  bool anyOn = false;
  for (int i = 0; i < 6; i++) {
    digitalWrite(LED_PINS[i], ledOn[i] ? HIGH : LOW);
    if (ledOn[i]) anyOn = true;
  }
  bool relayLevel = RELAY_ACTIVE_HIGH ? anyOn : !anyOn;
  digitalWrite(RELAY_PIN, relayLevel ? HIGH : LOW);
}

void hardwareSelfTest() {
  Serial.println("SELF-TEST: เปิด LED ทีละดวง...");
  for (int i = 0; i < 6; i++) {
    allOutputsOff();
    digitalWrite(LED_PINS[i], HIGH);
    digitalWrite(RELAY_PIN, RELAY_ACTIVE_HIGH ? HIGH : LOW);
    Serial.printf("  LED%d ON\n", i + 1);
    delay(250);
  }
  allOutputsOff();
  Serial.println("SELF-TEST: เปิดทั้ง 6 ดวง 1 วินาที...");
  for (int i = 0; i < 6; i++) {
    digitalWrite(LED_PINS[i], HIGH);
    ledOn[i] = true;
  }
  applyOutputs();
  delay(1000);
  for (int i = 0; i < 6; i++) {
    ledOn[i] = false;
  }
  allOutputsOff();
  Serial.println("SELF-TEST เสร็จ — ถ้าตอนนี้ LED ไม่ติดเลย = ปัญหาสาย/ไฟเลี้ยง");
}

bool connectWifi() {
  Serial.print("WiFi ");
  Serial.println(WIFI_SSID);

  WiFi.persistent(false);
  WiFi.mode(WIFI_STA);
  WiFi.setSleep(false);
  WiFi.setTxPower(WIFI_POWER_11dBm);

  delay(300);
  WiFi.disconnect(true);
  delay(200);
  WiFi.begin(WIFI_SSID, WIFI_PASS);

  unsigned long t0 = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - t0 < 30000) {
    delay(500);
    Serial.print(".");
  }
  Serial.println();

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi FAIL — รีเซ็ตใน 5 วินาที");
    delay(5000);
    ESP.restart();
    return false;
  }

  Serial.print("WiFi OK IP=");
  Serial.println(WiFi.localIP());
  Serial.print("Gateway=");
  Serial.println(WiFi.gatewayIP());
  return true;
}

bool parseLedState(const String& json, int ledIndex, bool& outOn) {
  String key = String("\"api_key\":\"led-") + String(ledIndex + 1) + "-key\"";
  int pos = json.indexOf(key);
  if (pos < 0) return false;

  int end = min((int)json.length(), pos + 180);
  String chunk = json.substring(pos, end);

  int onPos = chunk.indexOf("\"is_on\":");
  if (onPos < 0) return false;

  onPos += 8;
  while (onPos < (int)chunk.length() &&
         (chunk[onPos] == ' ' || chunk[onPos] == '\t')) {
    onPos++;
  }

  if (chunk.startsWith("true", onPos) || chunk[onPos] == '1') {
    outOn = true;
    return true;
  }
  if (chunk.startsWith("false", onPos) || chunk[onPos] == '0') {
    outOn = false;
    return true;
  }
  return false;
}

bool pollLights() {
  if (WiFi.status() != WL_CONNECTED) return false;

  HTTPClient http;
  http.setTimeout(HTTP_TIMEOUT_MS);
  http.setReuse(false);

  String url = pollUrl();
  Serial.println(url);

  bool okBegin;
  if (useRender) {
    okBegin = http.begin(secureClient, url);
  } else {
    okBegin = http.begin(plainClient, url);
  }

  if (!okBegin) {
    Serial.println("HTTP begin FAIL");
    return false;
  }

  http.addHeader("X-API-Key", LIGHTS_API_KEY);
  http.addHeader("User-Agent", "ESP32-B-Lights/2.0");

  int code = http.GET();
  String body = http.getString();
  http.end();

  Serial.printf("poll-lights → HTTP %d (len=%d)\n", code, body.length());
  if (code < 200 || code >= 300) {
    Serial.println(body.substring(0, 200));
    return false;
  }

  int parsed = 0;
  bool changed = false;

  for (int i = 0; i < 6; i++) {
    bool on = false;
    if (parseLedState(body, i, on)) {
      parsed++;
      if (ledOn[i] != on) {
        ledOn[i] = on;
        changed = true;
        Serial.printf("  LED%d = %s\n", i + 1, on ? "ON" : "OFF");
      }
    }
  }

  if (parsed < 6) {
    Serial.printf("WARNING: parse ได้แค่ %d/6\n", parsed);
    Serial.println(body.substring(0, 240));
    return false;
  }

  if (!syncedOnce || changed) {
    applyOutputs();
    syncedOnce = true;
    if (!changed) Serial.println("  sync OK (สถานะเดิม)");
  } else {
    applyOutputs();
  }

  return true;
}

void setup() {
  WRITE_PERI_REG(RTC_CNTL_BROWN_OUT_REG, 0);

  Serial.begin(115200);
  delay(1500);
  Serial.println();
  Serial.println("=== ESP-B Lights v2 (local + Render fallback) ===");

  for (int i = 0; i < 6; i++) {
    pinMode(LED_PINS[i], OUTPUT);
  }
  pinMode(RELAY_PIN, OUTPUT);
  allOutputsOff();

  hardwareSelfTest();

  secureClient.setInsecure();
  connectWifi();

  useRender = false;
  Serial.println("ลอง poll LOCAL ก่อน...");
  for (int attempt = 1; attempt <= 5; attempt++) {
    Serial.printf("local attempt %d/5\n", attempt);
    if (pollLights()) {
      failStreak = 0;
      Serial.println("ใช้ LOCAL server");
      lastPollMs = millis();
      return;
    }
    delay(2000);
  }

  Serial.println("LOCAL ไม่ได้ — สลับไป RENDER...");
  useRender = true;
  for (int attempt = 1; attempt <= 5; attempt++) {
    Serial.printf("render attempt %d/5\n", attempt);
    if (pollLights()) {
      failStreak = 0;
      Serial.println("ใช้ RENDER server");
      lastPollMs = millis();
      return;
    }
    delay(5000);
  }

  Serial.println("ERROR: poll ไม่สำเร็จทั้ง local และ Render");
  lastPollMs = millis();
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi reconnect...");
    allOutputsOff();
    syncedOnce = false;
    WiFi.disconnect(true);
    delay(1000);
    connectWifi();
    return;
  }

  if (millis() - lastPollMs >= POLL_MS) {
    lastPollMs = millis();
    if (pollLights()) {
      failStreak = 0;
    } else {
      failStreak++;
      Serial.printf("fail streak=%d\n", failStreak);
      if (failStreak >= 3) {
        useRender = !useRender;
        failStreak = 0;
        syncedOnce = false;
        Serial.printf("สลับเซิร์ฟเวอร์ → %s\n", useRender ? "RENDER" : "LOCAL");
      }
    }
  }
}
