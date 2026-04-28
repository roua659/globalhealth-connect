-- ============================================================
-- Script de mise à jour BDD GlobalHealth Connect
-- À exécuter UNE SEULE FOIS via phpMyAdmin
-- ============================================================

-- Ajouter le champ resultat_analyses à la table suivie
-- (permet au patient de saisir ses résultats d'examens)
ALTER TABLE `suivie`
    ADD COLUMN IF NOT EXISTS `resultat_analyses` TEXT NULL
    AFTER `analyses_a_realiser`;

-- Vérification : afficher la structure finale de la table
SHOW COLUMNS FROM `suivie`;
