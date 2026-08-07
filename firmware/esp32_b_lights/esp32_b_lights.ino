/*
 * ESP-B Lights — LED x6 + Relay 12V
 * โหมดออนไลน์: poll Render เป็นหลัก, local เป็นสำรอง
 *
 * สาย LED: GPIO → 220Ω → LED(+) ; LED(−) → GND
 *   LED1–6 → 13, 14, 16, 17, 25, 26
 * Relay IN → GPIO 27
 *
 * Board: ESP32 Dev Module | Serial: 115200
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>
#include "soc/soc.h"
#include "soc/rtc_cntl_reg.h"

const char* WIFI_SSID = "ELECLAB2";
const char* WIFI_PASS = "171172173";
const char* LIGHTS_API_KEY = "lights-station-key";

// Primary = Render ออนไลน์ (สั่งจากเว็บ onrender.com)
const char* RENDER_HOST = "education-app-myav.onrender.com";

// Fallback = Laravel local ในแล็บ
const char* LOCAL_HOST = "10.5.200.126";
const int   LOCAL_PORT = 8000;

const int LED_PINS[6] = {13, 14, 16, 17, 25, 26};
#define RELAY_PIN 27
bool RELAY_ACTIVE_HIGH = true;

const unsigned long POLL_MS = 2500;
const unsigned long HTTP_TIMEOUT_MS = 45000;

unsigned long lastPollMs = 0;
bool ledOn[6] = {false, false, false, false, false, false};
bool syncedOnce = false;
int failStreak = 0;
bool useRender = true; // ออนไลน์เป็นค่าเริ่มต้น

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
  Serial.println("SELF-TEST LEDs...");
  for (int i = 0; i < 6; i++) {
    allOutputsOff();
    digitalWrite(LED_PINS[i], HIGH);
    digitalWrite(RELAY_PIN, RELAY_ACTIVE_HIGH ? HIGH : LOW);
    delay(200);
  }
  for (int i = 0; i < 6; i++) {
    digitalWrite(LED_PINS[i], HIGH);
    ledOn[i] = true;
  }
  applyOutputs();
  delay(800);
  for (int i = 0; i < 6; i++) {
    ledOn[i] = false;
  }
  allOutputsOff();
  Serial.println("SELF-TEST done");
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
    Serial.println("WiFi FAIL — restart");
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

  bool okBegin = useRender
    ? http.begin(secureClient, url)
    : http.begin(plainClient, url);

  if (!okBegin) {
    Serial.println("HTTP begin FAIL");
    return false;
  }

  http.addHeader("X-API-Key", LIGHTS_API_KEY);
  http.addHeader("User-Agent", "ESP32-B-Lights/online");

  int code = http.GET();
  String body = http.getString();
  http.end();

  Serial.printf("poll-lights → HTTP %d\n", code);
  if (code < 200 || code >= 300) {
    Serial.println(body.substring(0, 180));
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
    Serial.printf("WARNING: parsed %d/6\n", parsed);
    return false;
  }

  applyOutputs();
  syncedOnce = true;
  if (!changed) Serial.println("  sync OK");
  return true;
}

void setup() {
  WRITE_PERI_REG(RTC_CNTL_BROWN_OUT_REG, 0);

  Serial.begin(115200);
  delay(1500);
  Serial.println();
  Serial.println("=== ESP-B Lights ONLINE (Render primary) ===");

  for (int i = 0; i < 6; i++) {
    pinMode(LED_PINS[i], OUTPUT);
  }
  pinMode(RELAY_PIN, OUTPUT);
  allOutputsOff();

  hardwareSelfTest();
  secureClient.setInsecure();
  connectWifi();

  useRender = true;
  Serial.println("Poll RENDER (cold start may take 30-60s)...");
  for (int attempt = 1; attempt <= 8; attempt++) {
    Serial.printf("render attempt %d/8\n", attempt);
    if (pollLights()) {
      failStreak = 0;
      Serial.println("Using RENDER");
      lastPollMs = millis();
      return;
    }
    delay(4000);
  }

  Serial.println("RENDER failed — try LOCAL...");
  useRender = false;
  for (int attempt = 1; attempt <= 3; attempt++) {
    if (pollLights()) {
      failStreak = 0;
      Serial.println("Using LOCAL");
      lastPollMs = millis();
      return;
    }
    delay(2000);
  }

  Serial.println("ERROR: no server reachable");
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
      if (failStreak >= 4) {
        useRender = !useRender;
        failStreak = 0;
        syncedOnce = false;
        Serial.printf("switch server → %s\n", useRender ? "RENDER" : "LOCAL");
      }
    }
  }
}
