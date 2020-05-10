#include <Wire.h>
#include "ADXL345.h"
#include "HMC5883L_Simple.h"
#include "DualVNH5019MotorShield.h"
#include <EEPROM.h>
DualVNH5019MotorShield md;
HMC5883L_Simple Compass;
ADXL345 accelerometer;

#define TOLERANTA_ELEVATIE 1.0f //in grade
#define TOLERANTA_AZIMUTH  2.0f
#define SENS_0_E 0             //pe astea le inversezi dupa teste, daca e nevoie
#define SENS_1_E 1
#define SENS_0_A 0             //pe astea le inversezi dupa teste, daca e nevoie
#define SENS_1_A 1

#define ELE_A_PIN 4
#define ELE_B_PIN 3

#define AZI_A_PIN 7
#define AZI_B_PIN 8

#define AZI_PWM 9
#define ELE_PWM 10

#define ELE_EN 6
#define AZI_EN  12

#define MIN_E 50 //puterea minima pwm  
#define MIN_A 40

#define K_E 5 //cu cate grade inainte sa incetinesc miscarea
#define K_A 10

#define MAX_E 150
#define MAX_A 150


int min_x, max_x;
int min_y, max_y;

long long lastReadTime;
long long lastTime;

float initUnrollAngle;

float pitch;
float heading;

float azimuth;
float elevation;

char unroll_state;
bool debug;

float x_off;
float y_off;

void initCompass() {
  // The declination for your area can be obtained from http://www.magnetic-declination.com/
  // Piatra-Neamt, 6°  15' EST
  Compass.SetDeclination(6, 15, 'E');

  Compass.SetSamplingMode(COMPASS_CONTINUOUS);
  Compass.SetScale(COMPASS_SCALE_810);
  Compass.SetOrientation(COMPASS_HORIZONTAL_X_NORTH);
}

void setup()
{
  Wire.begin();

  Serial.begin(9600);
  Serial.setTimeout(50);

  md.init();

  min_x = 10000;
  min_y = 10000;
  max_x = -10000;
  max_y = -10000;

  azimuth = 0.0f;
  elevation = 0.0f;

  lastReadTime = 0;
  unroll_state = -1;

  debug = false;
  lastTime = 0;

  pinMode(ELE_EN, OUTPUT);
  pinMode(AZI_EN,  OUTPUT);

  pinMode(ELE_A_PIN, OUTPUT);
  pinMode(ELE_B_PIN, OUTPUT);

  pinMode(AZI_A_PIN, OUTPUT);
  pinMode(AZI_B_PIN, OUTPUT);

  pinMode(AZI_PWM, OUTPUT);
  pinMode(ELE_PWM, OUTPUT);

  digitalWrite(AZI_EN,  HIGH);
  digitalWrite(ELE_EN,  HIGH);
 
  calibrateCompass(x_off, y_off);
  Serial.println("#### COMPASS OFFSET ####");
  Serial.print(x_off);
  Serial.print(" ");
  Serial.println(y_off);

  if(!accelerometer.begin()){Serial.println("Nu merge AXL"); while(true){}}
  accelerometer.setRange(ADXL345_RANGE_16G);
  initCompass();
}


void moveAzimuth(bool sens, int putere)
{
  if (sens) {
    md.setM2Speed(putere * 80 / 51);
  } else {
    md.setM2Speed(-1 * putere * 80 / 51);
  }
}

void moveElevation(bool sens, int putere)
{
  if (sens) {
    md.setM1Speed(putere * 80 / 51);
  } else {
    md.setM1Speed(-1 * putere * 80 / 51);
  }
}

void stopAzimuth()
{
  digitalWrite(AZI_A_PIN, LOW);
  digitalWrite(AZI_B_PIN, LOW);
}

void stopElevation()
{
  digitalWrite(ELE_A_PIN, LOW);
  digitalWrite(ELE_B_PIN, LOW);
}


int checkSum(String t) {
  unsigned int s = 0;
  for (int i = 0; t[i] != '\0'; ++i) {
    s += (int)t[i];
  }
  return s % 10;
}

