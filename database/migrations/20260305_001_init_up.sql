CREATE TABLE IF NOT EXISTS schema_migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration_name VARCHAR(255) NOT NULL UNIQUE,
    applied_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'lecturer', 'cr') NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marazone_course_id VARCHAR(100) NULL UNIQUE,
    code VARCHAR(40) NOT NULL,
    title VARCHAR(255) NOT NULL,
    lecturer_user_id INT NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_courses_lecturer FOREIGN KEY (lecturer_user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marazone_section_id VARCHAR(100) NULL UNIQUE,
    course_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    semester VARCHAR(30) NOT NULL,
    academic_year VARCHAR(30) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_sections_course FOREIGN KEY (course_id) REFERENCES courses(id)
);

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marazone_student_id VARCHAR(100) NULL UNIQUE,
    reg_no VARCHAR(80) NOT NULL UNIQUE,
    full_name VARCHAR(180) NOT NULL,
    email VARCHAR(160) NULL,
    section_id INT NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_students_section FOREIGN KEY (section_id) REFERENCES sections(id)
);

CREATE TABLE IF NOT EXISTS cr_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_id INT NOT NULL,
    cr_user_id INT NOT NULL,
    starts_on DATE NULL,
    ends_on DATE NULL,
    assigned_by INT NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cr_assignments_section FOREIGN KEY (section_id) REFERENCES sections(id),
    CONSTRAINT fk_cr_assignments_user FOREIGN KEY (cr_user_id) REFERENCES users(id),
    CONSTRAINT fk_cr_assignments_admin FOREIGN KEY (assigned_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marazone_session_id VARCHAR(120) NULL UNIQUE,
    section_id INT NOT NULL,
    session_date DATE NOT NULL,
    starts_at TIME NOT NULL,
    ends_at TIME NOT NULL,
    room VARCHAR(80) NULL,
    status ENUM('scheduled', 'completed', 'cancelled') NOT NULL DEFAULT 'scheduled',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_sessions_section FOREIGN KEY (section_id) REFERENCES sections(id),
    INDEX idx_sessions_date (session_date)
);

CREATE TABLE IF NOT EXISTS attendance_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL UNIQUE,
    cr_user_id INT NOT NULL,
    teaching_declared_at DATETIME NOT NULL,
    declaration_text VARCHAR(1000) NOT NULL,
    submitted_at DATETIME NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'overridden') NOT NULL DEFAULT 'pending',
    deadline_at DATETIME NOT NULL,
    lecturer_user_id INT NOT NULL,
    lecturer_decision_at DATETIME NULL,
    lecturer_comment VARCHAR(1000) NULL,
    signed_sheet_status ENUM('missing', 'attached', 'replaced') NOT NULL DEFAULT 'missing',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_attendance_submissions_session FOREIGN KEY (session_id) REFERENCES sessions(id),
    CONSTRAINT fk_attendance_submissions_cr FOREIGN KEY (cr_user_id) REFERENCES users(id),
    CONSTRAINT fk_attendance_submissions_lecturer FOREIGN KEY (lecturer_user_id) REFERENCES users(id),
    INDEX idx_attendance_submissions_status_deadline (status, deadline_at)
);

CREATE TABLE IF NOT EXISTS attendance_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    student_id INT NOT NULL,
    status ENUM('present', 'absent', 'late') NOT NULL,
    note VARCHAR(500) NULL,
    updated_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT uq_submission_student UNIQUE (submission_id, student_id),
    CONSTRAINT fk_attendance_items_submission FOREIGN KEY (submission_id) REFERENCES attendance_submissions(id),
    CONSTRAINT fk_attendance_items_student FOREIGN KEY (student_id) REFERENCES students(id),
    CONSTRAINT fk_attendance_items_updated_by FOREIGN KEY (updated_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS signed_sheet_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    version_no INT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    uploaded_by_user_id INT NOT NULL,
    uploaded_at DATETIME NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    size_bytes INT NOT NULL,
    storage_path VARCHAR(500) NOT NULL,
    sha256_hash CHAR(64) NOT NULL,
    replaced_at DATETIME NULL,
    CONSTRAINT uq_signed_sheet_submission_version UNIQUE (submission_id, version_no),
    CONSTRAINT fk_signed_sheet_submission FOREIGN KEY (submission_id) REFERENCES attendance_submissions(id),
    CONSTRAINT fk_signed_sheet_uploaded_by FOREIGN KEY (uploaded_by_user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS admin_overrides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    admin_user_id INT NOT NULL,
    action ENUM('approve', 'reject') NOT NULL,
    reason VARCHAR(1000) NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_admin_override_submission FOREIGN KEY (submission_id) REFERENCES attendance_submissions(id),
    CONSTRAINT fk_admin_override_admin FOREIGN KEY (admin_user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS escalations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    escalated_at DATETIME NOT NULL,
    resolved_at DATETIME NULL,
    resolution_note VARCHAR(1000) NULL,
    CONSTRAINT fk_escalation_submission FOREIGN KEY (submission_id) REFERENCES attendance_submissions(id),
    INDEX idx_escalation_submission_resolved (submission_id, resolved_at)
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    actor_user_id INT NOT NULL,
    entity_type VARCHAR(120) NOT NULL,
    entity_id INT NOT NULL,
    action VARCHAR(120) NOT NULL,
    before_json JSON NULL,
    after_json JSON NULL,
    ip VARCHAR(64) NOT NULL,
    user_agent VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_audit_entity (entity_type, entity_id),
    INDEX idx_audit_actor (actor_user_id),
    CONSTRAINT fk_audit_actor FOREIGN KEY (actor_user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS mz_students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_id VARCHAR(100) NOT NULL UNIQUE,
    reg_no VARCHAR(80) NULL,
    full_name VARCHAR(180) NULL,
    email VARCHAR(160) NULL,
    section_source_id VARCHAR(100) NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    row_checksum CHAR(64) NOT NULL,
    synced_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS mz_lecturers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_id VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(180) NULL,
    email VARCHAR(160) NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    row_checksum CHAR(64) NOT NULL,
    synced_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS mz_courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_id VARCHAR(100) NOT NULL UNIQUE,
    code VARCHAR(60) NULL,
    title VARCHAR(255) NULL,
    lecturer_source_id VARCHAR(100) NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    row_checksum CHAR(64) NOT NULL,
    synced_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS mz_sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_id VARCHAR(100) NOT NULL UNIQUE,
    course_source_id VARCHAR(100) NULL,
    name VARCHAR(100) NULL,
    semester VARCHAR(30) NULL,
    academic_year VARCHAR(30) NULL,
    row_checksum CHAR(64) NOT NULL,
    synced_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS mz_timetable_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_id VARCHAR(100) NOT NULL UNIQUE,
    section_source_id VARCHAR(100) NULL,
    day_of_week TINYINT NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    room VARCHAR(80) NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    row_checksum CHAR(64) NOT NULL,
    synced_at DATETIME NOT NULL,
    INDEX idx_mz_timetable_lookup (section_source_id, day_of_week)
);
