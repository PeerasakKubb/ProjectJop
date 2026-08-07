/*
 * ESP-B Lights — LED x6 + Relay 12V
 * ออนไลน์เท่านั้น: https://education-app-myav.onrender.com
 *
 * Board: ESP32 Dev Module | Serial: 115200
 * LED1-6 -> GPIO 13,14,16,17,25,26 | Relay -> GPIO 27
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>
#include "soc/soc.h"
#include "soc/rtc_cntl_reg.h"

const char* WIFI_SSID = "ELECLAB2";
const char* WIFI_PASS = "171172173";
const char* LIGHTS_API_KEY = "lights-station-key";
const char* RENDER_HOST = "education-app-myav.onrender.com";

const int LED_PINS[6] = {13, 14, 16, 17, 25, 26};
#define RELAY_PIN 27
const bool RELAY_ACTIVE_HIGH = true;

const unsigned long POLL_MS = 2000;
const unsigned long HTTP_TIMEOUT_MS = 60000;

bool ledOn[6] = {false, false, false, false, false, false};
unsigned long lastPollMs = 0;

WiFiClientSecure secureClient;

void allOutputsOff() {
  for (int i = 0; i < 6; i++) digitalWrite(LED_PINS[i], LOW);
  digitalWrite(RELAY_PIN, RELAY_ACTIVE_HIGH ? LOW : HIGH);
}

void applyOutputs() {
  bool anyOn = false;
  for (int i = 0; i < 6; i++) {
    digitalWrite(LED_PINS[i], ledOn[i] ? HIGH : LOW);
    if (ledOn[i]) anyOn = true;
  }
  digitalWrite(RELAY_PIN, (RELAY_ACTIVE_HIGH ? anyOn : !anyOn) ? HIGH : LOW);
}

bool connectWifi() {
  WiFi.persistent(false);
  WiFi.mode(WIFI_STA);
  WiFi.setSleep(false);
  WiFi.begin(WIFI_SSID, WIFI_PASS);
  unsigned long t0 = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - t0 < 30000) {
    delay(400);
    Serial.print(".");
  }
  Serial.println();
  if (WiFi.status() != WL_CONNECTED) {
    delay(4000);
    ESP.restart();
  }
  Serial.print("WiFi OK ");
  Serial.println(WiFi.localIP());
  return true;
}

bool parseLedState(const String& json, int ledIndex, bool& outOn) {
  String key = String("\"api_key\":\"led-") + String(ledIndex + 1) + "-key\"";
  int pos = json.indexOf(key);
  if (pos < 0) return false;
  String chunk = json.substring(pos, min((int)json.length(), pos + 180));
  int onPos = chunk.indexOf("\"is_on\":");
  if (onPos < 0) return false;
  onPos += 8;
  while (onPos < (int)chunk.length() && (chunk[onPos] == ' ' || chunk[onPos] == '\t')) onPos++;
  if (chunk.startsWith("true", onPos) || chunk[onPos] == '1') { outOn = true; return true; }
  if (chunk.startsWith("false", onPos) || chunk[onPos] == '0') { outOn = false; return true; }
  return false;
}

bool pollLights() {
  HTTPClient http;
  http.setTimeout(HTTP_TIMEOUT_MS);
  http.setReuse(false);
  String url = String("https://") + RENDER_HOST + "/api/devices/poll-lights";
  Serial.println(url);
  if (!http.begin(secureClient, url)) {
    Serial.println("begin FAIL");
    return false;
  }
  http.addHeader("X-API-Key", LIGHTS_API_KEY);
  int code = http.GET();
  String body = http.getString();
  http.end();
  Serial.printf("HTTP %d\n", code);
  if (code < 200 || code >= 300) {
    Serial.println(body.substring(0, 160));
    return false;
  }
  bool changed = false;
  for (int i = 0; i < 6; i++) {
    bool on = false;
    if (parseLedState(body, i, on) && ledOn[i] != on) {
      ledOn[i] = on;
      changed = true;
      Serial.printf("LED%d=%s\n", i + 1, on ? "ON" : "OFF");
    }
  }
  applyOutputs();
  if (!changed) Serial.println("sync OK");
  return true;
}

void setup() {
  WRITE_PERI_REG(RTC_CNTL_BROWN_OUT_REG, 0);
  Serial.begin(115200);
  delay(1200);
  Serial.println("=== ESP-B ONLINE Render ===");
  for (int i = 0; i < 6; i++) pinMode(LED_PINS[i], OUTPUT);
  pinMode(RELAY_PIN, OUTPUT);
  allOutputsOff();
  // self-test
  for (int i = 0; i < 6; i++) {
    allOutputsOff();
    digitalWrite(LED_PINS[i], HIGH);
    delay(180);
  }
  allOutputsOff();
  secureClient.setInsecure();
  connectWifi();
  for (int a = 1; a <= 10; a++) {
    Serial.printf("poll %d/10\n", a);
    if (pollLights()) break;
    delay(5000);
  }
  lastPollMs = millis();
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    allOutputsOff();
    connectWifi();
    return;
  }
  if (millis() - lastPollMs >= POLL_MS) {
    lastPollMs = millis();
    pollLights();
  }
}
