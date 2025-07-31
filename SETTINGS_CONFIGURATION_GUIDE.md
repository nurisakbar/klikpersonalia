# Settings & Configuration System Guide

## 📋 **Overview**

The Settings & Configuration System provides comprehensive management capabilities for company settings, user management, and system policies. This system allows administrators to configure various aspects of the payroll application according to their business requirements.

## 🏗️ **Architecture**

### **Core Components**
- **SettingsController**: Main controller handling all settings operations
- **Company Model**: Stores company information and settings
- **User Model**: Manages user accounts and permissions
- **Blade Views**: User interface for settings management

### **Key Features**
- Company profile management with logo upload
- Payroll policy configuration (overtime rates, attendance bonus)
- Leave policy management (quotas, approval workflow)
- User profile and password management
- User management with role-based access control
- System settings and backup management

## 📊 **Database Schema**

### **Companies Table**
```sql
- id (UUID, Primary Key)
- name (String)
- email (String)
- phone (String)
- address (Text)
- city (String)
- province (String)
- postal_code (String)
- country (String)
- website (String, Nullable)
- tax_number (String, Nullable)
- business_number (String, Nullable)
- logo (String, Nullable)
- subscription_plan (String)
- max_employees (Integer)
- status (String)
- payroll_settings (JSON)
- leave_settings (JSON)
- created_at (Timestamp)
- updated_at (Timestamp)
```

### **Users Table**
```sql
- id (UUID, Primary Key)
- name (String)
- email (String, Unique)
- password (String)
- role (String: admin, hr, manager, employee)
- status (String: active, inactive)
- company_id (UUID, Foreign Key)
- avatar (String, Nullable)
- phone (String, Nullable)
- email_verified_at (Timestamp, Nullable)
- last_login_at (Timestamp, Nullable)
- created_at (Timestamp)
- updated_at (Timestamp)
```

## 🎯 **Features & Functionality**

### **1. Company Settings**
- **Profile Management**: Update company information, logo, contact details
- **Business Information**: Tax number, business registration, address
- **Subscription Details**: Plan information, employee limits, status

**Key Features:**
- Logo upload with preview
- Real-time form validation
- Company information summary
- File upload handling

### **2. Payroll Policy Configuration**
- **Working Hours**: Configure daily and weekly working hours
- **Overtime Rates**: Set multipliers for different overtime types
  - Regular overtime (default: 1.5x)
  - Holiday overtime (default: 2.0x)
  - Weekend overtime (default: 2.0x)
  - Emergency overtime (default: 3.0x)
- **Attendance Bonus**: Configure bonus rates for good attendance
  - 95%+ attendance bonus (default: 5%)
  - 90%+ attendance bonus (default: 3%)
- **Leave Deduction**: Enable/disable salary deductions for excessive leave
- **Payroll Schedule**: Set payroll processing day of month

**Key Features:**
- Real-time calculation preview
- Input validation with min/max values
- Policy summary display
- Toggle switches for boolean settings

### **3. Leave Policy Management**
- **Leave Quotas**: Set annual quotas for different leave types
  - Annual leave (default: 12 days)
  - Sick leave (default: 12 days)
  - Maternity leave (default: 90 days)
  - Paternity leave (default: 3 days)
  - Other leave (default: 6 days)
- **Approval Workflow**: Configure leave approval requirements
- **Advance Notice**: Set minimum notice period for leave requests
- **Carry Forward**: Enable/disable unused leave carry forward

**Key Features:**
- Real-time summary updates
- Policy configuration interface
- Leave type management
- Approval workflow settings

### **4. User Profile Management**
- **Personal Information**: Update name, email, phone
- **Profile Picture**: Upload and manage avatar
- **Account Information**: View account details, verification status
- **Security Settings**: Change password with strength validation

**Key Features:**
- Avatar upload with preview
- Password strength indicator
- Account information display
- Email verification status

### **5. Password Management**
- **Current Password Verification**: Secure password change process
- **Password Strength Validation**: Real-time strength checking
- **Requirements Checklist**: Visual feedback for password requirements
- **Security Guidelines**: Password security tips and best practices

**Password Requirements:**
- Minimum 8 characters
- Uppercase and lowercase letters
- Numbers and special characters
- Password confirmation matching

**Key Features:**
- Real-time strength indicator
- Requirements checklist
- Password visibility toggle
- Security guidelines

### **6. User Management**
- **User Listing**: View all company users with pagination
- **User Statistics**: Dashboard with user counts and status
- **Role Management**: Assign roles (admin, hr, manager, employee)
- **Status Management**: Activate/deactivate user accounts
- **User Operations**: Create, edit, delete users

**Key Features:**
- User statistics dashboard
- Role-based access control
- User status management
- Modal-based CRUD operations
- Email verification indicators

## 🔐 **Security & Permissions**

### **Role-Based Access Control**
- **Admin**: Full access to all settings
- **HR**: Access to payroll and leave policy settings
- **Manager**: Limited access to team-related settings
- **Employee**: Access to personal profile and password settings

