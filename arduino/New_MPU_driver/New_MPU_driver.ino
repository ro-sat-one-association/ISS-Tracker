#include <math.h>
#include <SPI.h>
#include <Wire.h>
#include <EEPROM.h>
#include "DualVNH5019MotorShield.h"
#include "HMC5883L_Simple.h"
#include "I2Cdev.h"
#include "MPU6050_6Axis_MotionApps_V6_12.h"
#include "Wire.h"

MPU6050 mpu;
HMC5883L_Simple Compass;
DualVNH5019MotorShield md;

bool dmpReady = false;  // set true if DMP init was successful
uint8_t mpuIntStatus;   // holds actual interrupt status byte from MPU
uint8_t devStatus;      // return status after each device operation (0 = success, !0 = error)
uint16_t packetSize;    // expected DMP packet size (default is 42 bytes)
uint16_t fifoCount;     // count of all bytes currently in FIFO
uint8_t fifoBuffer[64]; // FIFO storage buffer
Quaternion q;           // [w, x, y, z]         quaternion container
VectorInt16 aa;         // [x, y, z]            accel sensor measurements
VectorInt16 gy;         // [x, y, z]            gyro sensor measurements
VectorInt16 aaReal;     // [x, y, z]            gravity-free accel sensor measurements
VectorInt16 aaWorld;    // [x, y, z]            world-frame accel sensor measurements
VectorFloat gravity;    // [x, y, z]            gravity vector
float euler[3];         // [psi, theta, phi]    Euler angle container
float ypr[3];           // [yaw, pitch, roll]   yaw/pitch/roll container and gravity vector


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


int min_x, max_x;
int min_y, max_y;

long long lastReadTime;
long long lastTime;

float initUnrollAngle;

float roll;
float heading;

float azimuth;
float elevation;

char unroll_state;
bool debug;

float x_off;
float y_off;

void initMPU(){
 mpu.initialize();

  Serial.println(F("Testing device connections..."));
  Serial.println(mpu.testConnection() ? F("MPU6050 connection successful") : F("MPU6050 connection failed"));

  // load and configure the DMP
  Serial.println(F("Initializing DMP..."));
  devStatus = mpu.dmpInitialize();

  // supply your own gyro offsets here, scaled for min sensitivity
 /* mpu.setXGyroOffset(51);
  mpu.setYGyroOffset(8);
  mpu.setZGyroOffset(21);
  mpu.setXAccelOffset(1150);
  mpu.setYAccelOffset(-50);
  mpu.setZAccelOffset(1060); */
  // make sure it worked (returns 0 if so)
  if (devStatus == 0) {
    // Calibration Time: generate offsets and calibrate our MPU6050
   // mpu.CalibrateAccel(6);
   // mpu.CalibrateGyro(6);
   // Serial.println();
   // mpu.PrintActiveOffsets();
    // turn on the DMP, now that it's ready
    Serial.println(F("Enabling DMP..."));
    mpu.setDMPEnabled(true);
    Serial.println(F("DMP ready! Waiting for first interrupt..."));
    dmpReady = true;
    packetSize = mpu.dmpGetFIFOPacketSize();
  } else {
    Serial.print(F("DMP Initialization failed (code "));
    Serial.print(devStatus);
    Serial.println(F(")"));
  }
}

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
  Wire.setClock(400000); 

  Serial.begin(115200);
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
 
  Serial.println("#### COMPASS OFFSET ####");
  Serial.print(x_off);
  Serial.print(" ");
  Serial.println(y_off);

  initMPU();
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

#define MIN_E 50 //puterea minima pwm  
#define MIN_A 40

#define K_E 5 //cu cate grade inainte sa incetinesc miscarea
#define K_A 10

#define MAX_E 255
#define MAX_A 255

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

#define PRINT_DELAY 100

float normalizeAngle(float a){
  if (a < 0) return a + 360.0f;
  if (a > 360.0f) return a - 360.0f;
  return a;
}

void getCompass(float &x_off, float &y_off) {
  heading = Compass.GetHeadingDegrees(x_off, y_off);
}

void calibrateCompass(float &x_off, float &y_off){ //TO-DO
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

void loop()
{
  readData(azimuth, elevation);

  if (mpu.dmpGetCurrentFIFOPacket(fifoBuffer)) { 
    mpu.dmpGetQuaternion(&q, fifoBuffer);
    mpu.dmpGetGravity(&gravity, &q);
    mpu.dmpGetYawPitchRoll(ypr, &q, &gravity);
    roll = ypr[2] * (180 / M_PI);
  }

  getCompass(x_off, y_off);

  if (millis() - lastTime > PRINT_DELAY) {
    lastTime = millis();
    String s = String(heading);
    s += "&" + String(roll) + "!";
    s += String(checkSum(s)); 
    Serial.println(s);
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
      alignElevation(elevation, roll);
  }
}
