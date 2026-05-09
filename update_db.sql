-- GlobalHealth Connect - migration consultation et suivis
-- A executer une seule fois si votre base vient de l'ancien projet Tasnim.

-- Table consultation attendue par ConsultationModel:
-- id_consultation, diagnostic, traitement, notes, id_rdv, date_creation

ALTER TABLE `suivie` DROP FOREIGN KEY `suivie_ibfk_2`;

ALTER TABLE `consultation`
    CHANGE COLUMN `id` `id_consultation` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `consultation`
    ADD COLUMN `date_creation` DATETIME NULL DEFAULT CURRENT_TIMESTAMP AFTER `id_rdv`;

-- Table suivie attendue par SuivieModel:
-- id_suivie, date_suivi, poids, tension, etat_general, analyses_a_realiser,
-- resultat_analyses, regime_alimentaire, activite_physique, prochain_rdv,
-- id_patient, id_consultation, id_medecin, date_creation

ALTER TABLE `suivie`
    CHANGE COLUMN `id` `id_suivie` INT(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `suivie`
    CHANGE COLUMN `date` `date_suivi` DATE NULL;

ALTER TABLE `suivie`
    ADD COLUMN `resultat_analyses` TEXT NULL AFTER `analyses_a_realiser`;

ALTER TABLE `suivie`
    ADD COLUMN `date_creation` DATETIME NULL DEFAULT CURRENT_TIMESTAMP AFTER `id_medecin`;

ALTER TABLE `suivie`
    ADD CONSTRAINT `suivie_ibfk_2`
    FOREIGN KEY (`id_consultation`) REFERENCES `consultation` (`id_consultation`);

SHOW COLUMNS FROM `consultation`;
SHOW COLUMNS FROM `suivie`;
