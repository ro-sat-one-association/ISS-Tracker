#ifndef MAINWINDOW_H
#define MAINWINDOW_H

#include <QMainWindow>

namespace Ui {
class MainWindow;
}

class MainWindow : public QMainWindow
{
    Q_OBJECT

public:
    explicit MainWindow(QWidget *parent = 0);
    ~MainWindow();

private slots:
    void on_openPort_clicked();

    void on_readButton_clicked();

    void on_portName_returnPressed();

    void on_searchButton_clicked();

    void portRead();

    void sendData(QString);

private:
    Ui::MainWindow *ui;
};

#endif // MAINWINDOW_H
