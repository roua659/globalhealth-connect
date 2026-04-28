-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 27 avr. 2026 à 20:03
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `globalhealth-connect`
--

-- --------------------------------------------------------

--
-- Structure de la table `commentaire`
--

CREATE TABLE `commentaire` (
  `id_commentaire` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `date_publication` datetime DEFAULT current_timestamp(),
  `date_modification` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `statut` enum('en_attente','publie','supprime') DEFAULT 'en_attente',
  `note` int(11) DEFAULT NULL CHECK (`note` between 1 and 5),
  `signalements` int(11) DEFAULT 0,
  `ip_utilisateur` varchar(45) DEFAULT NULL,
  `reponse` tinyint(1) DEFAULT 0,
  `id_publication` int(11) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commentaire`
--

INSERT INTO `commentaire` (`id_commentaire`, `contenu`, `date_publication`, `date_modification`, `statut`, `note`, `signalements`, `ip_utilisateur`, `reponse`, `id_publication`, `id_user`) VALUES
(2, 'ouii', '2026-04-27 17:36:45', NULL, 'publie', NULL, 0, NULL, 0, 5, 1),
(3, 'wttt', '2026-04-27 17:40:54', NULL, 'publie', NULL, 0, NULL, 0, 5, 4),
(4, 'nnn', '2026-04-27 17:45:11', NULL, 'publie', NULL, 0, NULL, 0, 5, 1),
(5, 'cc', '2026-04-27 17:45:18', NULL, 'publie', NULL, 0, NULL, 0, 4, 1),
(7, 'tt', '2026-04-27 18:56:45', '2026-04-27 18:40:37', 'supprime', NULL, 0, NULL, 0, 5, 1);

-- --------------------------------------------------------

--
-- Structure de la table `consultation`
--

CREATE TABLE `consultation` (
  `id` int(11) NOT NULL,
  `diagnostic` text DEFAULT NULL,
  `traitement` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `id_rdv` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `dossier_medical`
--

CREATE TABLE `dossier_medical` (
  `id_dossier` int(11) NOT NULL,
  `id_patient` int(11) DEFAULT NULL,
  `id_medecin` int(11) DEFAULT NULL,
  `id_rdv` int(11) DEFAULT NULL,
  `symptomes` text DEFAULT NULL,
  `diagnostic` text DEFAULT NULL,
  `traitement` text DEFAULT NULL,
  `ordonnance_texte` text DEFAULT NULL,
  `ordonnance_fichier` varchar(255) DEFAULT NULL,
  `notes_medecin` text DEFAULT NULL,
  `date_creation` datetime DEFAULT NULL,
  `historique_modification` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `dossier_medical`
--

INSERT INTO `dossier_medical` (`id_dossier`, `id_patient`, `id_medecin`, `id_rdv`, `symptomes`, `diagnostic`, `traitement`, `ordonnance_texte`, `ordonnance_fichier`, `notes_medecin`, `date_creation`, `historique_modification`) VALUES
(1, 1, 2, NULL, 'patienttt', 'dfndfh', 'trainteamnt', NULL, NULL, 'bien', '2026-04-13 00:39:30', 'Créé le 2026-04-13 00:39:30'),
(2, 1, 2, NULL, '', '', '', '', NULL, '', '2026-04-19 23:19:23', 'Cree le 2026-04-19 23:19:23'),
(3, 1, 2, NULL, '', '', '', '', NULL, '', '2026-04-19 23:27:09', 'Cree le 2026-04-19 23:27:09'),
(4, 1, 2, NULL, 'dfjhduf', 'diagggg', 'trrrttt', 'orrddd', NULL, 'beunnnn', '2026-04-19 23:32:31', 'Cree le 2026-04-19 23:32:31'),
(5, 1, 3, NULL, 'sump', 'diagggnd', 'fkdf', 'medicaments', NULL, 'notess', '2026-04-25 04:34:53', 'Cree le 2026-04-25 04:34:53'),
(6, 2, 3, NULL, 'sdfhdf', 'medical', 'traiiir', 'jjf', '69ec2a0d0c70d_20260425_044221.pdf', 'notessd', '2026-04-25 04:42:21', 'Cree le 2026-04-25 04:42:21');

-- --------------------------------------------------------

--
-- Structure de la table `medecin`
--

CREATE TABLE `medecin` (
  `id_medecin` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `specialite` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `medecin`
--

INSERT INTO `medecin` (`id_medecin`, `id_user`, `specialite`) VALUES
(2, 3, ''),
(3, 4, 'orrr');

-- --------------------------------------------------------

--
-- Structure de la table `patient`
--

CREATE TABLE `patient` (
  `id_patient` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `patient`
--

INSERT INTO `patient` (`id_patient`, `id_user`) VALUES
(1, 1),
(2, 5);

-- --------------------------------------------------------

--
-- Structure de la table `publication`
--

CREATE TABLE `publication` (
  `id_publication` int(11) NOT NULL,
  `contenu` text DEFAULT NULL,
  `date_publication` datetime DEFAULT NULL,
  `url_video` text DEFAULT NULL,
  `url_image` text DEFAULT NULL,
  `id_medecin` int(11) DEFAULT NULL,
  `statut` enum('approved','blocked') NOT NULL DEFAULT 'approved'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `publication`
--

INSERT INTO `publication` (`id_publication`, `contenu`, `date_publication`, `url_video`, `url_image`, `id_medecin`, `statut`) VALUES
(2, 'bfhd  dubdf sdifs f', '2026-04-27 16:57:43', NULL, NULL, 3, 'approved'),
(3, 'js jdsdf s', '2026-04-27 17:19:34', NULL, '/globalhealth-connect1/uploads/publications/pub_69ef7e868b50c6.44322686.jpg', 3, 'approved'),
(4, 'uasihud sjdsugd', '2026-04-27 17:22:12', NULL, NULL, 3, 'approved'),
(5, 'sjfdh sdfhgdf', '2026-04-27 17:26:38', NULL, '/globalhealth-connect1/uploads/publications/pub_69ef90554628e5.34218381.png', 3, 'approved'),
(6, '💊😡knklsfl', '2026-04-27 18:52:42', NULL, NULL, 3, 'blocked'),
(8, 'gshd sjdfdfj', '2026-04-27 19:47:14', NULL, NULL, 2, 'approved');

-- --------------------------------------------------------

--
-- Structure de la table `rendezvous`
--

CREATE TABLE `rendezvous` (
  `id_rdv` int(11) NOT NULL,
  `date_rdv` date DEFAULT NULL,
  `heure_rdv` time DEFAULT NULL,
  `motif` text DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL,
  `type_consultation` varchar(50) DEFAULT NULL,
  `lien_visio` text DEFAULT NULL,
  `date_creation` datetime DEFAULT NULL,
  `id_patient` int(11) DEFAULT NULL,
  `id_medecin` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `rendezvous`
--

INSERT INTO `rendezvous` (`id_rdv`, `date_rdv`, `heure_rdv`, `motif`, `statut`, `type_consultation`, `lien_visio`, `date_creation`, `id_patient`, `id_medecin`) VALUES
(2, '2026-04-19', '19:34:00', 'hfhfd', 'confirme', 'video', 'https://meet.jit.si/GlobalHealth_room_69dc1e1561405_75a968e183344b63', '2026-04-13 00:35:01', 1, 2),
(3, '2026-04-24', '01:36:00', 'nonnuuuu', 'en_attente', 'video', 'https://meet.jit.si/GlobalHealth_room_69dc1e688c716_ec233beacaf3a288', '2026-04-13 00:36:24', 1, 2),
(5, '2026-04-26', '09:08:00', 'Monn', 'confirme', 'presentiel', NULL, '2026-04-25 04:11:02', 1, 3),
(6, '2026-04-30', '15:32:00', 'Onon', 'confirme', 'presentiel', NULL, '2026-04-25 18:32:58', 1, 3);

-- --------------------------------------------------------

--
-- Structure de la table `role`
--

CREATE TABLE `role` (
  `id_role` int(11) NOT NULL,
  `type_role` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `role`
--

INSERT INTO `role` (`id_role`, `type_role`) VALUES
(1, 'patient'),
(2, 'medecin'),
(3, 'admin');

-- --------------------------------------------------------

--
-- Structure de la table `suivie`
--

CREATE TABLE `suivie` (
  `id` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `poids` float DEFAULT NULL,
  `tension` varchar(20) DEFAULT NULL,
  `etat_general` text DEFAULT NULL,
  `analyses_a_realiser` text DEFAULT NULL,
  `regime_alimentaire` text DEFAULT NULL,
  `activite_physique` text DEFAULT NULL,
  `prochain_rdv` date DEFAULT NULL,
  `id_patient` int(11) DEFAULT NULL,
  `id_consultation` int(11) DEFAULT NULL,
  `id_medecin` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id_user` int(11) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `sexe` varchar(10) DEFAULT NULL,
  `poids` float DEFAULT NULL,
  `taille` float DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `mot_de_passe` varchar(255) DEFAULT NULL,
  `cas_social` varchar(100) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `id_role` int(11) DEFAULT NULL,
  `specialite` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id_user`, `nom`, `prenom`, `age`, `sexe`, `poids`, `taille`, `email`, `mot_de_passe`, `cas_social`, `date_naissance`, `adresse`, `id_role`, `specialite`) VALUES
(1, 'Dupont', 'Jean', 30, 'Homme', 75.5, 1.8, 'jean.dupont@example.com', 'motdepasse123', 'Aucun', '1994-05-15', '12 rue de Paris, 75001 Paris', 1, NULL),
(2, 'ben hamouda', 'Firas', 23, 'Homme', 75.5, 1.8, 'firas.dev19@gmail.com', 'motdepasse123', 'Aucun', '1994-05-15', '12 rue de Paris, 75001 Paris', 3, NULL),
(3, 'Ben Hamouda', 'Youssef', 19, 'Homme', 60, 171, 'youssef@gmail.com', 'youssef19503', 'celeb', '2007-05-04', 'manouba', 2, NULL),
(4, 'ayari', 'roua', 21, 'Femme', 60, 164, 'roua@gmail.com', 'roua19503', 'celeb', '2005-05-02', 'ariana', 2, NULL),
(5, 'Hajji', 'nour', 22, 'Femme', 65, 168, 'nour@gmail.com', 'nour19503', NULL, '2004-07-16', 'manouba', 1, NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD PRIMARY KEY (`id_commentaire`),
  ADD KEY `id_publication` (`id_publication`),
  ADD KEY `id_user` (`id_user`);

--
-- Index pour la table `consultation`
--
ALTER TABLE `consultation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_rdv` (`id_rdv`);

--
-- Index pour la table `dossier_medical`
--
ALTER TABLE `dossier_medical`
  ADD PRIMARY KEY (`id_dossier`),
  ADD KEY `id_patient` (`id_patient`),
  ADD KEY `id_medecin` (`id_medecin`),
  ADD KEY `id_rdv` (`id_rdv`);

--
-- Index pour la table `medecin`
--
ALTER TABLE `medecin`
  ADD PRIMARY KEY (`id_medecin`),
  ADD UNIQUE KEY `id_user` (`id_user`);

--
-- Index pour la table `patient`
--
ALTER TABLE `patient`
  ADD PRIMARY KEY (`id_patient`),
  ADD UNIQUE KEY `id_user` (`id_user`);

--
-- Index pour la table `publication`
--
ALTER TABLE `publication`
  ADD PRIMARY KEY (`id_publication`),
  ADD KEY `id_medecin` (`id_medecin`);

--
-- Index pour la table `rendezvous`
--
ALTER TABLE `rendezvous`
  ADD PRIMARY KEY (`id_rdv`),
  ADD KEY `id_patient` (`id_patient`),
  ADD KEY `id_medecin` (`id_medecin`);

--
-- Index pour la table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id_role`);

