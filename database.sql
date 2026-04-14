-- Create Role table (must be first - no dependencies)
CREATE TABLE IF NOT EXISTS `role` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create User table (depends on role)
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nom` VARCHAR(100) NOT NULL,
  `prenom` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `telephone` VARCHAR(20),
  `password` VARCHAR(255) NOT NULL,
  `id_role` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_role`) REFERENCES `role`(`id`),
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Publication table (depends on utilisateur)
CREATE TABLE IF NOT EXISTS `publication` (
  `id_publication` INT AUTO_INCREMENT PRIMARY KEY,
  `contenu` LONGTEXT NOT NULL,
  `date_publication` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `url_video` VARCHAR(255) NULL,
  `url_image` VARCHAR(255) NULL,
  `id_medecin` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_medecin`) REFERENCES `utilisateur`(`id`) ON DELETE CASCADE,
  INDEX `idx_medecin` (`id_medecin`),
  INDEX `idx_date` (`date_publication`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Commentaire table (depends on publication and utilisateur)
CREATE TABLE IF NOT EXISTS `commentaire` (
  `id_commentaire` INT AUTO_INCREMENT PRIMARY KEY,
  `contenu` TEXT NOT NULL,
  `date_publication` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `id_publication` INT NOT NULL,
  `id_user` INT NOT NULL,
  `statut` ENUM('approved', 'pending', 'rejected') DEFAULT 'pending',
  `note` INT DEFAULT 0,
  `signalements` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_publication`) REFERENCES `publication`(`id_publication`) ON DELETE CASCADE,
  FOREIGN KEY (`id_user`) REFERENCES `utilisateur`(`id`) ON DELETE CASCADE,
  INDEX `idx_publication` (`id_publication`),
  INDEX `idx_user` (`id_user`),
  INDEX `idx_statut` (`statut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample roles
INSERT INTO `role` (`name`) VALUES ('admin'), ('medecin'), ('user') ON DUPLICATE KEY UPDATE `name`=`name`;

-- Insert sample doctors (médecins)
INSERT INTO `utilisateur` (`nom`, `prenom`, `email`, `telephone`, `password`, `id_role`, `created_at`) 
VALUES 
('Dupont', 'Jean', 'jean.dupont@medicalapp.com', '0612345678', SHA2('password123', 256), 2, NOW()),
('Martin', 'Marie', 'marie.martin@medicalapp.com', '0687654321', SHA2('password123', 256), 2, NOW()),
('Bernard', 'Pierre', 'pierre.bernard@medicalapp.com', '0698765432', SHA2('password123', 256), 2, NOW()),
('Leclerc', 'Sophie', 'sophie.leclerc@medicalapp.com', '0678901234', SHA2('password123', 256), 2, NOW())
ON DUPLICATE KEY UPDATE `email`=`email`;

-- Insert sample patients (utilisateurs)
INSERT INTO `utilisateur` (`nom`, `prenom`, `email`, `telephone`, `password`, `id_role`, `created_at`) 
VALUES 
('Durand', 'Anne', 'anne.durand@email.com', '0611223344', SHA2('password123', 256), 3, NOW()),
('Lefebvre', 'Marc', 'marc.lefebvre@email.com', '0622334455', SHA2('password123', 256), 3, NOW()),
('Moreau', 'Luc', 'luc.moreau@email.com', '0633445566', SHA2('password123', 256), 3, NOW())
ON DUPLICATE KEY UPDATE `email`=`email`;
