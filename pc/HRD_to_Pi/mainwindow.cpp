#include "mainwindow.h"
#include "ui_mainwindow.h"
#include <QString>
#include <QDebug>
#include <QSerialPort>
#include <QSerialPortInfo>
#include <QTime>
#include <QThread>
#include <QNetworkAccessManager>

MainWindow::MainWindow(QWidget *parent):
    QMainWindow(parent),
        ui(new Ui::MainWindow) {
          /*  QFont font = QApplication::font();
            font.setStyleStrategy(QFont::PreferQuality);
            QApplication::setFont(font);*/

            ui->setupUi(this);
            setWindowTitle("HRD to Wi-Fi Tracker");

        }

MainWindow::~MainWindow() {
    delete ui;
}

QSerialPort port;
QSerialPortInfo portInfo;
QString portN;
QString data_string;
QString ip;
QString phpstr = "/hrdtopi.php?data=";


bool searchPort(QString portName) {
    foreach (const QSerialPortInfo &info, QSerialPortInfo::availablePorts()) {
        qInfo() << "Name : " << info.portName();
        qInfo() << "Description : " << info.description();
        qInfo() << "Manufacturer: " << info.manufacturer();
        if(info.portName() == portName){
            portInfo = info;
            qInfo() << "Am gasit";
            return true;
        }
    }
    return false;

}

bool openPort(int baud) {
    port.close();
    port.setPort(portInfo);
    port.setBaudRate(baud);
    bool success = port.open(QIODevice::ReadWrite);
    qInfo()<<"Baud: "<<baud;
    qInfo()<<"Port state: "<<success;
    return success;
}

void MainWindow::sendData(QString data){
    QNetworkAccessManager *manager = new QNetworkAccessManager(this);
    manager->get(QNetworkRequest(QUrl("http://" + ip + phpstr + data)));
    ui->portName->setText(data);
    qInfo()<<ip + phpstr + data;
}

void MainWindow::portRead() {
    port.blockSignals(true);
    QByteArray data = port.readAll();
    data_string = QString::fromLatin1(data.data());
    qInfo() << data_string;
    sendData(data_string);
    port.blockSignals(false);
}

void MainWindow::on_openPort_clicked(){ /*"try to make connection" - button*/

    portN = ui->comList->currentText();
    qInfo()<<portN;
    if(searchPort(portN)){
        if(openPort(ui->baudList->currentText().toInt())){
            qInfo() << "Success!";
            ui->portName->clear();
            ui->portName->setText("Port opened");
        }
        else{
            qInfo() << "Failed to open";
            ui->portName->setText("Failed opening port");
        }
    }
    else{
        qInfo() << "Port not found";
        ui->portName->setText("Port not found - refresh the list");
    }
}

void MainWindow::on_portName_returnPressed() {/*Enter <=> press button*/
    MainWindow::on_openPort_clicked();
}

void MainWindow::on_readButton_clicked() {
    QObject::connect(&port, SIGNAL (readyRead()), this, SLOT(portRead()));

    ip = ui->ipBox->text();
    ui->ipBox->setText(ip);

    ui->portName->setText("Ready to stream");

    /*
    portRead();
    QString bufferString = QString::fromLocal8Bit(buffer);
    qInfo() << bufferString;
    ui->serialText->appendPlainText(bufferString);
    */
}

void MainWindow::on_searchButton_clicked() {
    ui->comList->clear();
    foreach (const QSerialPortInfo &info, QSerialPortInfo::availablePorts()) {
        qInfo() << "Name : " << info.portName();
        ui->comList->addItem(info.portName());
    }
}
