package com.gc.isstrackercompass

import android.Manifest
import android.annotation.SuppressLint
import android.content.Context
import android.content.pm.PackageManager
import android.hardware.Sensor
import android.hardware.SensorEvent
import android.hardware.SensorEventListener
import android.hardware.SensorManager
import android.location.Location
import android.os.Build
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.util.Log
import android.view.Menu
import android.view.MenuItem
import android.view.View
import android.view.WindowManager
import android.widget.EditText
import android.widget.TextView
import androidx.annotation.RequiresApi
import androidx.appcompat.app.AppCompatActivity
import androidx.core.app.ActivityCompat
import com.gc.isstrackercompass.R
import com.google.android.material.floatingactionbutton.FloatingActionButton
import com.google.android.material.snackbar.Snackbar
import java.lang.Math.round
import com.github.nkzawa.socketio.client.IO;
import com.github.nkzawa.socketio.client.Socket;
import com.google.android.gms.location.FusedLocationProviderClient
import com.google.android.gms.location.LocationServices

class MainActivity : AppCompatActivity(), SensorEventListener {

    private lateinit var sensorManager: SensorManager
    private val accelerometerReading = FloatArray(3)
    private val magnetometerReading = FloatArray(3)
    private val mOrientationAngles = FloatArray(3)
    private val rotationMatrix = FloatArray(9)
    private val orientationAngles = FloatArray(3)
    private lateinit var fusedLocationClient: FusedLocationProviderClient

    private val averageValues = 5
    private var azimuthArray = FloatArray(averageValues)
    private var azimuthCounter : Int = 0

    private var aziS = ""
    private var eleS = ""
    private var latS = ""
    private var lonS = ""
    private var altS = ""

    private var socket = IO.socket("http://10.8.0.5:3001")

    lateinit var mainHandler: Handler

    private val sendDataTask = object : Runnable {
        override fun run() {
            sendData()
            mainHandler.postDelayed(this, 100)
        }
    }

    fun sendSocket(msg : String) {
        socket.emit("sensordata", msg)
    }

    fun reconnectSocket(){
        socket.disconnect()
        var ipView = findViewById(R.id.ipView) as EditText
        socket = IO.socket("http://" + ipView.text)
        socket.connect()
            .on(Socket.EVENT_CONNECT) { Log.d("DEBUG","connected") }
            .on(Socket.EVENT_DISCONNECT) { Log.d("DEBUG","disconnected") }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        window.addFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON)

        socket.connect()
            .on(Socket.EVENT_CONNECT) { Log.d("DEBUG","connected")}
            .on(Socket.EVENT_DISCONNECT) { Log.d("DEBUG","disconnected") }

        Log.d("DEBUG","Merge logul")
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)
        setSupportActionBar(findViewById(R.id.toolbar))

        findViewById<FloatingActionButton>(R.id.fab).setOnClickListener {
                view -> Snackbar.make(view, "Încerc să mă reconectez", Snackbar.LENGTH_SHORT).setAction("Action", null).show()
                reconnectSocket()
        }
        sensorManager = getSystemService(Context.SENSOR_SERVICE) as SensorManager
        fusedLocationClient = LocationServices.getFusedLocationProviderClient(this)
        mainHandler = Handler(Looper.getMainLooper())
    }

    override fun onCreateOptionsMenu(menu: Menu): Boolean {
        // Inflate the menu; this adds items to the action bar if it is present.
        menuInflater.inflate(R.menu.menu_main, menu)
        return true
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        return when (item.itemId) {
            R.id.action_settings -> true
            else -> super.onOptionsItemSelected(item)
        }
    }

    @RequiresApi(Build.VERSION_CODES.KITKAT)
    override fun onResume() {
        super.onResume()

        // Get updates from the accelerometer and magnetometer at a constant rate.
        // To make batch operations more efficient and reduce power consumption,
        // provide support for delaying updates to the application.
        //
        // In this example, the sensor reporting delay is small enough such that
        // the application receives an update before the system checks the sensor
        // readings again.
        sensorManager.getDefaultSensor(Sensor.TYPE_ACCELEROMETER)?.also { accelerometer ->
            sensorManager.registerListener(
                    this,
                    accelerometer,
                    SensorManager.SENSOR_DELAY_GAME
            )
        }
        sensorManager.getDefaultSensor(Sensor.TYPE_MAGNETIC_FIELD)?.also { magneticField ->
            sensorManager.registerListener(
                    this,
                    magneticField,
                    SensorManager.SENSOR_DELAY_GAME
            )
        }

        mainHandler.post(sendDataTask)
    }

    override fun onAccuracyChanged(sensor: Sensor, accuracy: Int) {
        if(sensor.type == Sensor.TYPE_MAGNETIC_FIELD){
            Log.d("DEBUG","S-a schimbat precizia busolei:")
            Log.d("DEBUG", accuracy.toString())
            val accuracyView = findViewById<View>(R.id.accuracyView) as TextView
            when(accuracy){
                0 -> {
                    accuracyView.setText("Precizie zero")
                }

                1 -> {
                    accuracyView.setText("Precizie mică")
                }

                2 -> {
                    accuracyView.setText("Precizie medie")
                }

                3 -> {
                    accuracyView.setText("Precizie mare")
                }
            }
        }
    }

    // the device's accelerometer and magnetometer.
    fun updateOrientationAngles() {
        // Update rotation matrix, which is needed to update orientation angles.
        SensorManager.getRotationMatrix(
                rotationMatrix,
                null,
                accelerometerReading,
                magnetometerReading
        )

        // "mRotationMatrix" now has up-to-date information.

        SensorManager.getOrientation(rotationMatrix, mOrientationAngles)

        // "mOrientationAngles" now has up-to-date information.
    }

    @SuppressLint("MissingPermission")
    override fun onSensorChanged(event: SensorEvent) {
        val azimuthView =  findViewById<View>(R.id.azimuthView) as TextView
        val elevationView =  findViewById<View>(R.id.elevationView) as TextView

        if (event.sensor.type == Sensor.TYPE_ACCELEROMETER) {
            System.arraycopy(event.values, 0, accelerometerReading, 0, accelerometerReading.size)

            //val elevationView =  findViewById<View>(R.id.elevationView) as TextView
            var ele = round(mOrientationAngles[1] * 180 / Math.PI * 100)/100.0f * -1 //*-1 pt a fi cu ecranul in sus
            eleS = ele.toString()
            elevationView.text = eleS

        } else if (event.sensor.type == Sensor.TYPE_MAGNETIC_FIELD) {
            System.arraycopy(event.values, 0, magnetometerReading, 0, magnetometerReading.size)

            // azimuthView = findViewById<View>(R.id.azimuthView) as TextView
            var azi = round(mOrientationAngles[0] * 180 / Math.PI * 100)/100.0f
            if (azi < 0) azi += 360
            azimuthArray[azimuthCounter] = azi
            azimuthCounter += 1

            if(azimuthCounter == averageValues){
                aziS = (round(azimuthArray.average() * 100)/100.0f).toString()
                azimuthView.text = aziS
                azimuthCounter = 0
            }
        }

        fusedLocationClient.lastLocation
            .addOnSuccessListener { location : Location? ->
               latS = location?.latitude.toString()
               lonS = location?.longitude.toString()
               altS = location?.altitude.toString()
            }

        updateOrientationAngles()
    }

    private fun sendData(){
        sendSocket("$aziS $eleS") //+ " " + latS + " " + lonS + " " + altS)
    }
}