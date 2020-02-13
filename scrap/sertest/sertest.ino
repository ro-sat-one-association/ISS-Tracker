void setup(){
  Serial.begin(9600);
}

void loop(){
  // Serial.println("Nu primesc nimic...");

   String textPacket = "\0";
   
   if(Serial.available()) {
    textPacket =  Serial.readString();
    Serial.println(textPacket);
   }

   delay(100);
}
