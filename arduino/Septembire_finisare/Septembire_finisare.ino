#include "DualVNH5019MotorShield.h"
DualVNH5019MotorShield md;

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
#define K_A 60

#define MAX_E 255
#define MAX_A 255

#define UNROLL_SAMPLE 5000 //calc viteza unghiulara in X ms

long long lastReadTime;
long long lastTime;

float initUnrollAngle;
long long initUnrollTime;
unsigned long deltaT;

float pitch;
float heading;

float azimuth;
float elevation;

char unroll_state;
bool debug;
bool calibrate;

bool primitTelefon;

void setup()
{
  primitTelefon = false;
  

  Serial.begin(115200);
  Serial.setTimeout(50);

  md.init();

  deltaT = 0;

  azimuth = 0.0f;
  elevation = 0.0f;

  lastReadTime = 0;
  unroll_state = -1;

  debug = false;
  calibrate = true; 
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
}


void moveAzimuth(bool sens, int putere)
{
  digitalWrite(AZI_EN, HIGH);
  if (sens) {
    md.setM2Speed(putere * 80 / 51);
  } else {
    md.setM2Speed(-1 * putere * 80 / 51);
  }
}

void moveElevation(bool sens, int putere)
{
  digitalWrite(ELE_EN, HIGH);
  if (sens) {
    md.setM1Speed(putere * 80 / 51);
  } else {
    md.setM1Speed(-1 * putere * 80 / 51);
  }
}

void stopAzimuth()
{
  digitalWrite(AZI_EN, LOW);
  digitalWrite(AZI_A_PIN, HIGH);
  digitalWrite(AZI_B_PIN, HIGH);
}

void stopElevation()
{
  digitalWrite(ELE_EN, LOW);
  digitalWrite(ELE_A_PIN, HIGH);
  digitalWrite(ELE_B_PIN, HIGH);
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
      Serial.print("Debugul este acum: ");
      debug = !debug;
      Serial.println(debug);
      return;
    }

    if(textPacket == "C") {
      Serial.print("Calibrarea busolei este acum: ");
      calibrate = !calibrate;
      Serial.println(calibrate);
      return;
    }

    if (textPacket == "A0") {
      unroll_state = 1;
      initUnrollAngle = heading;
      initUnrollTime = millis();
      deltaT = 0;
      return;
    }
    if (textPacket == "A1") {
      unroll_state = 2;
      initUnrollAngle = heading;
      initUnrollTime = millis();
      deltaT = 0;
      return;
    }

    if (textPacket[0] == 'S' && textPacket[1] == 'N' && textPacket[2] == 'Z'){
      primitTelefon = true;
      int i = 4;
      for(i; (textPacket[i] != ' ' && textPacket[i] != '\0'); ++i){
        A += textPacket[i];
      }
      heading = A.toFloat();
      ++i;
      for(i; (textPacket[i] != ' ' && textPacket[i] != '\0'); ++i){
        E += textPacket[i];
      }
      pitch = E.toFloat();

      if(debug){
        Serial.print(A);
        Serial.print(' ');
        Serial.println(E);
      }
      
      return;
    }

    if (textPacket[0] == 'A' && textPacket[1] == 'Z') {
      unroll_state = -1;  //AZ239.0 EL3.0 UP000 XXX DN000 XXX
      int i = 2;
      for (i; textPacket[i] != ' ' && textPacket[i] != '\0' ; ++i) {
        A += textPacket[i];
      }
      i += 3;
      for (i; textPacket[i] != ' ' && textPacket[i] != '\0' ; ++i) {
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

#define PRINT_DELAY 100

float normalizeAngle(float a){
  if (a < 0) return a + 360.0f;
  if (a > 360.0f) return a - 360.0f;
  return a;
}

void routine()
{

  if (millis() - lastTime > PRINT_DELAY) {
    lastTime = millis();
    String s = String(heading);
    s += "&" + String(pitch) + "!";
    s += String(checkSum(s)); 
    Serial.println(s);
  }

  if(unroll_state != -1){
    if(unroll_state == 1){ //A0
      stopElevation();
      moveAzimuth(sensAzimuth(normalizeAngle(heading + 1.0f), heading), MAX_A);

      if(millis() - initUnrollTime > UNROLL_SAMPLE && deltaT == 0){
        float omega = deltaAzimuth(initUnrollAngle, heading) / (float)(millis() - initUnrollTime);
        deltaT  = 360.0f/omega - UNROLL_SAMPLE;
        //Serial.print("omega: "); Serial.print(omega); Serial.print(" deltaT: "); Serial.println(deltaT);
      }

      if (millis() - initUnrollTime > deltaT && deltaT != 0) { 
         unroll_state = -1;
      }
            
    }
    

    if(unroll_state == 2){ //A1
      stopElevation();
      moveAzimuth(sensAzimuth(normalizeAngle(heading - 1.0f), heading), MAX_A);

      if(millis() - initUnrollTime > UNROLL_SAMPLE && deltaT == 0){
        float omega = deltaAzimuth(initUnrollAngle, heading) / (float)(millis() - initUnrollTime);
        deltaT  = 360.0f/omega - UNROLL_SAMPLE;
        //Serial.print("omega: "); Serial.print(omega); Serial.print(" deltaT: "); Serial.println(deltaT);
      }

      if (millis() - initUnrollTime > deltaT && deltaT != 0) {
         unroll_state = -1;
      }
    }

  } else {
      alignAzimuth(azimuth, heading);
      alignElevation(elevation, pitch);
  }
}

void loop(){
  readData(azimuth, elevation);
  if(primitTelefon){
    routine();
  }
}
