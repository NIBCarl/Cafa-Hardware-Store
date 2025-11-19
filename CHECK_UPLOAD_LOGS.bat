@echo off
cd "c:\Users\Engr. John Rome\Desktop\capstone\SNSU CAPSTONE\cafa-pos"
powershell -Command "Get-Content storage\logs\laravel.log -Tail 20 | Select-String -Pattern 'Payment proof'"
pause
