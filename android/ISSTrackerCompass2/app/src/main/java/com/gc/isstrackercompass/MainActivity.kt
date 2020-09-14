package com.gc.isstrackercompass

import android.annotation.SuppressLint
import android.content.Context
import android.hardware.Sensor
import android.hardware.SensorEvent
import android.hardware.SensorEventListener
import android.hardware.SensorManager
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
import android.widget.SeekBar
import android.widget.TextView
import androidx.annotation.RequiresApi
import androidx.appcompat.app.AppCompatActivity
import com.github.nkzawa.socketio.client.IO
import com.github.nkzawa.socketio.client.Socket
import com.google.android.gms.location.FusedLocationProviderClient
import com.google.android.gms.location.LocationServices
import com.google.android.material.floatingactionbutton.FloatingActionButton
import com.google.android.material.snackbar.Snackbar
import java.lang.Math.round
import kotlin.math.sign

class MainActivity : AppCompatActivity(), SensorEventListener {

    private lateinit var sensorManager: SensorManager
    private val mGravity = FloatArray(3)
    private val mGeomagnetic = FloatArray(3)
    private val mOrientationAngles = FloatArray(3)
    private val rotationMatrix = FloatArray(9)
    private val P = FloatArray(9)
    private val I = FloatArray(9)
    private lateinit var fusedLocationClient: FusedLocationProviderClient

    private var azimuth = 0f
    private var elevation = 0f

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
            mainHandler.postDelayed(this, 50)
        }
    }

    fun sendSocket(msg : String) {
        socket.emit("sensordata", msg)
    }

    fun reconnectSocket(){
        socket.disconnect()
        var ipView = findViewById<EditText>(R.id.ipView)
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
                mGravity,
                mGeomagnetic
        )

        // "mRotationMatrix" now has up-to-date information.

        SensorManager.getOrientation(rotationMatrix, mOrientationAngles)

        // "mOrientationAngles" now has up-to-date information.
    }

    @SuppressLint("MissingPermission")
    override fun onSensorChanged(event: SensorEvent) {
        val azimuthView =  findViewById<View>(R.id.azimuthView) as TextView
        val elevationView =  findViewById<View>(R.id.elevationView) as TextView
        val alphaView = findViewById<View>(R.id.alphaView) as TextView
        val alphaSlider = findViewById<View>(R.id.alphaSlider) as SeekBar

        var alpha = alphaSlider.progress.toFloat() / 100.0f
        alphaView.text = alpha.toString()

       // val alpha = 0.85f //0.97
        synchronized(this) {
            if (event.sensor.type == Sensor.TYPE_ACCELEROMETER) {
                mGravity[0] = alpha * mGravity[0] + (1 - alpha) * event.values[0]
                mGravity[1] = alpha * mGravity[1] + (1 - alpha) * event.values[1]
                mGravity[2] = alpha * mGravity[2] + (1 - alpha) * event.values[2]

                // mGravity = event.values;

                // Log.e(TAG, Float.toString(mGravity[0]));
            }
            if (event.sensor.type == Sensor.TYPE_MAGNETIC_FIELD) {
                // mGeomagnetic = event.values;
                mGeomagnetic[0] = alpha * mGeomagnetic[0] + (1 - alpha) * event.values[0]
                mGeomagnetic[1] = alpha * mGeomagnetic[1] + (1 - alpha) * event.values[1]
                mGeomagnetic[2] = alpha * mGeomagnetic[2] + (1 - alpha) * event.values[2]
                // Log.e(TAG, Float.toString(event.values[0]));
            }
            val success = SensorManager.getRotationMatrix(P, I, mGravity, mGeomagnetic)
            if (success) {
                updateOrientationAngles()
                val orientation = FloatArray(3)
                SensorManager.getOrientation(P, orientation)
                // Log.d(TAG, "azimuth (rad): " + azimuth);
                azimuth = Math.toDegrees(orientation[0].toDouble()).toFloat() // orientation
                azimuth = (azimuth + 360) % 360
                aziS = round(azimuth).toString()
                azimuthView.text = aziS

                elevation = -Math.toDegrees(orientation[1].toDouble()).toFloat()

                val inclination = round(
                    Math.toDegrees(
                        Math.acos(
                            rotationMatrix[8].toDouble()
                        )
                    )
                ).toInt()

                //if(inclination > 90) elevation = sign(elevation) * 90

                elevation = sign(elevation) * inclination

                eleS = round(elevation).toString()
                elevationView.text = eleS
                // Log.d(TAG, "azimuth (deg): " + azimuth
            }
        }
    }

    private fun sendData(){
        sendSocket("$aziS $eleS") //+ " " + latS + " " + lonS + " " + altS)
    }
}