bool validPackage(String textPacket) { //SPAGHETTI CODE, stiu
  bool et = false;
  String C = "\0";
  String P = "\0";
  if (textPacket[0] == '!') {
    P += '!';
    int i = 1;
    for (i; textPacket[i] != '!'; ++i) {
      if (textPacket[i] == '\0') return false;
      P += textPacket[i];
      if (textPacket[i] == '&') et = true;
    }
    if (!et) return false;
    ++i;
    if (textPacket[i] == '\0') return false;
    for (i; textPacket[i] != '\0'; ++i) {
      C += textPacket[i];
    }
    if (debug) {
      Serial.print("ctoint ");
      Serial.println(C.toInt());
      Serial.print("csum ");
      Serial.println(checkSum(P));
    }
    if (C.toInt() == checkSum(P) && et) return true;
  }
  return false;
}


void readData(float &azi, float &ele)
{
  String textPacket = "\0";
  String A = "\0";
  String E = "\0";

  if (Serial.available()) {
    textPacket =  Serial.readString();
    textPacket.trim();
    if (textPacket == "D") {
      debug = !debug;
      return;
    }

    if (textPacket == "A0") {
      unroll_state = 1;
      initUnrollAngle = heading;
      return;
    }
    if (textPacket == "A1") {
      unroll_state = 2;
      initUnrollAngle = heading;
      return;
    }

    if (textPacket[0] == 'A' && textPacket[1] == 'Z') {
      unroll_state = -1;  //AZ239.0 EL3.0 UP000 XXX DN000 XXX
      int i = 2;
      for (i; textPacket[i] != ' ' ; ++i) {
        A += textPacket[i];
      }
      i += 3;
      for (i; textPacket[i] != ' '; ++i) {
        E += textPacket[i];
      }
      azi = A.toFloat();
      ele = E.toFloat();
      if (debug) {
        Serial.print("Easycomm! Azimut:");
        Serial.print(azi);
        Serial.print(" Elevatie:");
        Serial.println(ele);
      }
      return;
    }

    if (validPackage(textPacket)) {
      unroll_state = -1;
      int i = 1;
      for (i; textPacket[i] != '&' ; ++i) {
        A += textPacket[i];
      }
      i += 1;
      for (i; textPacket[i] != '\0'; ++i) {
        E += textPacket[i];
      }
      azi = A.toFloat();
      ele = E.toFloat();
      if (debug) {
        Serial.print("Pachet valid! Azimut:");
        Serial.print(azi);
        Serial.print(" Elevatie:");
        Serial.println(ele);
      }
    } else {
      if (debug) {
        Serial.println("Pachet nevalid");
      }
      return;
    }
  } else {
    return;
  }
}

float deltaAzimuth(float t, float h) {
  if (fabs(t - h) < 180)
    return fabs(t - h);
  else
    return 360 - fabs(t - h);
}

