# API Documentation

## Base URL
```
http://localhost:8000/api
```

## Endpoints

### Authentication

#### Register
```
POST /register
```
Request:
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```
Response:
```json
{
  "success": true,
  "message": "User registered successfully. An OTP has been sent to your email for verification.",
  "data": {
    "user": { ... }
  }
}
```

#### Login
```
POST /login
```
Request:
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

#### Logout
```
POST /logout
```
Requires: Bearer Token

### Blog

#### Get All Blogs
```
GET /blogs
```
Query parameters:
- `per_page` (optional, default: 15)

#### Create Blog
```
POST /blogs
```
Requires: Auth, Bearer Token
Request:
```json
{
  "title": "My Blog Post",
  "content": "Blog content here...",
  "image": "base64 encoded image (optional)"
}
```

#### Get Single Blog
```
GET /blogs/{id}
```

#### Update Blog
```
PUT /blogs/{id}
```
Requires: Auth (author only)

#### Delete Blog
```
DELETE /blogs/{id}
```
Requires: Auth (author only)

### OTP Verification

#### Send Email Verification OTP
```
POST /send-email-verification-otp
```

#### Verify Email
```
POST /verify-email-with-otp
```
Request:
```json
{
  "email": "john@example.com",
  "otp": "123456"
}
```

### Password Reset

#### Forgot Password
```
POST /forgot-password
```
Request:
```json
{
  "email": "john@example.com"
}
```

#### Reset Password
```
POST /reset-password
```
Request:
```json
{
  "email": "john@example.com",
  "otp": "123456",
  "password": "newpassword",
  "password_confirmation": "newpassword"
}
```

## Error Responses

```json
{
  "success": false,
  "message": "Error message here"
}
```

Status codes:
- 200: Success
- 201: Created
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 422: Validation Error
- 500: Server Error