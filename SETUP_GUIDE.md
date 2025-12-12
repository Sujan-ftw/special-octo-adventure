# PSG Polytechnic College - Role-Based Access System Setup Guide

## Overview
This system implements a comprehensive role-based access control system for the college department project with separate portals for students and staff.

## Features Implemented

### Authentication System
- **Unified Login Page** (`auth_login.php`): Secure login with session management
- **Registration Page** (`auth_register.php`): User registration with role selection (Student/Staff)
- **Session Security**: CSRF protection, session timeout (30 minutes), password hashing
- **Role-Based Access Control**: Automatic redirection based on user role

### Student Portal Features
1. **Home Page** (`student_home.php`): Dashboard with navigation to all features
2. **About Page** (`student_about.php`): View-only access to college information
3. **Departments** (`student_departments.php`): Department information (restricted to student's dept if enrolled)
4. **Gallery** (`student_gallery.php`): View college event photos
5. **Marks** (`student_marks.php`): View academic performance by semester
6. **Registration Forms** (`student_register.php`):
   - Submit initial registration forms
   - Forms route to staff for approval
   - Unlock additional activities (sports, cultural, technical clubs) after initial approval
   - Track submission status (pending/approved/rejected)

### Staff Portal Features
1. **Home Page** (`staff_home.php`): Dashboard with admin controls and notification badges
2. **Gallery Upload** (`staff_gallery_upload.php`): Upload images to college gallery
3. **Department Content** (`staff_department_content.php`): Upload announcements, syllabi, notes
4. **Form Approval** (`staff_approve_forms.php`):
   - Review pending student registration forms
   - Approve or reject with comments
   - View history of reviewed forms
5. **Marks Entry** (`staff_marks_entry.php`):
   - Enter marks for students by semester
   - Support multiple subjects per semester
   - Calculate totals and pass/fail status
   - Restricted to allocated subjects

## Database Setup

### Step 1: Run the Base Schema
Execute the existing `iqac.sql` file to create the base database structure:
```bash
mysql -u root -p < iqac.sql
```

### Step 2: Apply Updates
Execute `schema_updates.sql` to add new tables and fields:
```bash
mysql -u root -p iqac < schema_updates.sql
```

This adds:
- `student_registrations` table for form submissions
- `staff_subjects` table for subject allocation
- Additional fields to `students` table (department, year_class, tutor_id, user_id)
- Additional field to `staff_details` table (user_id)

### Step 3: Create Sample Users
```sql
USE iqac;

-- Create a staff user
INSERT INTO users (username, password, role) 
VALUES ('staff1', '$2y$10$abcdefghijklmnopqrstuvwxyz', 'staff');
-- Password: 'password123' (you should hash this properly)

-- Create a student user
INSERT INTO users (username, password, role) 
VALUES ('student1', '$2y$10$abcdefghijklmnopqrstuvwxyz', 'student');
-- Password: 'password123' (you should hash this properly)
```

**Important**: Use PHP's `password_hash()` function to generate secure password hashes:
```php
<?php
echo password_hash('password123', PASSWORD_DEFAULT);
?>
```

## File Structure
```
.
├── auth_login.php              # Unified login page
├── auth_register.php           # Registration page
├── auth_check.php              # Session validation utility
├── logout.php                  # Logout handler
│
├── student_home.php            # Student dashboard
├── student_about.php           # About page (view-only)
├── student_departments.php     # Departments (view-only)
├── student_gallery.php         # Gallery (view-only)
├── student_marks.php           # View marks
├── student_register.php        # Registration forms
│
├── staff_home.php              # Staff dashboard
├── staff_gallery_upload.php    # Upload gallery images
├── staff_department_content.php # Upload department content
├── staff_approve_forms.php     # Approve student forms
├── staff_marks_entry.php       # Enter student marks
│
├── schema_updates.sql          # Database schema updates
├── dp_connection.php           # Database connection
├── utils.php                   # Utility functions
└── uploads/                    # Upload directories
    ├── gallery/
    └── department_content/
```

## Security Features

### 1. Session Management
- Session timeout after 30 minutes of inactivity
- Session ID regeneration on login
- Secure session cookie settings

### 2. Authentication
- Password hashing using PHP's `password_hash()` (bcrypt)
- CSRF token validation on forms
- SQL injection prevention using prepared statements

### 3. Access Control
- Role-based page access using `require_role()` function
- User must be logged in to access protected pages
- Automatic redirection based on role

### 4. File Upload Security
- File type validation (only allowed types)
- File size limits
- Unique filename generation
- Secure directory permissions

### 5. Input Validation
- All user inputs are validated
- XSS prevention using `htmlspecialchars()` (h() function)
- SQL injection prevention with prepared statements

## Usage Instructions

### For Students:
1. Register at `auth_register.php` (select "Student" role)
2. Login at `auth_login.php`
3. Complete profile information if prompted
4. Navigate through:
   - View About, Departments, Gallery (read-only)
   - Submit registration forms
   - View marks once entered by staff
5. After initial form approval, additional activities become available

### For Staff:
1. Register at `auth_register.php` (select "Staff" role)
2. Login at `auth_login.php`
3. Access admin features:
   - Upload gallery images
   - Upload department content
   - Review and approve/reject student forms
   - Enter marks for students
4. Notification badges show pending forms count

## Configuration

### Database Connection (`dp_connection.php`)
Update database credentials if needed:
```php
$host = 'localhost'; 
$db_user = 'root'; 
$db_password = ''; 
$db_name = 'iqac';
```

### Upload Directory Permissions
Ensure upload directories are writable:
```bash
chmod 755 uploads/gallery
chmod 755 uploads/department_content
```

### Session Timeout
Modify timeout in `auth_check.php`:
```php
$timeout_duration = 1800; // 30 minutes in seconds
```

## Testing Checklist

### Authentication Tests
- [ ] Register new student user
- [ ] Register new staff user
- [ ] Login with student credentials
- [ ] Login with staff credentials
- [ ] Test invalid credentials
- [ ] Test session timeout
- [ ] Test logout functionality

### Student Portal Tests
- [ ] Access all student pages
- [ ] Submit initial registration form
- [ ] View submitted forms status
- [ ] View marks (if any exist)
- [ ] Verify cannot access staff pages

### Staff Portal Tests
- [ ] Access all staff pages
- [ ] Upload gallery image
- [ ] Upload department content
- [ ] View pending forms
- [ ] Approve a student form
- [ ] Reject a student form
- [ ] Enter marks for a student
- [ ] Verify cannot access as student

### Security Tests
- [ ] Try accessing staff pages while logged in as student
- [ ] Try accessing student pages while logged in as staff
- [ ] Test CSRF protection
- [ ] Test file upload validation
- [ ] Test SQL injection prevention
- [ ] Test XSS prevention

## Troubleshooting

### Issue: Session timeout errors
**Solution**: Check session settings in php.ini or increase timeout in `auth_check.php`

### Issue: File upload fails
**Solution**: 
- Check directory permissions (755 or 777)
- Verify upload_max_filesize in php.ini
- Ensure directories exist

### Issue: Database connection error
**Solution**: 
- Verify MySQL is running
- Check credentials in `dp_connection.php`
- Ensure database exists

### Issue: Password hash mismatch
**Solution**: 
- Regenerate password hashes using `password_hash()`
- Ensure using PASSWORD_DEFAULT constant

## Maintenance

### Adding New Subjects for Staff
```sql
INSERT INTO staff_subjects (staff_id, user_id, subject_code, subject_name, department, semester)
VALUES (1, 1, 'CS101', 'Data Structures', 'Information Technology', 3);
```

### Linking Users to Students/Staff
```sql
-- Link user to student record
UPDATE students SET user_id = 1 WHERE id = 1;

-- Link user to staff record
UPDATE staff_details SET user_id = 2 WHERE id = 1;
```

## Future Enhancements
- Email notifications for form approvals
- Bulk marks upload via CSV
- Student profile completion wizard
- Staff-student messaging system
- Report generation (PDF)
- Dashboard analytics
- Mobile-responsive improvements

## Support
For issues or questions, refer to the repository's issue tracker or contact the development team.

---
**Version**: 1.0  
**Last Updated**: December 2025  
**Author**: PSG Polytechnic Development Team
