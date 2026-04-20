# Postman Testing Guide for Futech Project API

## Base URL
```
http://localhost:8000/api
```
(Replace with your actual domain if different)

---

## 🔐 Authentication Flow

### 1. Register a New User
**POST** `/auth/register`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "User registered successfully. Please verify your email.",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "email_verified_at": null
        }
    }
}
```

**📧 OTP/Verification Email:**
- Check your email inbox for verification link
- If using `MAIL_MAILER=log`, check `storage/logs/laravel.log`
- Or check database: `SELECT * FROM otps WHERE email = 'john@example.com' ORDER BY created_at DESC LIMIT 1;`

---

### 2. Verify Email
**GET** `/auth/email/verify/{id}/{hash}?expires={timestamp}&signature={signature}`

**Note:** This link comes in the verification email. For testing, you can:
- Check the email sent
- Or use the database to get the signed URL from logs

**Alternative: Use Database Query to Get OTP**
```sql
SELECT code FROM otps 
WHERE email = 'john@example.com' 
AND type = 'email_verification' 
AND used = 0 
ORDER BY created_at DESC LIMIT 1;
```

---

### 3. Resend Verification Email
**POST** `/auth/email/resend`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Response:**
```json
{
    "success": true,
    "message": "Verification email resent"
}
```

---

### 4. Login
**POST** `/auth/login`

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (JSON) - Without 2FA:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Body (JSON) - With 2FA:**
```json
{
    "email": "john@example.com",
    "password": "password123",
    "two_factor_code": "123456"
}
```

**Response (Success):**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "email_verified_at": "2025-12-19T10:00:00.000000Z"
        },
        "token": "1|abcdefghijklmnopqrstuvwxyz..."
    }
}
```

**Response (Email Not Verified):**
```json
{
    "success": false,
    "message": "Please verify your email address before logging in.",
    "requires_email_verification": true
}
```

**Response (2FA Required):**
```json
{
    "success": false,
    "message": "Two-factor authentication code is required",
    "requires_two_factor": true
}
```

**💾 Save the token** from response for authenticated requests!

---

## 🔑 Password Reset Flow

### 1. Forgot Password (Request OTP)
**POST** `/auth/forgot-password`

**Headers:**
```
Content-Type: application/json
```

**Body (JSON):**
```json
{
    "email": "john@example.com"
}
```

**Response:**
```json
{
    "success": true,
    "message": "If the email exists, a password reset OTP has been sent."
}
```

**📧 Get OTP:**
- Check email inbox
- Or database: `SELECT code FROM otps WHERE email = 'john@example.com' AND type = 'forgot_password' AND used = 0 ORDER BY created_at DESC LIMIT 1;`

---

### 2. Reset Password (Verify OTP)
**POST** `/auth/reset-password`

**Headers:**
```
Content-Type: application/json
```

**Body (JSON):**
```json
{
    "email": "john@example.com",
    "otp": "123456",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Password has been reset successfully."
}
```

---

## 👤 Profile Management

### 1. Get Current User
**GET** `/auth/user`

**Headers:**
```
Authorization: Bearer {your_token}
Accept: application/json
```

