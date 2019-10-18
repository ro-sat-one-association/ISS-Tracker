//#define M_PI 3.1415
#include "MPU9250.h"
#include <math.h>
// an MPU9250 object with the MPU-9250 sensor on I2C bus 0 with address 0x68
MPU9250 IMU(Wire,0x68);
int status;
int azimuth;
int elevation;

#define AzimuthPWML 3
#define AzimuthPWMR 5

#define ElevatiePWMR 6
#define ElevatiePWML 9

void startAzimuth(){
    analogWrite(AzimuthPWMR, 0);
    analogWrite(AzimuthPWML, 255);
    delay(100);
}

void moveAzimuth(bool sens, int putere){  
  if(sens){
    analogWrite(AzimuthPWMR, putere);
    analogWrite(AzimuthPWML, 0);
  } else {
    analogWrite(AzimuthPWMR, 0);
    analogWrite(AzimuthPWML, putere);
  }
}

void moveElevation(bool sens, int putere){
  if(sens){
    analogWrite(ElevatiePWMR, putere);
    analogWrite(ElevatiePWML, 0);
  } else {
    analogWrite(ElevatiePWMR, 0);
    analogWrite(ElevatiePWML, putere);
  }
}


void stopAzimuth(){ 
  digitalWrite(AzimuthPWMR, LOW);
  digitalWrite(AzimuthPWML, LOW);
  
}

void stopElevation(){
  digitalWrite(ElevatiePWMR, LOW);
  digitalWrite(ElevatiePWML, LOW);
  
}

void setup() {
  azimuth = 0;
  elevation = 0;

  Serial.begin(9600);

  pinMode(AzimuthPWMR, OUTPUT);
  pinMode(AzimuthPWML, OUTPUT);
  
  pinMode(ElevatiePWMR, OUTPUT);
  pinMode(ElevatiePWML, OUTPUT);

  
  while(!Serial) {}

  status = IMU.begin();
  if (status < 0) {
    Serial.println("IMU initialization unsuccessful");
    Serial.println("Check IMU wiring or try cycling power");
    Serial.print("Status: ");
    Serial.println(status);
    while(1) {}
  }

  startAzimuth();
}

void readData(int &azi, int &elev){
  if (Serial.available() > 0) {
     
    String inString = "";

    byte incomingByte = Serial.read(); 

    if(incomingByte == '<'){
      while(incomingByte != '>'){
          if(incomingByte != '<')
            inString += (char)incomingByte;
          if(incomingByte == ' '){
            azi = inString.toInt();
            inString = "";
          }
          incomingByte = Serial.read(); 
      }
      elev = inString.toInt();
    }
  }

  return;

}


void loop() {
  readData(azimuth, elevation);
  Serial.print(azimuth);
  Serial.print('\t');
  Serial.println(elevation);

  
  IMU.readSensor();
  float roll, pitch, yaw;


  float accelX = IMU.getAccelX_mss();
  float accelY = IMU.getAccelY_mss();
  float accelZ = IMU.getAccelZ_mss();
  float magReadX = IMU.getMagX_uT();
  float magReadY = IMU.getMagY_uT();
  float magReadZ = IMU.getMagZ_uT();

  pitch = 180 * atan2(accelX, sqrt(accelY*accelY + accelZ*accelZ))/PI;
  roll = 180 * atan2(accelY, sqrt(accelX*accelX + accelZ*accelZ))/PI;
  
  float mag_x = magReadX*cos(pitch) + magReadY*sin(roll)*sin(pitch) + magReadZ*cos(roll)*sin(pitch);
  float mag_y = magReadY * cos(roll) - magReadZ * sin(roll);
 
  /*Serial.print(IMU.getAccelX_mss(),6);
  Serial.print("\t");
  Serial.print(IMU.getAccelY_mss(),6);
  Serial.print("\t");
  Serial.print(IMU.getAccelZ_mss(),6);
  Serial.print("\t");
  Serial.print(IMU.getGyroX_rads(),6);
  Serial.print("\t");
  Serial.print(IMU.getGyroY_rads(),6);
  Serial.print("\t");
  Serial.print(IMU.getGyroZ_rads(),6);
  Serial.print("\t");
  Serial.print(IMU.getMagX_uT(),6);
  Serial.print("\t");
  Serial.print(IMU.getMagY_uT(),6);
  Serial.print("\t");
  Serial.print(IMU.getMagZ_uT(),6);
  Serial.print("\t");
  Serial.println(IMU.getTemperature_C(),6); */


  yaw = 180 * atan2(-mag_y,mag_x)/M_PI;

  //Serial.print(roll);
  //Serial.print('\t');
  //Serial.print(pitch);
  //Serial.println('\t');
  //Serial.println(yaw);

  stopAzimuth();
  stopElevation();
  moveElevation(false, 255);
  moveAzimuth(false, 100);
  
  delay(100);


}
