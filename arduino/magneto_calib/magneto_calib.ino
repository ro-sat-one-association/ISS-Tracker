#include <math.h>
#include <SPI.h>
#include <Wire.h>

#include "HMC5883L_Simple.h"
HMC5883L_Simple Compass;

#define AZI_A_PIN 7
#define AZI_B_PIN 8
#define AZI_PWM 5
#define AZI_EN  A1

#define SENS_0_A 0             //pe astea le inversezi dupa teste, daca e nevoie
#define SENS_1_A 1

#define TOLERANTA_AZIMUTH  2.0f

void moveAzimuth(bool sens, int putere)
{
  if (sens) {
    digitalWrite(AZI_A_PIN, HIGH);
    digitalWrite(AZI_B_PIN, LOW);
  } else {
    digitalWrite(AZI_A_PIN, LOW);
    digitalWrite(AZI_B_PIN, HIGH);
  }
  analogWrite(AZI_PWM, putere);
}

bool sensAzimuth(int t, int h) {
  if (fabs(t - h) < 180) {
    if (h > t)
      return SENS_0_A;
    else
      return SENS_1_A;
  } else {
    if (h > t)
      return SENS_1_A;
    else
      return SENS_0_A;
  }
}

float deltaAzimuth(float t, float h) {
  if (fabs(t - h) < 180)
    return fabs(t - h);
  else
    return 360 - fabs(t - h);
}

void stopAzimuth()
{
  digitalWrite(AZI_A_PIN, LOW);
  digitalWrite(AZI_A_PIN, LOW);
}

void alignAzimuth(float t, float h, int p) {
  float delta = deltaAzimuth(t, h);
  /*Serial.print(delta);
    Serial.print('\t');
    Serial.println(TOLERANTA_AZIMUTH);*/

  if (delta > TOLERANTA_AZIMUTH) {
    //digitalWrite(AZI_EN, HIGH);
    moveAzimuth(sensAzimuth(t, h), p);

  } else {
   // digitalWrite(AZI_EN, LOW);
    stopAzimuth();
  }
}


float lastTime;

void setup(){
    pinMode(AZI_EN,  OUTPUT);
    pinMode(AZI_A_PIN, OUTPUT);
    pinMode(AZI_B_PIN, OUTPUT);
    pinMode(AZI_PWM, OUTPUT);
    digitalWrite(AZI_EN,  HIGH);

    Compass.SetDeclination(6, 15, 'E');
    Compass.SetSamplingMode(COMPASS_CONTINUOUS);
    Compass.SetScale(COMPASS_SCALE_810);
    Compass.SetOrientation(COMPASS_HORIZONTAL_X_NORTH);

    lastTime = 0;
}

#define PRINT_DELAY 500

void loop(){
    float heading = Compass.GetHeadingDegrees();
    float azi = 0;
    if (Serial.available()) {
        String textPacket =  Serial.readString();
        textPacket.trim();
        azi =  textPacket.toFloat();
    }

    alignAzimuth(azi, heading, 50);
    
    if (millis() - lastTime > PRINT_DELAY) {
        lastTime = millis();
        Serial.println(heading);
    }
}