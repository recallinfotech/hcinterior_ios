@echo off
set "JAVA_HOME=C:\Users\user\.gradle\jdks\eclipse_adoptium-17-amd64-windows.2"
set "PATH=%JAVA_HOME%\bin;%PATH%"
cd /d "c:\Users\user\Desktop\hcinterior\android"
call gradlew.bat assembleDebug