**Response:**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "email_verified_at": "2025-12-19T10:00:00.000000Z",
            "two_factor_enabled": false,
            "pending_email": null
        }
    }
}
```

---

### 2. Update Profile (Name/Password Only)
**PUT** `/auth/profile`

**Headers:**
```
Authorization: Bearer {your_token}
Content-Type: application/json
```

**Body (JSON):**
```json
{
    "name": "John Updated",
    "password": "newpassword123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Profile updated successfully.",
    "data": {
        "user": {
            "id": 1,
            "name": "John Updated",
            "email": "john@example.com"
        }
    }
}
```

---

### 3. Update Email (Step 1: Request OTP)
**PUT** `/auth/profile`

**Headers:**
```
Authorization: Bearer {your_token}
Content-Type: application/json
```

**Body (JSON):**
```json
{
    "email": "newemail@example.com"
}
```

**Response:**
```json
{
    "success": true,
    "message": "OTP sent to new email address. Please verify to complete email change.",
    "requires_otp": true
}
```

**📧 Get OTP from new email:**
- Check `newemail@example.com` inbox
- Or database: `SELECT code FROM otps WHERE email = 'newemail@example.com' AND type = 'email_change' AND used = 0 ORDER BY created_at DESC LIMIT 1;`

---

### 4. Update Email (Step 2: Verify OTP)
**PUT** `/auth/profile`

**Headers:**
```
Authorization: Bearer {your_token}
Content-Type: application/json
```

**Body (JSON):**
```json
{
    "email": "newemail@example.com",
    "email_otp": "123456"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Profile updated successfully.",
    "data": {
        "user": {
            "id": 1,
            "name": "John Updated",
            "email": "newemail@example.com",
            "email_verified_at": null
        }
    }
}
```

**Note:** After email change, user needs to verify the new email again.

---

## 🔒 Two-Factor Authentication (2FA)

### 1. Setup 2FA
**POST** `/auth/2fa/setup`

**Headers:**
```
Authorization: Bearer {your_token}
Content-Type: application/json
```

**Response:**
```json
{
    "success": true,
    "message": "2FA setup initiated. Scan the QR code and verify with a code.",
    "data": {
        "secret": "ABCDEFGHIJKLMNOP",
        "qr_code_url": "otpauth://totp/Futech%20Project:john@example.com?secret=ABCDEFGHIJKLMNOP&issuer=Futech%20Project",
        "recovery_codes": [
            "A1B2C3D4",
            "E5F6G7H8",
            ...
        ]
    }
}
```

**📱 Next Steps:**
1. Copy the `secret` or scan the `qr_code_url` with an authenticator app (Google Authenticator, Authy, etc.)
2. Use the authenticator app to generate a 6-digit code
3. Verify the code in the next step

---

### 2. Verify and Enable 2FA
**POST** `/auth/2fa/verify`

**Headers:**
```
Authorization: Bearer {your_token}
Content-Type: application/json
```

**Body (JSON):**
```json
{
    "code": "123456"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Two-factor authentication enabled successfully."
}
```

---

### 3. Check if 2FA is Enabled
**GET** `/auth/user`

Check the `two_factor_enabled` field in the response.

---

### 4. Login with 2FA
After enabling 2FA, login requires the 2FA code:

**POST** `/auth/login`

**Body (JSON):**
```json
{
    "email": "john@example.com",
    "password": "password123",
    "two_factor_code": "123456"
}
```

Get the code from your authenticator app!

---

### 5. Disable 2FA
**POST** `/auth/2fa/disable`

**Headers:**
```
Authorization: Bearer {your_token}
Content-Type: application/json
```

**Body (JSON):**
```json
{
    "code": "123456"
}
```

**Note:** You can use either:
- 2FA code from authenticator app
- Recovery code (one of the codes from setup)

**Response:**
```json
{
    "success": true,
    "message": "Two-factor authentication disabled successfully."
}
```

---

## 🚪 Logout

**POST** `/auth/logout`

**Headers:**
```
Authorization: Bearer {your_token}
Content-Type: application/json
```

**Response:**
```json
{
    "success": true,
    "message": "Successfully logged out"
}
```

---

## 🔍 How to Get OTP Codes for Testing

### Method 1: Check Email
If you have email configured, check your inbox.

### Method 2: Check Database (Recommended for Testing)
```sql
-- Get latest OTP for an email
SELECT code, type, expires_at, used, created_at 
FROM otps 
WHERE email = 'your-email@example.com' 
AND used = 0 
ORDER BY created_at DESC 
LIMIT 1;
```

### Method 3: Check Laravel Logs
If `MAIL_MAILER=log` in `.env`, check:
```bash
tail -f storage/logs/laravel.log
```

### Method 4: Use the Helper Endpoint (See below)

---

## 📊 Check Feature Status

### Check Email Verification Status
**GET** `/auth/user`

Look for:
- `email_verified_at`: `null` = not verified, has date = verified

### Check 2FA Status
**GET** `/auth/user`

Look for:
- `two_factor_enabled`: `true` = enabled, `false` = disabled

### Check Pending Email Change
**GET** `/auth/user`

Look for:
- `pending_email`: `null` = no pending change, has email = pending change

---

## 🛠️ Helper Endpoint (For Testing)

I'll create a helper endpoint to get OTP codes easily. See the next section.

---

## 📝 Postman Collection Setup

1. **Create Environment Variables:**
   - `base_url`: `http://localhost:8000/api`
   - `token`: (will be set after login)

2. **Create Collection:**
   - Auth
     - Register
     - Login
     - Verify Email
     - Resend Verification
     - Logout
   - Password Reset
     - Forgot Password
     - Reset Password
   - Profile
     - Get User
     - Update Profile
   - 2FA
     - Setup 2FA
     - Verify 2FA
     - Disable 2FA

3. **Set Authorization:**
   - For authenticated routes, use: `Bearer {{token}}`
   - Set token variable after login response

---

## ⚠️ Common Issues

1. **"Email not verified" error:**
   - Check email or database for verification link/OTP
   - Use resend verification endpoint

2. **"Invalid OTP" error:**
   - OTP expires in 5 minutes
   - OTP can only be used once
   - Check database for latest unused OTP

3. **"2FA code required" error:**
   - User has 2FA enabled
   - Get code from authenticator app
   - Or use recovery code

4. **Token expired:**
   - Login again to get new token

---

## 🎯 Quick Test Flow

1. Register → Get verification email/OTP
2. Verify email (or use database OTP)
3. Login → Get token
4. Update profile (test email change with OTP)
5. Setup 2FA → Scan QR code
6. Verify 2FA → Enable it
7. Login with 2FA code
8. Test forgot password flow

---

Happy Testing! 🚀

