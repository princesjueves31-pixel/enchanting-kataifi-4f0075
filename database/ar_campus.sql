CREATE DATABASE IF NOT EXISTS ar_campus
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ar_campus;

CREATE TABLE IF NOT EXISTS users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  user_type ENUM('guest', 'student', 'admin') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS admins (
  admin_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(120) DEFAULT 'System Administrator',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS staff (
  staff_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  department VARCHAR(150) NOT NULL,
  office VARCHAR(150) NOT NULL,
  building_id INT NULL
);

CREATE TABLE IF NOT EXISTS buildings (
  building_id INT AUTO_INCREMENT PRIMARY KEY,
  marker_id VARCHAR(80) NOT NULL UNIQUE,
  building_name VARCHAR(160) NOT NULL,
  category VARCHAR(80) NOT NULL,
  latitude DECIMAL(10, 8) NOT NULL,
  longitude DECIMAL(11, 8) NOT NULL,
  description TEXT NOT NULL,
  image VARCHAR(255) NULL,
  info_status VARCHAR(80) DEFAULT 'Prototype',
  source_note VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS building_info (
  info_id INT AUTO_INCREMENT PRIMARY KEY,
  building_id INT NOT NULL,
  marker_image VARCHAR(255) NOT NULL,
  ar_payload VARCHAR(120) NOT NULL UNIQUE,
  description TEXT NOT NULL,
  FOREIGN KEY (building_id) REFERENCES buildings(building_id)
    ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS feedback (
  feedback_id INT AUTO_INCREMENT PRIMARY KEY,
  user_name VARCHAR(120) NOT NULL,
  building_id INT NULL,
  comment TEXT NOT NULL,
  date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (building_id) REFERENCES buildings(building_id)
    ON DELETE SET NULL
);

INSERT INTO admins (username, password_hash, full_name)
VALUES ('admin', '$2y$10$jnP8lrVe06kV9zfssTRgOOadCuyXYw9hCzHJzV7gd9ARbCrsVJ2Pa', 'System Administrator')
ON DUPLICATE KEY UPDATE username = VALUES(username);

INSERT INTO users (user_type)
SELECT 'admin'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE user_type = 'admin');

INSERT INTO buildings
  (marker_id, building_name, category, latitude, longitude, description, image, info_status, source_note)
VALUES
  ('gate', 'Main Gate / Entrance', 'Campus Entry', 8.15626000, 123.95448000, 'Primary campus entry point used as a starting reference for the navigation prototype.', 'markers/gate.png', 'Prototype', 'Prototype coordinate. Verify on-site.'),
  ('admin', 'Administration Building', 'Administration', 8.15648000, 123.95437000, 'Administrative destination in the campus guide. Exact coordinates should be verified during campus mapping.', 'markers/admin.png', 'Prototype', 'Prototype coordinate. Verify on-site.'),
  ('dcs', 'Department of Computer Science', 'Academic', 8.15670000, 123.95470000, 'Academic department listed on the official MSU-MCEST website.', 'markers/dcs.png', 'Public Source', 'https://msumcest.edu.ph/pages/campus_life/campus_map.php'),
  ('dhess', 'Department of Humanities, Education, and Social Sciences', 'Academic', 8.15686000, 123.95431000, 'Academic department listed on the official MSU-MCEST website.', 'markers/dhess.png', 'Public Source', 'https://msumcest.edu.ph/pages/campus_life/campus_map.php'),
  ('secondary', 'Secondary Department', 'Academic', 8.15702000, 123.95458000, 'Secondary program area referenced through the official MSU-MCEST site navigation.', 'markers/secondary.png', 'Public Source', 'https://msumcest.edu.ph/pages/campus_life/campus_map.php'),
  ('hotel', 'Dr. Magadapa A. Ringia Hotel', 'Hospitality', 8.15718000, 123.95485000, 'Facility named in MHCT reporting as one of the MSU-MCEST facilities awarded Halal compliance.', 'markers/hotel.png', 'Public Source', 'https://www.mhctagency.com/certification/msu-mcest-achieves-halal-compliance-with-mhct/'),
  ('halal', 'Halal Food Innovation Center / DTI-SSF', 'Training Facility', 8.15645000, 123.95503000, 'MHCT identifies DTI-SSF as housed in the Halal Food Innovation Center.', 'markers/halal.png', 'Public Source', 'https://www.mhctagency.com/certification/msu-mcest-achieves-halal-compliance-with-mhct/'),
  ('kitchen', 'Kitchen Laboratory', 'Laboratory', 8.15618000, 123.95486000, 'Facility named in MHCT reporting as one of the MSU-MCEST facilities awarded Halal compliance.', 'markers/kitchen.png', 'Public Source', 'https://www.mhctagency.com/certification/msu-mcest-achieves-halal-compliance-with-mhct/'),
  ('tesda', 'TESDA Training and Assessment Center', 'Training Facility', 8.15596000, 123.95456000, 'Training and assessment facility named in MHCT reporting and linked from the official MSU-MCEST site navigation.', 'markers/tesda.png', 'Public Source', 'https://www.mhctagency.com/certification/msu-mcest-achieves-halal-compliance-with-mhct/')
ON DUPLICATE KEY UPDATE
  building_name = VALUES(building_name),
  category = VALUES(category),
  latitude = VALUES(latitude),
  longitude = VALUES(longitude),
  description = VALUES(description),
  image = VALUES(image),
  info_status = VALUES(info_status),
  source_note = VALUES(source_note);

INSERT INTO building_info (building_id, marker_image, ar_payload, description)
SELECT building_id, COALESCE(image, CONCAT('markers/', marker_id, '.png')), CONCAT('msu-mcest:', marker_id), description
FROM buildings
ON DUPLICATE KEY UPDATE
  marker_image = VALUES(marker_image),
  description = VALUES(description);
