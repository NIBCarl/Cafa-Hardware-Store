# 🚀 SMS-Gate.app Quick Reference Guide

**Status:** ✅ OPERATIONAL  
**Last Updated:** November 9, 2025

---

## 📋 Quick Commands

### Test SMS Sending
```bash
cd cafa-pos
php artisan sms:test 09123456789 --message="Test message"
```

### Check Gateway Status
```bash
php artisan sms:test 09123456789
# Press 'n' when asked to send (will show status only)
```

### View SMS Logs
```bash
cd cafa-pos
Get-Content storage\logs\laravel.log -Tail 50 | Select-String "SMS"
```

### Clear Configuration Cache
```bash
cd cafa-pos
php artisan config:clear
php artisan cache:clear
```

---

## ⚙️ Configuration

### Current Settings (.env)
```env
ANDROID_SMS_GATEWAY_ENABLED=true
ANDROID_SMS_GATEWAY_URL=https://api.sms-gate.app
ANDROID_SMS_GATEWAY_USERNAME=DMQTCR
ANDROID_SMS_GATEWAY_PASSWORD=1p8fs-1-90ahbr
ANDROID_SMS_GATEWAY_DEVICE_ID=Skm6BLLt6Mhi9gHtDLprp
SMS_PROVIDER=hybrid
```

### Change SMS Provider
```env
# Use Android only (100% free, no backup)
SMS_PROVIDER=android

# Use hybrid (95% free + Semaphore backup) ✅ RECOMMENDED
SMS_PROVIDER=hybrid

# Use Semaphore only (old system)
SMS_PROVIDER=semaphore
```

---

## 🔧 Troubleshooting

### Gateway Shows Offline
```bash
# 1. Check Android device is connected
# 2. Verify credentials in .env
# 3. Clear cache
php artisan config:clear

# 4. Test connection
php artisan sms:test 09123456789
```

### SMS Not Delivered
```bash
# 1. Check Laravel logs
Get-Content storage\logs\laravel.log -Tail 20

# 2. Verify SIM has load
# 3. Check Android app is running
# 4. Try sending manually from phone
```

### Authentication Error (401)
```bash
# 1. Verify credentials in .env
# 2. Check username: DMQTCR
# 3. Check password: 1p8fs-1-90ahbr
# 4. Clear config cache
php artisan config:clear
```

---

## 📱 Android Device Checklist

- [ ] SMS Gateway app installed
- [ ] Connected to SMS-Gate.app cloud
- [ ] Device ID: Skm6BLLt6Mhi9gHtDLprp
- [ ] All permissions granted
- [ ] Battery optimization disabled
- [ ] SIM has unlimited text promo
- [ ] Device powered on
- [ ] Internet connected

---

## 📊 API Endpoints

### Check Status
```
GET /api/sms/status
Authorization: Bearer {admin_token}
```

### Send Test SMS
```
POST /api/sms/test
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "phone": "09123456789",
  "message": "Test message",
  "channel": "android"
}
```

---

## 💰 Cost Summary

| Provider | Cost | When Used |
|----------|------|-----------|
| SMS-Gate.app | FREE | 95% of the time |
| Semaphore | ₱0.50/SMS | Only when Android fails |
| **Average Cost** | **~₱0.025/SMS** | **Hybrid mode** |

**Monthly Cost:** ~₱400 (SIM load) + minimal Semaphore

---

## 📁 Important Files

```
cafa-pos/
├── app/
│   ├── Services/
│   │   ├── AndroidSmsService.php          ← SMS-Gate.app integration
│   │   ├── NotificationService.php        ← All SMS start here
│   │   └── Channels/
│   │       ├── SmsChannelFactory.php      ← Smart routing
│   │       ├── AndroidSmsChannel.php
│   │       └── SemaphoreSmsChannel.php
│   ├── Http/Controllers/Api/
│   │   └── SmsGatewayStatusController.php ← API endpoints
│   └── Console/Commands/
│       └── TestSmsCommand.php             ← Test command
├── config/
│   └── services.php                       ← SMS configuration
├── routes/
│   └── api.php                            ← API routes
└── .env                                   ← Credentials HERE
```

---

## 🎯 Common Tasks

### Add Semaphore Fallback
1. Get Semaphore API key from semaphore.co
2. Add to .env:
   ```env
   SEMAPHORE_API_KEY=your_api_key_here
   ```
3. Clear cache:
   ```bash
   php artisan config:clear
   ```

### Monitor SMS Usage
```bash
# Real-time monitoring
Get-Content storage\logs\laravel.log -Wait | Select-String "SMS"

# Count sent today
Get-Content storage\logs\laravel.log | Select-String "SMS sent successfully" | Measure-Object
```

### Reset Everything
```bash
# If something goes wrong
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Test again
php artisan sms:test 09123456789
```

---

## 📞 Getting Help

### Check Logs First
```bash
cd cafa-pos
Get-Content storage\logs\laravel.log -Tail 50
```

### Look for These Messages
✅ `"SMS sent successfully via SMS-Gate.app"`  
⚠️  `"Android SMS Gateway connection failed"`  
⚠️  `"Primary SMS channel failed"`  
✅ `"SMS sent successfully using fallback"`  

### Still Stuck?
1. Check `SMS_GATE_APP_FINAL_STATUS.md` for complete documentation
2. Review `SMS_GATE_APP_SETUP_COMPLETE.md` for setup steps
3. Verify Android device status
4. Check SMS-Gate.app dashboard

---

## ✅ Success Indicators

System is working when:
- ✅ `php artisan sms:test` shows "Gateway: ONLINE"
- ✅ Test SMS delivered to phone
- ✅ Laravel logs show "SMS sent successfully"
- ✅ POS transactions send receipts automatically
- ✅ No errors in logs

---

## 🔄 Regular Maintenance

### Weekly
- [ ] Check Android device is online
- [ ] Verify SIM has load
- [ ] Review SMS success rate in logs

### Monthly
- [ ] Renew SIM unlimited text promo
- [ ] Review total SMS sent
- [ ] Compare costs vs old system
- [ ] Check for any failures

---

**Quick Links:**
- 📖 Full Status: `SMS_GATE_APP_FINAL_STATUS.md`
- 📖 Setup Guide: `SMS_GATE_APP_SETUP_COMPLETE.md`
- 📖 Quick Start: `ANDROID_SMS_QUICK_START.md`

**Need more help?** Check the detailed guides above!
