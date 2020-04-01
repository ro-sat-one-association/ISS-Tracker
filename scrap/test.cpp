#include <iostream>
using namespace std;

float initUnrollAngle = 0;
float heading = 0;
float roll = 0;

void alignAzimuth(float x, float y){}
void alignElevation(float x, float y){}
float deltaAzimuth(float x, float y){return 10.0f;}
float deltaElevatie(float x, float y){return 10.0f;}
void stopElevation(){}
void stopAzimuth(){}


int main(){
    int unroll_state;
    cin>>unroll_state;
    
    switch (unroll_state) {
      case 0: {
          cout<<"case 0";
          stopElevation();
          stopAzimuth();
        } break;
      case 1: { //A0
            cout<<"case 1";
          float delta = deltaAzimuth(initUnrollAngle, heading + 30.0f);
          if (delta > 5.0f) {
            alignAzimuth(heading + 30.0f, heading);
            stopElevation();
          } else {
            stopAzimuth();
            unroll_state = 0;
          }
          // Serial.println("A0");
        } break;

      case 2: { //A1
            cout<<"case 2";
          float delta = deltaAzimuth(initUnrollAngle, heading - 30.0f);
          if (delta > 5.0f) {
            alignAzimuth(heading - 30.0f, heading);
            stopElevation();
          } else {
            stopAzimuth();
            unroll_state = 0;
          }
          // Serial.println("A1");

        } break;
      case 3: {
          cout<<"case 3";
          float delta = deltaElevatie(initUnrollAngle, roll + 10.0f);
          if (delta > 5.0f) {
            alignElevation(roll + 10.0f, roll);
            stopAzimuth();
          } else {
            stopElevation();
            unroll_state = 0;
          }
          // Serial.println("E0");
        } break;
      case 4: {
          cout<<"case 4";
          float delta = deltaElevatie(initUnrollAngle, roll - 10.0f);
          if (delta > 5.0f) {
            alignElevation(roll - 10.0f, roll);
            stopAzimuth();
          } else {
            stopElevation();
            unroll_state = 0;
          }
          //Serial.println("E1");
        } break;
    }

    return 0;
        
}