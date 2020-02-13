#define ELE_A_PIN 4
#define ELE_B_PIN 9

#define AZI_A_PIN 7
#define AZI_B_PIN 8

#define AZI_PWM 5
#define ELE_PWM 6

#define ELE_EN A0
#define AZI_EN  A1

#define CS_THRESHOLD 15
unsigned char s;


bool sens; 

void setup(){
  sens = false;
  Serial.begin(9600);
  s = 200;
  pinMode(ELE_EN, OUTPUT);
  pinMode(AZI_EN,  OUTPUT);

  pinMode(ELE_A_PIN, OUTPUT);
  pinMode(ELE_B_PIN, OUTPUT);

  pinMode(AZI_A_PIN, OUTPUT);
  pinMode(AZI_B_PIN, OUTPUT);

  pinMode(AZI_PWM, OUTPUT);
  pinMode(ELE_PWM, OUTPUT);


}


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

void moveElevation(bool sens, int putere)
{
  if (sens) {
    digitalWrite(ELE_A_PIN, HIGH);
    digitalWrite(ELE_B_PIN, LOW);
  } else {
    digitalWrite(ELE_A_PIN, LOW);
    digitalWrite(ELE_B_PIN, HIGH);
  }
    analogWrite(ELE_PWM, putere);
}


void loop(){
 // s+=1;
 //s = 200;
  if(Serial.available()){
    char c = Serial.read();
    if(c == '1') sens = true;
    if(c == '0') sens = false;
    if(c == '2') s = 0;
    if(c == '3') s += 50;
  }
  //if(s<200 || s > 240) s = 200;
	digitalWrite(AZI_EN, HIGH);
  digitalWrite(ELE_EN, HIGH);
	// moveAzimuth(sens, s);
  moveElevation(sens,s);

}
