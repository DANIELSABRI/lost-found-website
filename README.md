# University Lost & Found Management System

A world-class, secure, and modern web application for managing lost and found items on campus. Designed for students, faculty, and administrators to report, track, and recover belongings efficiently.

## 🚀 Features

### **Public Identity**
- **Professional Landing Page**: Features a hero section, live statistics, and a clear "How It Works" guide.
- **Responsive Design**: Fully optimized for mobile, tablet, and desktop (Purple/White Modern Theme).

### **User Experience (Student)**
- **Dashboard**: A personalized hub showing your reported items and successful matches.
- **Report System**: Intuitive styling for reporting "Lost" or "Found" items with image upload support.
- **Smart Search**: Advanced filtering by category, location, and date.
- **Intelligent Matching**: The system automatically suggests "Found" items that match your "Lost" reports based on category.
- **Real-time Notifications**: Alerts for status updates and messages.
- **Secure Messaging**: integrated chat system to communicate with finders anonymously.

### **Admin Capabilities**
- **System Overview**: Global statistics on lost vs. found rates.
- **Content Moderation**: Ability to view and manage all reports.

## 🛠️ Technology Stack

- **Backend**: PHP 8.0+ (PDO for Database Security)
- **Frontend**: HTML5, CSS3 (Custom "University" Design System - No Frameworks), Vanilla JS.
- **Database**: MySQL.
- **Server**: Apache (XAMPP/MAMP recommended).

## 📦 Installation & Setup

1. **Clone the Repository**
   ```bash
   git clone https://github.com/university/lost-found.git
   cd lost-found
   ```

2. **Database Setup**
   - Open PHPMyAdmin.
   - Create a database `lost_found_db`.
   - Import `database.sql` (Schema included in root).

3. **Configuration**
   - Edit `includes/config.php` (if applicable) to match your DB credentials.
   - Ensure `uploads/` directory has write permissions.

4. **Launch**
   - Start Apache & MySQL via XAMPP.
   - Navigate to `http://localhost/lost-found`.

## 🛡️ Security Measures
- **PDO Prepared Statements**: Prevents SQL Injection.
- **Session Authentication**: Secure login/logout flows.
- **Input Validation**: Server-side checks for all forms.
- **XSS Protection**: `htmlspecialchars` output encoding.

## 👥 Authors
- **Daniel Sabri** - *Lead Developer & UI Architect*

---
© 2026 University Management System. All Rights Reserved.
