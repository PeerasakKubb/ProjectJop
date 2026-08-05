/*
 * ESP-B Lights — LED x6 + Relay 12V
 * แก้ brownout: ปิด brownout detector + ลดกำลัง WiFi + ต่อโหลดช้า
 *
 * สาย LED: GPIO → 220Ω → LED(+) ; LED(−) → GND
 *   LED1–6 → 13, 14, 16, 17, 25, 26
 * Relay IN → GPIO 27
 *
 * Board: ESP32 Dev Module | Serial: 115200
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include "soc/soc.h"
#include "soc/rtc_cntl_reg.h"

const char* WIFI_SSID   = "ELECLAB2";
const char* WIFI_PASS   = "171172173";
const char* SERVER_HOST = "10.5.200.126";
const int   SERVER_PORT = 8000;
const char* LIGHTS_API_KEY = "lights-station-key";

const int LED_PINS[6] = {13, 14, 16, 17, 25, 26};
#define RELAY_PIN 27
const bool RELAY_ACTIVE_HIGH = true;

const unsigned long POLL_MS = 2000;
unsigned long lastPollMs = 0;

bool ledOn[6] = {false, false, false, false, false, false};
bool syncedOnce = false;

String baseUrl() {
  return String("http://") + SERVER_HOST + ":" + SERVER_PORT;
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

bool connectWifi() {
  Serial.print("WiFi ");
  Serial.println(WIFI_SSID);

  WiFi.persistent(false);
  WiFi.mode(WIFI_STA);
  WiFi.setSleep(true);
  WiFi.setTxPower(WIFI_POWER_11dBm);

  delay(300);
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
  return true;
}

bool parseLedState(const String& json, int ledIndex, bool& outOn) {
  String key = String("\"api_key\":\"led-") + String(ledIndex + 1) + "-key\"";
  int pos = json.indexOf(key);
  if (pos < 0) return false;

  int end = min((int)json.length(), pos + 160);
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

void pollLights() {
  if (WiFi.status() != WL_CONNECTED) return;

  HTTPClient http;
  http.setTimeout(8000);
  String url = baseUrl() + "/api/devices/poll-lights";

  if (!http.begin(url)) {
    Serial.println("HTTP begin FAIL");
    return;
  }

  http.addHeader("X-API-Key", LIGHTS_API_KEY);
  int code = http.GET();
  String body = http.getString();
  http.end();

  Serial.printf("poll-lights → HTTP %d\n", code);
  if (code < 200 || code >= 300) {
    Serial.println(body.substring(0, 160));
    return;
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
    Serial.printf("WARNING: พบแค่ %d/6 หลอด — รัน LightsDevicesSeeder\n", parsed);
  }

  if (!syncedOnce || changed) {
    applyOutputs();
    syncedOnce = true;
    if (!changed) Serial.println("  sync OK");
  }
}

void setup() {
  WRITE_PERI_REG(RTC_CNTL_BROWN_OUT_REG, 0);

  Serial.begin(115200);
  delay(1500);
  Serial.println();
  Serial.println("=== ESP-B Lights (brownout-safe) ===");

  for (int i = 0; i < 6; i++) {
    pinMode(LED_PINS[i], OUTPUT);
  }
  pinMode(RELAY_PIN, OUTPUT);
  allOutputsOff();

  delay(500);
  connectWifi();

  delay(500);
  pollLights();
  lastPollMs = millis();

  Serial.println("Loop — poll ทุก 2s");
  Serial.println("เว็บ: /admin/devices");
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi reconnect...");
    allOutputsOff();
    WiFi.disconnect(true);
    delay(1000);
    connectWifi();
    return;
  }

  if (millis() - lastPollMs >= POLL_MS) {
    lastPollMs = millis();
    pollLights();
  }
}
