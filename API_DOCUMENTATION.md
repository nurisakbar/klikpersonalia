# Payroll Management System - API Documentation

## Table of Contents
1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Base URL](#base-url)
4. [Response Format](#response-format)
5. [Error Handling](#error-handling)
6. [Endpoints](#endpoints)
7. [Mobile API](#mobile-api)
8. [Webhooks](#webhooks)
9. [Rate Limiting](#rate-limiting)
10. [Examples](#examples)

---

## Overview

The Payroll Management System provides a comprehensive RESTful API for integrating with external systems, mobile applications, and third-party services. The API supports all major payroll operations including employee management, payroll processing, attendance tracking, and reporting.

### API Version
- **Current Version**: v1.0
- **Base URL**: `https://your-domain.com/api/v1`
- **Content Type**: `application/json`

---

## Authentication

### Laravel Sanctum Authentication
The API uses Laravel Sanctum for token-based authentication.

#### Login
```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}
```

**Response:**
```json
{
    "success": true,
    "token": "1|abc123def456ghi789...",
    "user": {
        "id": "uuid",
        "name": "John Doe",
        "email": "user@example.com",
        "role": "employee",
        "company_id": "company-uuid"
    }
}
```

#### Logout
```http
POST /api/auth/logout
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

### API Token Usage
Include the token in the Authorization header for all protected endpoints:
```http
Authorization: Bearer {your-token}
```

---

## Base URL

### Development
```
http://localhost:8000/api/v1
```

### Production
```
https://your-domain.com/api/v1
```

---

## Response Format

### Success Response
```json
{
    "success": true,
    "data": {
        // Response data
    },
    "message": "Operation completed successfully"
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field": ["Validation error message"]
    }
}
```

### Paginated Response
```json
{
    "success": true,
    "data": [
        // Array of items
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 15,
        "total": 100,
        "last_page": 7,
        "from": 1,
        "to": 15
    }
}
```

---

## Error Handling

### HTTP Status Codes
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `429` - Too Many Requests
- `500` - Internal Server Error

### Common Error Messages
```json
{
    "success": false,
    "message": "Unauthorized access",
    "code": "UNAUTHORIZED"
}
```

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required"],
        "password": ["The password must be at least 8 characters"]
    }
}
```

---

## Endpoints

### Authentication Endpoints

#### Login
```http
POST /api/auth/login
```

**Request Body:**
```json
{
    "email": "user@example.com",
    "password": "password"
}
```

#### Logout
```http
POST /api/auth/logout
Authorization: Bearer {token}
```

#### Refresh Token
```http
POST /api/auth/refresh
Authorization: Bearer {token}
```

### Employee Endpoints

#### Get All Employees
```http
GET /api/employees
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15)
- `search` - Search term
- `department` - Filter by department
- `status` - Filter by status (active, inactive)

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": "uuid",
            "first_name": "John",
            "last_name": "Doe",
            "email": "john.doe@example.com",
            "position": "Software Engineer",
            "department": "IT",
            "salary": 5000000,
            "hire_date": "2023-01-15",
            "is_active": true,
            "created_at": "2023-01-15T00:00:00.000000Z"
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 15,
        "total": 100
    }
}
```

#### Get Employee by ID
```http
GET /api/employees/{id}
Authorization: Bearer {token}
```

#### Create Employee
```http
POST /api/employees
Authorization: Bearer {token}
Content-Type: application/json

{
    "first_name": "Jane",
    "last_name": "Smith",
    "email": "jane.smith@example.com",
    "phone": "081234567890",
    "position": "HR Manager",
    "department": "HR",
    "salary": 6000000,
    "hire_date": "2024-01-01",
    "ptkp_status": "TK/0"
}
```

#### Update Employee
```http
PUT /api/employees/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "salary": 6500000,
    "position": "Senior HR Manager"
}
```

#### Delete Employee
```http
DELETE /api/employees/{id}
Authorization: Bearer {token}
```

### Payroll Endpoints

#### Get All Payrolls
```http
GET /api/payrolls
Authorization: Bearer {token}
```

**Query Parameters:**
- `period` - Payroll period (YYYY-MM)
- `status` - Payroll status (pending, approved, paid)
- `employee_id` - Filter by employee

#### Get Payroll by ID
```http
GET /api/payrolls/{id}
Authorization: Bearer {token}
```

#### Create Payroll
```http
POST /api/payrolls
Authorization: Bearer {token}
Content-Type: application/json

{
    "employee_id": "employee-uuid",
    "payroll_period": "2024-01",
    "basic_salary": 5000000,
    "allowances": 500000,
    "deductions": 200000,
    "overtime_pay": 100000
}
```

#### Calculate Payroll
```http
POST /api/payrolls/calculate
Authorization: Bearer {token}
Content-Type: application/json

{
    "employee_id": "employee-uuid",
    "payroll_period": "2024-01"
}
```

#### Approve Payroll
```http
PUT /api/payrolls/{id}/approve
Authorization: Bearer {token}
```

### Attendance Endpoints

#### Get Attendance Records
```http
GET /api/attendance
Authorization: Bearer {token}
```

**Query Parameters:**
- `employee_id` - Filter by employee
- `date` - Specific date (YYYY-MM-DD)
- `start_date` - Start date range
- `end_date` - End date range

#### Check In/Out
```http
POST /api/attendance/check-in-out
Authorization: Bearer {token}
Content-Type: application/json

{
    "type": "check_in",
    "latitude": -6.2088,
    "longitude": 106.8456,
    "notes": "Optional notes"
}
```

#### Create Attendance Record
```http
POST /api/attendance
Authorization: Bearer {token}
Content-Type: application/json

{
    "employee_id": "employee-uuid",
    "date": "2024-01-15",
    "check_in": "08:00:00",
    "check_out": "17:00:00",
    "total_hours": 8
}
```

### Leave Endpoints

#### Get Leave Requests
```http
GET /api/leaves
Authorization: Bearer {token}
```

#### Submit Leave Request
```http
POST /api/leaves
Authorization: Bearer {token}
Content-Type: application/json

{
    "leave_type": "annual",
    "start_date": "2024-02-01",
    "end_date": "2024-02-03",
    "reason": "Family vacation"
}
```

#### Approve/Reject Leave
```http
PUT /api/leaves/{id}/approve
Authorization: Bearer {token}
Content-Type: application/json

{
    "status": "approved",
    "notes": "Approved by manager"
}
```

### Tax Endpoints

#### Get Tax Records
```http
GET /api/taxes
Authorization: Bearer {token}
```

#### Calculate Tax
```http
POST /api/taxes/calculate
Authorization: Bearer {token}
Content-Type: application/json

{
    "employee_id": "employee-uuid",
    "tax_period": "2024-01",
    "gross_income": 5000000,
    "ptkp_status": "TK/0"
}
```

### BPJS Endpoints

#### Get BPJS Records
```http
GET /api/bpjs
Authorization: Bearer {token}
```

#### Calculate BPJS
```http
POST /api/bpjs/calculate
Authorization: Bearer {token}
Content-Type: application/json

{
    "employee_id": "employee-uuid",
    "bpjs_period": "2024-01",
    "salary": 5000000
}
```

### Report Endpoints

#### Get Payroll Report
```http
GET /api/reports/payroll
Authorization: Bearer {token}
```

**Query Parameters:**
- `period` - Report period (YYYY-MM)
- `format` - Export format (json, pdf, excel)

#### Get Tax Report
```http
GET /api/reports/tax
Authorization: Bearer {token}
```

#### Get Attendance Report
```http
GET /api/reports/attendance
Authorization: Bearer {token}
```

---

## Mobile API

### Mobile Authentication

#### Mobile Login
```http
POST /api/mobile/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password",
    "device_id": "device-uuid"
}
```

### Mobile Dashboard

#### Get Dashboard Data
```http
GET /api/mobile/dashboard
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "employee": {
            "id": "uuid",
            "name": "John Doe",
            "position": "Software Engineer",
            "department": "IT"
        },
        "today_attendance": {
            "check_in": "08:00:00",
            "check_out": null,
            "status": "present"
        },
        "monthly_summary": {
            "total_days": 22,
            "present_days": 20,
            "leave_days": 2
        },
        "recent_payslips": [
            {
                "id": "uuid",
                "period": "2024-01",
                "net_salary": 4500000
            }
        ],
        "leave_balance": {
            "annual": 12,
            "sick": 12,
            "personal": 6
        }
    }
}
```

### Mobile Attendance

#### Check In/Out
```http
POST /api/mobile/attendance/check-in-out
Authorization: Bearer {token}
Content-Type: application/json

{
    "type": "check_in",
    "latitude": -6.2088,
    "longitude": 106.8456,
    "location_name": "Office Building"
}
```

#### Get Attendance History
```http
GET /api/mobile/attendance/history
Authorization: Bearer {token}
```

### Mobile Payroll

#### Get Payslip
```http
GET /api/mobile/payslip/{id}
Authorization: Bearer {token}
```

#### Get Payslip List
```http
GET /api/mobile/payslips
Authorization: Bearer {token}
```

### Mobile Leave

#### Submit Leave Request
```http
POST /api/mobile/leave/request
Authorization: Bearer {token}
Content-Type: application/json

{
    "leave_type": "annual",
    "start_date": "2024-02-01",
    "end_date": "2024-02-03",
    "reason": "Family vacation"
}
```

#### Get Leave History
```http
GET /api/mobile/leave/history
Authorization: Bearer {token}
```

#### Get Leave Balance
```http
GET /api/mobile/leave/balance
Authorization: Bearer {token}
```

### Mobile Profile

#### Get Profile
```http
GET /api/mobile/profile
Authorization: Bearer {token}
```

#### Update Profile
```http
PUT /api/mobile/profile
Authorization: Bearer {token}
Content-Type: application/json

{
    "phone": "081234567890",
    "address": "New Address"
}
```

---

## Webhooks

### Webhook Configuration
Configure webhooks to receive real-time updates from the system.

#### Available Webhooks
- `employee.created` - New employee created
- `employee.updated` - Employee information updated
- `payroll.generated` - New payroll generated
- `payroll.approved` - Payroll approved
- `attendance.recorded` - New attendance record
- `leave.requested` - New leave request
- `leave.approved` - Leave request approved/rejected

#### Webhook Endpoint
```http
POST /api/webhooks/{event}
Content-Type: application/json
X-Webhook-Signature: {signature}

{
    "event": "employee.created",
    "data": {
        "employee_id": "uuid",
        "employee_name": "John Doe",
        "timestamp": "2024-01-15T10:30:00Z"
    }
}
```

#### Webhook Security
Webhooks are secured with HMAC signatures:
```php
$signature = hash_hmac('sha256', $payload, $webhook_secret);
```

---

## Rate Limiting

### Rate Limit Rules
- **Authentication endpoints**: 5 requests per minute
- **General API endpoints**: 60 requests per minute
- **Report endpoints**: 10 requests per minute
- **Mobile API endpoints**: 100 requests per minute

### Rate Limit Headers
```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1642234567
```

### Rate Limit Exceeded Response
```json
{
    "success": false,
    "message": "Too many requests",
    "retry_after": 60
}
```

---

## Examples

### Complete Employee Management Flow

#### 1. Create Employee
```http
POST /api/employees
Authorization: Bearer {token}
Content-Type: application/json

{
    "first_name": "Jane",
    "last_name": "Smith",
    "email": "jane.smith@example.com",
    "phone": "081234567890",
    "position": "HR Manager",
    "department": "HR",
    "salary": 6000000,
    "hire_date": "2024-01-01",
    "ptkp_status": "TK/0"
}
```

#### 2. Generate Payroll
```http
POST /api/payrolls/calculate
Authorization: Bearer {token}
Content-Type: application/json

{
    "employee_id": "employee-uuid",
    "payroll_period": "2024-01"
}
```

#### 3. Approve Payroll
```http
PUT /api/payrolls/{payroll-id}/approve
Authorization: Bearer {token}
```

### Mobile Application Flow

#### 1. Mobile Login
```http
POST /api/mobile/login
Content-Type: application/json

{
    "email": "employee@example.com",
    "password": "password",
    "device_id": "device-uuid"
}
```

#### 2. Check In
```http
POST /api/mobile/attendance/check-in-out
Authorization: Bearer {token}
Content-Type: application/json

{
    "type": "check_in",
    "latitude": -6.2088,
    "longitude": 106.8456
}
```

#### 3. View Payslip
```http
GET /api/mobile/payslip/{payslip-id}
Authorization: Bearer {token}
```

### Error Handling Example

#### Validation Error
```http
POST /api/employees
Authorization: Bearer {token}
Content-Type: application/json

{
    "first_name": "",
    "email": "invalid-email"
}
```

**Response:**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "first_name": ["The first name field is required"],
        "email": ["The email must be a valid email address"]
    }
}
```

#### Authentication Error
```http
GET /api/employees
Authorization: Bearer invalid-token
```

**Response:**
```json
{
    "success": false,
    "message": "Unauthenticated"
}
```

---

## SDKs and Libraries

### PHP SDK
```php
composer require payroll-system/api-client
```

```php
use PayrollSystem\ApiClient;

$client = new ApiClient('your-api-key');
$employees = $client->employees()->all();
```

### JavaScript SDK
```javascript
npm install payroll-system-api
```

```javascript
import PayrollAPI from 'payroll-system-api';

const api = new PayrollAPI('your-api-key');
const employees = await api.employees.getAll();
```

### Python SDK
```python
pip install payroll-system-api
```

```python
from payroll_system import PayrollAPI

api = PayrollAPI('your-api-key')
employees = api.employees.get_all()
```

---

## Testing

### API Testing with Postman
Import the Postman collection for testing all endpoints:
```
https://your-domain.com/api/postman-collection.json
```

### API Testing with cURL
```bash
# Login
curl -X POST https://your-domain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# Get employees
curl -X GET https://your-domain.com/api/employees \
  -H "Authorization: Bearer {token}"
```

---

## Support

### API Support
- **Documentation**: https://docs.payroll-system.com/api
- **Support Email**: api-support@payroll-system.com
- **Developer Portal**: https://developers.payroll-system.com

### Rate Limits and Quotas
- **Free Tier**: 1,000 requests per month
- **Professional Tier**: 10,000 requests per month
- **Enterprise Tier**: Unlimited requests

### API Versioning
- **Current Version**: v1.0
- **Deprecation Policy**: 12 months notice for breaking changes
- **Migration Guide**: Available for version upgrades

---

**Note**: This API documentation is regularly updated. For the latest version, please check the developer portal or contact API support. 