float deltaElevatie(float t, float r) {
  if (r < 0) r += 360.0f;
  if (t < 0) t += 360.0f;
  return deltaAzimuth(t, r);
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

bool sensElevatie(int t, int r) {
  if (r < 0) r += 360;
  if (t < 0) t += 360;
  if (fabs(t - r) < 180) {
    if (r > t)
      return SENS_0_E;
    else
      return SENS_1_E;
  } else {
    if (r > t)
      return SENS_1_E;
    else
      return SENS_0_E;
  }
}

int putereElevatie(int d) {
  if (d > K_E) {
    return MAX_E;
  } else {
    int v = MIN_E + (MAX_E - MIN_E) * d / K_E;
    return v;
  }
}

int putereAzimuth(int d) {
  if (d > K_A) {
    return MAX_A;
  } else {
    int v = MIN_A + (float)(MAX_A - MIN_A) * (float)(d) / (float)(K_A);
    return v;
  }
}

template <typename type>
type sign(type value) {
  return type((value > 0) - (value < 0));
}


void alignAzimuth(float t, float h) {
  float delta = deltaAzimuth(t, h);
  /*Serial.print(delta);
    Serial.print('\t');
    Serial.println(TOLERANTA_AZIMUTH);*/

  if (delta > TOLERANTA_AZIMUTH) {
    //digitalWrite(AZI_EN, HIGH);
    moveAzimuth(sensAzimuth(t, h), putereAzimuth(delta));

  } else {
   // digitalWrite(AZI_EN, LOW);
    stopAzimuth();
  }
}

void alignElevation(float t, float r) {
  float delta = deltaElevatie(t, r);
  /*Serial.print(delta);
    Serial.print('\t');
    Serial.println(TOLERANTA_ELEVATIE);*/
  if (delta > TOLERANTA_ELEVATIE) {
    //digitalWrite(ELE_EN, HIGH);
    moveElevation(sensElevatie(t, r), putereElevatie(delta));
  } else {
    stopElevation();
    //digitalWrite(ELE_EN, LOW);
  }
}

#define PRINT_DELAY 500

float normalizeAngle(float a){
  if (a < 0) return a + 360.0f;
  if (a > 360.0f) return a - 360.0f;
  return a;
}

void getCompass(float &x_off, float &y_off) {
  heading = Compass.GetHeadingDegrees(x_off, y_off);
}

void calibrateCompass(float &x_off, float &y_off){ 
  EEPROM.get(0, x_off);
  EEPROM.get(sizeof(float), y_off);
}

void writeCompassOffsets(float x_off, float y_off){
  EEPROM.put(0, x_off);
  EEPROM.put(sizeof(float), y_off);
}

bool checkI2C(uint8_t dev){
  Wire.beginTransmission (dev);
  if (Wire.endTransmission() == 0) {
      //Serial.print (F("I2C return truedevice found at 0x"));
      //Serial.println (dev, HEX);
      return true;
  }
  return false;
}

void getPitch() {
    Vector norm = accelerometer.readNormalize();
    Vector filtered = accelerometer.lowPassFilter(norm, 0.5);
    pitch = -(atan2(filtered.XAxis, sqrt(filtered.YAxis*filtered.YAxis + filtered.ZAxis*filtered.ZAxis))*180.0)/M_PI;
}

void loop()
{
  readData(azimuth, elevation);


  getPitch();
  getCompass(x_off, y_off);

  if (millis() - lastTime > PRINT_DELAY) {
    Serial.print(heading);
    Serial.print(" ");
    Serial.println(pitch);
    lastTime = millis();
    if(debug){
      Serial.print("Heading: ");
      Serial.println(heading);

      Serial.print("Pitch:");
      Serial.println(pitch);
      Serial.println("-------------");
    }
  }

  if(unroll_state != -1){
    if(unroll_state == 1){ //A0
      float d = deltaAzimuth(initUnrollAngle, heading);
      float k  = normalizeAngle(initUnrollAngle - 1.0f);
      float dk = deltaAzimuth(k, heading);

      int x, y;

      Compass.GetRaw(x,y);

      if(x < min_x) min_x = x;
      if(x > max_x) max_x = x;
      if(y < min_y) min_y = y;
      if(y > max_y) max_y = y;
      
      if(dk > d){
        moveAzimuth(sensAzimuth(normalizeAngle(heading + 1.0f), heading), 255);
      } else {
        alignAzimuth(normalizeAngle(k - 2.0f), heading);
        if (deltaAzimuth(normalizeAngle(k - 2.0f), heading) < TOLERANTA_AZIMUTH) { // am terminat tura
          unroll_state=-1;

          float x_o = (float)(min_x + max_x)/2.0f;
          float y_o = (float)(min_y + max_y)/2.0f;

          writeCompassOffsets(x_o, y_o);
          calibrateCompass(x_off, y_off);

           min_x = -10000;
           min_y = -10000;
           max_x =  10000;
           max_y =  10000;
        }
      }
    }

    if(unroll_state == 2){ //A1
      float d = deltaAzimuth(initUnrollAngle, heading);
      float k  = normalizeAngle(initUnrollAngle + 1.0f);
      float dk = deltaAzimuth(k, heading);

      int x, y;

      Compass.GetRaw(x,y);

      if(x < min_x) min_x = x;
      if(x > max_x) max_x = x;
      if(y < min_y) min_y = y;
      if(y > max_y) max_y = y;
      
      if(dk > d){
        moveAzimuth(sensAzimuth(normalizeAngle(heading - 1.0f), heading), 255);
      } else {
        alignAzimuth(normalizeAngle(k + 2.0f), heading);
        if (deltaAzimuth(normalizeAngle(k + 2.0f), heading) < TOLERANTA_AZIMUTH) {
          unroll_state=-1;
          float x_o = (float)(min_x + max_x)/2.0f;
          float y_o = (float)(min_y + max_y)/2.0f;

          writeCompassOffsets(x_o, y_o);
          calibrateCompass(x_off, y_off);

           min_x = -10000;
           min_y = -10000;
           max_x =  10000;
           max_y =  10000;
        }
      }
    }

  } else {
      alignAzimuth(azimuth, heading);
      alignElevation(elevation, pitch);
  }
}