--
-- Index pour la table `suivie`
--
ALTER TABLE `suivie`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_patient` (`id_patient`),
  ADD KEY `id_consultation` (`id_consultation`),
  ADD KEY `id_medecin` (`id_medecin`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id_user`),
  ADD KEY `id_role` (`id_role`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `commentaire`
--
ALTER TABLE `commentaire`
  MODIFY `id_commentaire` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `consultation`
--
ALTER TABLE `consultation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `dossier_medical`
--
ALTER TABLE `dossier_medical`
  MODIFY `id_dossier` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `medecin`
--
ALTER TABLE `medecin`
  MODIFY `id_medecin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `patient`
--
ALTER TABLE `patient`
  MODIFY `id_patient` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `publication`
--
ALTER TABLE `publication`
  MODIFY `id_publication` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `rendezvous`
--
ALTER TABLE `rendezvous`
  MODIFY `id_rdv` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `role`
--
ALTER TABLE `role`
  MODIFY `id_role` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `suivie`
--
ALTER TABLE `suivie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD CONSTRAINT `commentaire_ibfk_1` FOREIGN KEY (`id_publication`) REFERENCES `publication` (`id_publication`),
  ADD CONSTRAINT `commentaire_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `utilisateur` (`id_user`);

--
-- Contraintes pour la table `consultation`
--
ALTER TABLE `consultation`
  ADD CONSTRAINT `consultation_ibfk_1` FOREIGN KEY (`id_rdv`) REFERENCES `rendezvous` (`id_rdv`);

--
-- Contraintes pour la table `dossier_medical`
--
ALTER TABLE `dossier_medical`
  ADD CONSTRAINT `dossier_medical_ibfk_1` FOREIGN KEY (`id_patient`) REFERENCES `patient` (`id_patient`),
  ADD CONSTRAINT `dossier_medical_ibfk_2` FOREIGN KEY (`id_medecin`) REFERENCES `medecin` (`id_medecin`),
  ADD CONSTRAINT `dossier_medical_ibfk_3` FOREIGN KEY (`id_rdv`) REFERENCES `rendezvous` (`id_rdv`);

--
-- Contraintes pour la table `medecin`
--
ALTER TABLE `medecin`
  ADD CONSTRAINT `medecin_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `utilisateur` (`id_user`);

--
-- Contraintes pour la table `patient`
--
ALTER TABLE `patient`
  ADD CONSTRAINT `patient_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `utilisateur` (`id_user`);

--
-- Contraintes pour la table `publication`
--
ALTER TABLE `publication`
  ADD CONSTRAINT `publication_ibfk_1` FOREIGN KEY (`id_medecin`) REFERENCES `medecin` (`id_medecin`);

--
-- Contraintes pour la table `rendezvous`
--
ALTER TABLE `rendezvous`
  ADD CONSTRAINT `rendezvous_ibfk_1` FOREIGN KEY (`id_patient`) REFERENCES `patient` (`id_patient`),
  ADD CONSTRAINT `rendezvous_ibfk_2` FOREIGN KEY (`id_medecin`) REFERENCES `medecin` (`id_medecin`);

--
-- Contraintes pour la table `suivie`
--
ALTER TABLE `suivie`
  ADD CONSTRAINT `suivie_ibfk_1` FOREIGN KEY (`id_patient`) REFERENCES `patient` (`id_patient`),
  ADD CONSTRAINT `suivie_ibfk_2` FOREIGN KEY (`id_consultation`) REFERENCES `consultation` (`id`),
  ADD CONSTRAINT `suivie_ibfk_3` FOREIGN KEY (`id_medecin`) REFERENCES `medecin` (`id_medecin`);

--
-- Contraintes pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD CONSTRAINT `utilisateur_ibfk_1` FOREIGN KEY (`id_role`) REFERENCES `role` (`id_role`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