### **Security Features**
- Password strength validation
- Current password verification
- File upload validation
- CSRF protection
- Input sanitization

## 🎨 **User Interface**

### **Design Principles**
- **AdminLTE 3**: Modern, responsive design
- **Card-based Layout**: Organized information display
- **Real-time Feedback**: Live updates and validation
- **Modal Dialogs**: Clean form interfaces
- **Progress Indicators**: Visual feedback for operations

### **Key UI Components**
- **Settings Dashboard**: Overview of all settings categories
- **Form Validation**: Real-time error feedback
- **File Upload**: Drag-and-drop logo/avatar upload
- **Toggle Switches**: Boolean setting controls
- **Progress Bars**: Password strength indicators
- **Statistics Cards**: User and system statistics

## 📱 **Responsive Design**

### **Mobile Optimization**
- Responsive grid layouts
- Touch-friendly form controls
- Mobile-optimized modals
- Adaptive navigation

### **Cross-Browser Compatibility**
- Modern browser support
- Progressive enhancement
- Fallback mechanisms

## 🔧 **Technical Implementation**

### **Controller Methods**
```php
// Company Settings
public function company()
public function updateCompany(Request $request)

// Payroll Policy
public function payrollPolicy()
public function updatePayrollPolicy(Request $request)

// Leave Policy
public function leavePolicy()
public function updateLeavePolicy(Request $request)

// User Profile
public function profile()
public function updateProfile(Request $request)

// Password Management
public function password()
public function updatePassword(Request $request)

// User Management
public function users()
public function createUser(Request $request)
public function updateUser(Request $request, $id)
public function deleteUser($id)
```

### **Validation Rules**
```php
// Company Settings
'name' => 'required|string|max:255'
'email' => 'required|email|max:255'
'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'

// Payroll Policy
'working_hours_per_day' => 'required|numeric|min:1|max:24'
'overtime_regular_rate' => 'required|numeric|min:1|max:5'

// Leave Policy
'annual_leave_quota' => 'required|numeric|min:0|max:365'
'leave_notice_days' => 'required|numeric|min:0|max:30'

// User Management
'name' => 'required|string|max:255'
'email' => 'required|email|unique:users'
'password' => 'required|string|min:8|confirmed'
'role' => 'required|string|in:admin,hr,manager,employee'
```

## 📈 **Performance Optimization**

### **Database Optimization**
- Indexed foreign keys
- Efficient JSON storage for settings
- Pagination for user listings
- Optimized queries

### **Frontend Optimization**
- Lazy loading for images
- Debounced input validation
- Efficient DOM manipulation
- Minified assets

## 🧪 **Testing Strategy**

### **Unit Tests**
- Controller method testing
- Validation rule testing
- Model relationship testing
- File upload testing

### **Integration Tests**
- Settings workflow testing
- User management testing
- Permission testing
- Form submission testing

### **User Acceptance Testing**
- Settings configuration workflow
- User management operations
- Password change process
- File upload functionality

## 🚀 **Deployment Considerations**

### **File Storage**
- Configured file storage for logos and avatars
- Image optimization and resizing
- Backup and recovery procedures

### **Environment Configuration**
- Database migration setup
- File permissions configuration
- Email configuration for notifications

## 📚 **Usage Examples**

### **Configuring Payroll Policy**
1. Navigate to Settings → Payroll Policy
2. Set working hours (e.g., 8 hours/day, 5 days/week)
3. Configure overtime rates for different scenarios
4. Set attendance bonus rates
5. Configure leave deduction settings
6. Set payroll processing day
7. Save changes

### **Managing Users**
1. Navigate to Settings → User Management
2. View user statistics and listing
3. Click "Add New User" to create user
4. Fill in user details and assign role
5. Edit existing users as needed
6. Deactivate users when required

### **Updating Company Profile**
1. Navigate to Settings → Company Settings
2. Update company information
3. Upload new company logo
4. Verify business details
5. Save changes

## 🔄 **Workflow Examples**

### **New User Onboarding**
1. Admin creates user account
2. User receives email notification
3. User logs in and changes password
4. User updates profile information
5. User accesses system based on role

### **Policy Updates**
1. Admin reviews current policies
2. Admin updates policy settings
3. Changes are applied immediately
4. Users are notified of changes
5. New policies take effect

## 🛠️ **Maintenance & Support**

### **Regular Maintenance**
- Database backup procedures
- File storage cleanup
- Log rotation and monitoring
- Performance monitoring

### **Troubleshooting**
- Common issues and solutions
- Error logging and monitoring
- User support procedures
- System recovery procedures

## 🔮 **Future Enhancements**

### **Planned Features**
- Advanced user permissions
- Audit trail for settings changes
- Bulk user operations
- Advanced backup options
- API integration for settings

### **Scalability Considerations**
- Multi-tenant architecture support
- Advanced caching strategies
- Microservices architecture
- Cloud deployment options

---

**Last Updated:** July 31, 2025  
**Version:** 1.0  
**Status:** Complete ✅ 